<?php

namespace App\Services;

use App\Enums\DependentStatus;
use App\Enums\InvoiceStatus;
use App\Enums\MembershipStatus;
use App\Enums\ReservationStatus;
use App\Models\Branch;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function summary(User $user): array
    {
        return Cache::remember("dashboard.summary.{$user->id}", now()->addMinutes(5), function () use ($user) {
            if ($user->isAdminMatrix()) {
                return $this->matrixSummary();
            }

            if ($user->isAdminBranch()) {
                return $this->branchSummary($user);
            }

            return $this->memberSummary($user);
        });
    }

    protected function matrixSummary(): array
    {
        return [
            'scope' => 'matrix',
            'cards' => [
                'Total de filiais' => Branch::query()->count(),
                'Associados ativos' => Member::query()->where('status', MembershipStatus::Active)->count(),
                'Associados pendentes' => Member::query()->where('status', MembershipStatus::Pending)->count(),
                'Inadimplentes' => Member::query()->where('status', MembershipStatus::Delinquent)->count(),
                'Reservas do mes' => Reservation::query()->whereMonth('reservation_date', now()->month)->count(),
                'Faturamento previsto' => (float) MembershipInvoice::query()->whereMonth('billing_period', now()->month)->sum('amount'),
                'Dependentes' => Dependent::query()->count(),
            ],
            'branchComparisons' => Branch::query()
                ->withCount([
                    'members as active_members_count' => fn ($query) => $query->where('status', MembershipStatus::Active),
                    'resources',
                ])
                ->get(),
            'pendingMembers' => Member::query()->with(['user', 'primaryBranch', 'plan'])->where('status', MembershipStatus::Pending)->latest()->take(8)->get(),
            'recentInvoices' => MembershipInvoice::query()->with(['member.user', 'branch'])->latest()->take(8)->get(),
        ];
    }

    protected function branchSummary(User $user): array
    {
        return [
            'scope' => 'branch',
            'cards' => [
                'Associados ativos' => Member::query()->where('primary_branch_id', $user->branch_id)->where('status', MembershipStatus::Active)->count(),
                'Associados pendentes' => Member::query()->where('primary_branch_id', $user->branch_id)->where('status', MembershipStatus::Pending)->count(),
                'Dependentes pendentes' => Dependent::query()->where('branch_id', $user->branch_id)->where('status', DependentStatus::Pending)->count(),
                'Mensalidades pendentes' => MembershipInvoice::query()->where('branch_id', $user->branch_id)->where('status', InvoiceStatus::Pending)->count(),
                'Reservas do mes' => Reservation::query()->where('branch_id', $user->branch_id)->whereMonth('reservation_date', now()->month)->count(),
                'Receita prevista' => (float) MembershipInvoice::query()->where('branch_id', $user->branch_id)->whereMonth('billing_period', now()->month)->sum('amount'),
            ],
            'pendingMembers' => Member::query()->with(['user', 'primaryBranch', 'plan'])->where('primary_branch_id', $user->branch_id)->where('status', MembershipStatus::Pending)->latest()->take(8)->get(),
            'recentReservations' => Reservation::query()->with(['member.user', 'resource'])->where('branch_id', $user->branch_id)->latest()->take(8)->get(),
            'recentInvoices' => MembershipInvoice::query()->with(['member.user'])->where('branch_id', $user->branch_id)->latest()->take(8)->get(),
        ];
    }

    protected function memberSummary(User $user): array
    {
        $viewer = $user->loadMissing([
            'member.plan',
            'member.primaryBranch',
            'dependent.branch',
            'dependent.member.user',
            'dependent.member.plan',
        ]);
        $member = $user->activeMember()?->load(['plan', 'primaryBranch', 'dependents.user', 'invoices', 'reservations.resource']);

        return [
            'scope' => 'member',
            'viewer' => $viewer,
            'member' => $member,
            'cards' => [
                'Plano atual' => $member?->plan?->name ?? '-',
                'Filial principal' => $member?->primaryBranch?->name ?? '-',
                'Mensalidades abertas' => $member?->invoices->where('status', InvoiceStatus::Pending)->count() ?? 0,
                'Reservas confirmadas' => $member?->reservations->where('status', ReservationStatus::Confirmed)->count() ?? 0,
                'Dependentes' => $member?->dependents->count() ?? 0,
                'Reservas gratis restantes' => max(($member?->plan?->free_reservations_per_month ?? 0) - ($member?->freeReservationsUsedInMonth(now()) ?? 0), 0),
            ],
            'recentReservations' => $member?->reservations?->sortByDesc('reservation_date')->take(8) ?? collect(),
            'recentInvoices' => $member?->invoices?->sortByDesc('billing_period')->take(8) ?? collect(),
            'dependents' => $member?->dependents ?? collect(),
        ];
    }
}
