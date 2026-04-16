<?php

namespace App\Services;

use App\Enums\DependentStatus;
use App\Enums\InvoiceStatus;
use App\Enums\MembershipStatus;
use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\Plan;
use App\Models\Reservation;
use App\Models\User;

class BranchHubService
{
    public function __construct(
        protected ReportService $reportService,
    ) {
    }

    public function build(Branch $branch, User $user, array $filters = []): array
    {
        $start = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $end = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        $status = $filters['status'] ?? null;

        $resources = ClubResource::query()
            ->with(['plans', 'schedules', 'blocks'])
            ->where('branch_id', $branch->id)
            ->orderBy('name')
            ->get();

        $members = Member::query()
            ->with(['user', 'plan'])
            ->withCount('dependents')
            ->where('primary_branch_id', $branch->id)
            ->latest()
            ->get();

        $dependents = Dependent::query()
            ->with(['user', 'member.user'])
            ->where('branch_id', $branch->id)
            ->latest()
            ->get();

        $reservations = Reservation::query()
            ->with(['resource', 'member.user', 'reserver'])
            ->where('branch_id', $branch->id)
            ->latest('reservation_date')
            ->take(10)
            ->get();

        $invoices = MembershipInvoice::query()
            ->with(['member.user'])
            ->where('branch_id', $branch->id)
            ->latest('billing_period')
            ->take(10)
            ->get();

        $plans = Plan::query()
            ->with([
                'resources' => fn ($query) => $query
                    ->with('branch')
                    ->where('club_resources.branch_id', $branch->id),
            ])
            ->withCount([
                'members as branch_members_count' => fn ($query) => $query->where('primary_branch_id', $branch->id),
                'resources as branch_resources_count' => fn ($query) => $query->where('club_resources.branch_id', $branch->id),
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->filter(fn (Plan $plan) => $plan->branch_members_count > 0 || $plan->branch_resources_count > 0)
            ->values();

        $reportData = $this->reportService->generate($user, [
            'branch_id' => $branch->id,
            'start_date' => $start,
            'end_date' => $end,
            'status' => $status,
        ]);

        return [
            'summaryCards' => [
                'Associados ativos' => $members->where('status', MembershipStatus::Active)->count(),
                'Dependentes ativos' => $dependents->where('status', DependentStatus::Active)->count(),
                'Reservas no mes' => Reservation::query()
                    ->where('branch_id', $branch->id)
                    ->whereMonth('reservation_date', now()->month)
                    ->whereYear('reservation_date', now()->year)
                    ->count(),
                'Receita prevista' => (float) MembershipInvoice::query()
                    ->where('branch_id', $branch->id)
                    ->whereMonth('billing_period', now()->month)
                    ->whereYear('billing_period', now()->year)
                    ->sum('amount'),
                'Mensalidades pendentes' => $invoices->where('status', InvoiceStatus::Pending)->count(),
                'Recursos ativos' => $resources->where('is_active', true)->count(),
            ],
            'highlights' => [
                'members' => $members->count(),
                'dependents' => $dependents->count(),
                'resources' => $resources->count(),
                'plans' => $plans->count(),
                'pendingMembers' => $members->where('status', MembershipStatus::Pending)->count(),
                'pendingDependents' => $dependents->where('status', DependentStatus::Pending)->count(),
            ],
            'resources' => $resources,
            'members' => $members,
            'dependents' => $dependents,
            'reservations' => $reservations,
            'invoices' => $invoices,
            'plans' => $plans,
            'reportData' => $reportData,
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
                'status' => $status,
            ],
        ];
    }
}
