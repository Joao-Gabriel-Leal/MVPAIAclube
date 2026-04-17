<?php

namespace App\Services;

use App\Enums\DependentStatus;
use App\Enums\MembershipStatus;
use App\Enums\ProposalOrigin;
use App\Models\Branch;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProposalService
{
    public function paginatedFor(User $user, array $filters = []): array
    {
        $branchId = $user->isAdminBranch() ? $user->branch_id : ($filters['branch_id'] ?? null);
        $type = $filters['type'] ?? null;
        $origin = $filters['origin'] ?? null;
        $age = $filters['age'] ?? null;

        $collection = collect()
            ->when($type !== 'dependent', fn (Collection $items) => $items->concat($this->pendingMembers($branchId, $origin)))
            ->when($type !== 'member', fn (Collection $items) => $items->concat($this->pendingDependents($branchId, $origin)))
            ->filter(fn (array $proposal) => $this->matchesAgeFilter($proposal['submitted_at'], $age))
            ->sortByDesc('submitted_at')
            ->values();

        $page = Paginator::resolveCurrentPage();
        $perPage = 12;
        $paginated = new LengthAwarePaginator(
            $collection->slice(($page - 1) * $perPage, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        );

        return [
            'proposals' => $paginated->withQueryString(),
            'summary' => [
                'total' => $collection->count(),
                'members' => $collection->where('type', 'member')->count(),
                'dependents' => $collection->where('type', 'dependent')->count(),
                'public' => $collection->where('origin', ProposalOrigin::Public->value)->count(),
                'manual' => $collection->where('origin', ProposalOrigin::Manual->value)->count(),
                'attention' => $collection->filter(fn (array $proposal) => $this->ageBucket($proposal['submitted_at']) !== 'recent')->count(),
            ],
            'filters' => [
                'branch_id' => $branchId,
                'type' => $type,
                'origin' => $origin,
                'age' => $age,
            ],
            'branches' => $user->isAdminMatrix()
                ? Branch::query()->active()->orderBy('name')->get()
                : Branch::query()->active()->whereKey($branchId)->orderBy('name')->get(),
        ];
    }

    public function pendingCounts(?int $branchId = null): array
    {
        $members = Member::query()
            ->where('status', MembershipStatus::Pending)
            ->when($branchId, fn ($query) => $query->where('primary_branch_id', $branchId))
            ->count();

        $dependents = Dependent::query()
            ->where('status', DependentStatus::Pending)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->count();

        return [
            'members' => $members,
            'dependents' => $dependents,
            'total' => $members + $dependents,
        ];
    }

    public function recent(?int $branchId = null, int $limit = 8): Collection
    {
        return $this->pendingMembers($branchId)
            ->concat($this->pendingDependents($branchId))
            ->sortByDesc('submitted_at')
            ->take($limit)
            ->values();
    }

    protected function pendingMembers(?int $branchId = null, ?string $origin = null): Collection
    {
        return Member::query()
            ->with(['user', 'primaryBranch', 'plan'])
            ->where('status', MembershipStatus::Pending)
            ->when($branchId, fn ($query) => $query->where('primary_branch_id', $branchId))
            ->when($origin, fn ($query, $selectedOrigin) => $query->where('source', $selectedOrigin))
            ->get()
            ->map(function (Member $member) {
                $submittedAt = $member->created_at instanceof Carbon ? $member->created_at : Carbon::parse($member->created_at);

                return [
                    'type' => 'member',
                    'type_label' => 'Associado',
                    'id' => $member->id,
                    'name' => $member->user->name,
                    'email' => $member->user->email,
                    'branch_name' => $member->primaryBranch->name,
                    'context' => $member->plan?->name ?: 'Sem plano',
                    'status_label' => $member->status->label(),
                    'origin' => $member->source->value,
                    'origin_label' => $member->source->label(),
                    'submitted_at' => $submittedAt,
                    'submitted_age_label' => $this->ageLabel($submittedAt),
                    'age_bucket' => $this->ageBucket($submittedAt),
                    'show_url' => route('membros.show', $member),
                    'status_url' => route('members.status', $member),
                ];
            });
    }

    protected function pendingDependents(?int $branchId = null, ?string $origin = null): Collection
    {
        if ($origin === ProposalOrigin::Public->value) {
            return collect();
        }

        return Dependent::query()
            ->with(['user', 'branch', 'member.user'])
            ->where('status', DependentStatus::Pending)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($origin, fn ($query, $selectedOrigin) => $query->where('source', $selectedOrigin))
            ->get()
            ->map(function (Dependent $dependent) {
                $submittedAt = $dependent->created_at instanceof Carbon ? $dependent->created_at : Carbon::parse($dependent->created_at);

                return [
                    'type' => 'dependent',
                    'type_label' => 'Dependente',
                    'id' => $dependent->id,
                    'name' => $dependent->user->name,
                    'email' => $dependent->user->email,
                    'branch_name' => $dependent->branch->name,
                    'context' => $dependent->member->user->name,
                    'status_label' => $dependent->status->label(),
                    'origin' => $dependent->source->value,
                    'origin_label' => $dependent->source->label(),
                    'submitted_at' => $submittedAt,
                    'submitted_age_label' => $this->ageLabel($submittedAt),
                    'age_bucket' => $this->ageBucket($submittedAt),
                    'show_url' => route('dependentes.show', $dependent),
                    'status_url' => route('dependents.status', $dependent),
                ];
            });
    }

    protected function matchesAgeFilter(Carbon $submittedAt, ?string $age): bool
    {
        if (! $age) {
            return true;
        }

        return $this->ageBucket($submittedAt) === $age;
    }

    protected function ageBucket(Carbon $submittedAt): string
    {
        $days = $this->elapsedDays($submittedAt);

        return match (true) {
            $days <= 3 => 'recent',
            $days <= 7 => 'attention',
            default => 'stale',
        };
    }

    protected function ageLabel(Carbon $submittedAt): string
    {
        $hours = $this->elapsedHours($submittedAt);
        $days = intdiv($hours, 24);

        if ($hours < 1) {
            return 'Agora';
        }

        if ($hours < 24) {
            return $hours === 1 ? '1 h' : "{$hours} h";
        }

        if ($days === 1) {
            return '1 dia';
        }

        return "{$days} dias";
    }

    protected function elapsedDays(Carbon $submittedAt): int
    {
        return intdiv($this->elapsedHours($submittedAt), 24);
    }

    protected function elapsedHours(Carbon $submittedAt): int
    {
        return max((int) floor($submittedAt->diffInHours(now(), true)), 0);
    }
}
