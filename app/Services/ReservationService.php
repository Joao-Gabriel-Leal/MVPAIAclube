<?php

namespace App\Services;

use App\Enums\DiscountType;
use App\Enums\MembershipStatus;
use App\Enums\ReservationStatus;
use App\Models\ClubResource;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\ResourceBlock;
use App\Models\ResourceSchedule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReservationService
{
    public function __construct(
        protected AuditService $auditService,
    ) {
    }

    public function availability(ClubResource $resource, Carbon $date): Collection
    {
        $resource->loadMissing('schedules');
        [$blocksByDate, $reservationsByDate] = $this->availabilityContext($resource, $date, $date);

        return $this->buildSlots(
            $resource,
            $date,
            $blocksByDate->get($date->toDateString(), collect()),
            $reservationsByDate->get($date->toDateString(), collect()),
        );
    }

    public function monthlyAvailability(ClubResource $resource, Carbon $month): Collection
    {
        $resource->loadMissing('schedules');

        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        [$blocksByDate, $reservationsByDate] = $this->availabilityContext($resource, $monthStart, $monthEnd);
        $today = now()->startOfDay();

        return collect(range(0, $monthStart->daysInMonth - 1))
            ->map(function (int $offset) use ($resource, $monthStart, $blocksByDate, $reservationsByDate, $today) {
                $date = $monthStart->copy()->addDays($offset);

                if ($date->lt($today)) {
                    return [
                        'date' => $date->toDateString(),
                        'state' => 'past',
                        'available_slots_count' => 0,
                    ];
                }

                $slots = $this->buildSlots(
                    $resource,
                    $date,
                    $blocksByDate->get($date->toDateString(), collect()),
                    $reservationsByDate->get($date->toDateString(), collect()),
                );

                $availableSlots = $slots->where('available', true)->count();

                return [
                    'date' => $date->toDateString(),
                    'state' => $this->resolveDayState($slots->count(), $availableSlots),
                    'available_slots_count' => $availableSlots,
                ];
            });
    }

    public function createReservation(array $data, User $actor): Reservation
    {
        $resource = ClubResource::query()
            ->with(['plans', 'schedules', 'blocks'])
            ->findOrFail($data['club_resource_id']);

        if ($actor->isAdminBranch() && $resource->branch_id !== $actor->branch_id) {
            throw new RuntimeException('O admin da filial nao pode reservar recursos de outra unidade.');
        }

        [$member, $reserver] = $this->resolveReserver($actor, $data);

        if ($actor->isAdminBranch() && ! $member->allBranchIds()->contains($actor->branch_id)) {
            throw new RuntimeException('O associado informado nao pertence a esta filial.');
        }

        if ($member->status !== MembershipStatus::Active) {
            throw new RuntimeException('Somente associados ativos podem reservar recursos.');
        }

        $date = Carbon::parse($data['reservation_date']);

        if (! $this->isResourceAvailable($resource, $date, $data['start_time'], $data['end_time'])) {
            throw new RuntimeException('O horario selecionado nao esta disponivel.');
        }

        if ($member->plan && $member->plan->resources()->exists() && ! $member->plan->resources->contains($resource)) {
            throw new RuntimeException('O plano atual nao permite reservar este recurso.');
        }

        if (($data['guest_count'] ?? 0) > ($member->plan?->guest_limit_per_reservation ?? 0)) {
            throw new RuntimeException('A quantidade de convidados excede o limite do plano.');
        }

        [$originalAmount, $chargedAmount] = $this->calculateAmounts($member, $resource, $date);

        return DB::transaction(function () use ($data, $actor, $member, $reserver, $resource, $originalAmount, $chargedAmount) {
            $reservation = Reservation::query()->create([
                'branch_id' => $resource->branch_id,
                'club_resource_id' => $resource->id,
                'member_id' => $member->id,
                'reserver_type' => $reserver->getMorphClass(),
                'reserver_id' => $reserver->id,
                'reservation_date' => $data['reservation_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'guest_count' => $data['guest_count'] ?? 0,
                'original_amount' => $originalAmount,
                'charged_amount' => $chargedAmount,
                'status' => ReservationStatus::Confirmed,
                'notes' => $data['notes'] ?? null,
                'created_by_user_id' => $actor->id,
            ]);

            $this->auditService->log($actor, 'reservation.created', $reservation, [
                'charged_amount' => $chargedAmount,
            ]);

            return $reservation->load(['resource', 'member.user', 'reserver']);
        });
    }

    public function updateStatus(Reservation $reservation, ReservationStatus $status, User $actor, ?string $notes = null): Reservation
    {
        $reservation->update([
            'status' => $status,
            'notes' => filled($notes) ? trim(($reservation->notes ? $reservation->notes.PHP_EOL : '').$notes) : $reservation->notes,
        ]);

        $this->auditService->log($actor, 'reservation.status_updated', $reservation, [
            'status' => $status->value,
        ]);

        return $reservation->refresh();
    }

    protected function resolveReserver(User $actor, array $data): array
    {
        if ($actor->isAdminMatrix() || $actor->isAdminBranch()) {
            if (! empty($data['dependent_id'])) {
                $dependent = Dependent::query()->with('member.plan.resources')->findOrFail($data['dependent_id']);

                return [$dependent->member, $dependent];
            }

            $member = Member::query()->with('plan.resources')->findOrFail($data['member_id']);

            return [$member, $member];
        }

        if ($actor->isDependent()) {
            $dependent = $actor->dependent()->with('member.plan.resources')->firstOrFail();

            return [$dependent->member, $dependent];
        }

        $member = $actor->member()->with('plan.resources')->firstOrFail();

        return [$member, $member];
    }

    protected function calculateAmounts(Member $member, ClubResource $resource, Carbon $date): array
    {
        $plan = $member->plan;
        $original = (float) $resource->default_price;
        $freeReservations = $plan?->free_reservations_per_month ?? 0;
        $usedReservations = $member->reservations()
            ->whereMonth('reservation_date', $date->month)
            ->whereYear('reservation_date', $date->year)
            ->where('status', ReservationStatus::Confirmed)
            ->count();

        if ($usedReservations < $freeReservations) {
            return [$original, 0.0];
        }

        if (! $plan) {
            return [$original, $original];
        }

        $charged = match ($plan->extra_reservation_discount_type) {
            DiscountType::Percentage => $original * (1 - (((float) $plan->extra_reservation_discount_value) / 100)),
            DiscountType::Fixed => max($original - (float) $plan->extra_reservation_discount_value, 0),
            default => $original,
        };

        return [$original, round($charged, 2)];
    }

    protected function isResourceAvailable(ClubResource $resource, Carbon $date, string $startTime, string $endTime): bool
    {
        [$blocksByDate, $reservationsByDate] = $this->availabilityContext($resource, $date, $date);

        return $this->timeRangeIsAvailable(
            $startTime,
            $endTime,
            $blocksByDate->get($date->toDateString(), collect()),
            $reservationsByDate->get($date->toDateString(), collect()),
        );
    }

    protected function availabilityContext(ClubResource $resource, Carbon $startDate, Carbon $endDate): array
    {
        $blocks = $resource->blocks()
            ->whereDate('block_date', '>=', $startDate->toDateString())
            ->whereDate('block_date', '<=', $endDate->toDateString())
            ->get()
            ->groupBy(fn (ResourceBlock $block) => $block->block_date->toDateString());

        $reservations = $resource->reservations()
            ->whereDate('reservation_date', '>=', $startDate->toDateString())
            ->whereDate('reservation_date', '<=', $endDate->toDateString())
            ->where('status', '!=', ReservationStatus::Cancelled)
            ->get()
            ->groupBy(fn (Reservation $reservation) => $reservation->reservation_date->toDateString());

        return [$blocks, $reservations];
    }

    protected function buildSlots(ClubResource $resource, Carbon $date, Collection $blocks, Collection $reservations): Collection
    {
        $schedule = $this->scheduleForDate($resource, $date);

        if (! $schedule) {
            return collect();
        }

        $slots = collect();
        $cursor = Carbon::parse($date->toDateString().' '.$schedule->opens_at);
        $endOfDay = Carbon::parse($date->toDateString().' '.$schedule->closes_at);

        while ($cursor->lt($endOfDay)) {
            $slotEnd = $cursor->copy()->addMinutes($schedule->slot_interval_minutes);

            if ($slotEnd->gt($endOfDay)) {
                break;
            }

            $startTime = $cursor->format('H:i');
            $endTime = $slotEnd->format('H:i');

            $slots->push([
                'start_time' => $startTime,
                'end_time' => $endTime,
                'available' => $this->timeRangeIsAvailable($startTime, $endTime, $blocks, $reservations),
            ]);

            $cursor = $slotEnd;
        }

        return $slots;
    }

    protected function scheduleForDate(ClubResource $resource, Carbon $date): ?ResourceSchedule
    {
        return $resource->schedules
            ->where('is_active', true)
            ->firstWhere('day_of_week', $date->dayOfWeek);
    }

    protected function timeRangeIsAvailable(string $startTime, string $endTime, Collection $blocks, Collection $reservations): bool
    {
        $blocked = $blocks->contains(fn (ResourceBlock $block) => $block->start_time < $endTime && $block->end_time > $startTime);

        if ($blocked) {
            return false;
        }

        return ! $reservations->contains(
            fn (Reservation $reservation) => $reservation->start_time < $endTime && $reservation->end_time > $startTime
        );
    }

    protected function resolveDayState(int $slotCount, int $availableSlots): string
    {
        if ($slotCount === 0 || $availableSlots === 0) {
            return 'unavailable';
        }

        if ($availableSlots === $slotCount) {
            return 'available';
        }

        return 'partial';
    }
}
