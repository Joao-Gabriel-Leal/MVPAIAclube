<?php

namespace App\Services;

use App\Enums\DependentStatus;
use App\Enums\InvoiceStatus;
use App\Enums\MembershipStatus;
use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\Dependent;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\Plan;
use App\Models\Reservation;
use App\Models\User;
use App\Support\AdminMetricCards;
use Illuminate\Support\Carbon;

class BranchHubService
{
    public function __construct(
        protected ReportService $reportService,
        protected ProposalService $proposalService,
    ) {
    }

    public function build(Branch $branch, User $user, array $filters = []): array
    {
        $start = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $end = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        $status = $filters['status'] ?? null;
        $proposalOrigin = $filters['proposal_origin'] ?? null;
        $inventoryCategory = $filters['inventory_category'] ?? null;
        $billingPeriodValue = (string) ($filters['billing_period'] ?? now()->format('Y-m'));
        $billingPeriod = preg_match('/^\d{4}-\d{2}$/', $billingPeriodValue)
            ? Carbon::createFromFormat('Y-m', $billingPeriodValue)->startOfMonth()
            : now()->startOfMonth();
        $currentMonth = now()->startOfMonth();

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

        $inventoryItems = InventoryItem::query()
            ->with('resource')
            ->where('branch_id', $branch->id)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        $lowStockItems = $inventoryItems
            ->filter(fn (InventoryItem $item) => $item->is_low_stock)
            ->sortBy('current_quantity')
            ->take(8)
            ->values();
        $recentInventoryMovements = InventoryMovement::query()
            ->with(['item', 'resource', 'reservation.resource', 'actor'])
            ->where('branch_id', $branch->id)
            ->latest('occurred_at')
            ->take(10)
            ->get();
        $proposalCounts = $this->proposalService->pendingCounts($branch->id);
        $recentProposals = $this->proposalService->recent($branch->id, 6);

        $invoiceQuery = MembershipInvoice::query()
            ->with(['member.user'])
            ->where('branch_id', $branch->id)
            ->whereYear('billing_period', $billingPeriod->year)
            ->whereMonth('billing_period', $billingPeriod->month);
        $invoiceSnapshot = (clone $invoiceQuery)->get();
        $invoices = (clone $invoiceQuery)
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
            'proposal_origin' => $proposalOrigin,
            'inventory_category' => $inventoryCategory,
        ]);

        return [
            'summaryCards' => [
                'activeMembers' => AdminMetricCards::count(
                    'Associados ativos',
                    $members->where('status', MembershipStatus::Active)->count(),
                    AdminMetricCards::scopeContext('Base atual', $branch)
                ),
                'activeDependents' => AdminMetricCards::count(
                    'Dependentes ativos',
                    $dependents->where('status', DependentStatus::Active)->count(),
                    AdminMetricCards::scopeContext('Base atual', $branch)
                ),
                'pendingProposals' => AdminMetricCards::count(
                    'Propostas pendentes',
                    $proposalCounts['total'],
                    AdminMetricCards::scopeContext('Fila atual', $branch)
                ),
                'monthlyReservations' => AdminMetricCards::count(
                    'Reservas no mes',
                    Reservation::query()
                        ->where('branch_id', $branch->id)
                        ->whereMonth('reservation_date', $currentMonth->month)
                        ->whereYear('reservation_date', $currentMonth->year)
                        ->count(),
                    AdminMetricCards::scopeContext('Mes atual '.$currentMonth->format('m/Y'), $branch)
                ),
                'plannedMembershipFees' => AdminMetricCards::currency(
                    'Mensalidades previstas',
                    (float) MembershipInvoice::query()
                        ->where('branch_id', $branch->id)
                        ->whereMonth('billing_period', $currentMonth->month)
                        ->whereYear('billing_period', $currentMonth->year)
                        ->sum('amount'),
                    AdminMetricCards::competencyContext($currentMonth, $branch)
                ),
                'inventoryAlerts' => AdminMetricCards::count(
                    'Itens em alerta',
                    $lowStockItems->count(),
                    AdminMetricCards::scopeContext('Estoque atual', $branch)
                ),
                'pendingInvoices' => AdminMetricCards::count(
                    'Mensalidades pendentes',
                    MembershipInvoice::query()
                        ->where('branch_id', $branch->id)
                        ->where('status', InvoiceStatus::Pending)
                        ->count(),
                    AdminMetricCards::scopeContext('Carteira atual', $branch)
                ),
            ],
            'highlights' => [
                'activeMembers' => $members->where('status', MembershipStatus::Active)->count(),
                'cancelledMembers' => $members->where('status', MembershipStatus::Cancelled)->count(),
                'activeDependents' => $dependents->where('status', DependentStatus::Active)->count(),
                'cancelledDependents' => $dependents->where('status', DependentStatus::Cancelled)->count(),
                'resources' => $resources->count(),
                'plans' => $plans->count(),
                'pendingMembers' => $members->where('status', MembershipStatus::Pending)->count(),
                'pendingDependents' => $dependents->where('status', DependentStatus::Pending)->count(),
                'pendingTotal' => $proposalCounts['total'],
                'lowStock' => $lowStockItems->count(),
            ],
            'resources' => $resources,
            'members' => $members,
            'dependents' => $dependents,
            'reservations' => $reservations,
            'inventoryItems' => $inventoryItems,
            'lowStockItems' => $lowStockItems,
            'recentInventoryMovements' => $recentInventoryMovements,
            'recentProposals' => $recentProposals,
            'invoices' => $invoices,
            'financeSummary' => [
                'expected' => AdminMetricCards::currency(
                    'Mensalidades previstas',
                    (float) $invoiceSnapshot->sum('amount'),
                    AdminMetricCards::competencyContext($billingPeriod, $branch),
                    AdminMetricCards::detailCount($invoiceSnapshot->count(), 'mensalidade')
                ),
                'pending' => AdminMetricCards::currency(
                    'Pendentes',
                    (float) $invoiceSnapshot->where('status', InvoiceStatus::Pending)->sum('amount'),
                    AdminMetricCards::competencyContext($billingPeriod, $branch),
                    AdminMetricCards::detailCount($invoiceSnapshot->where('status', InvoiceStatus::Pending)->count(), 'mensalidade')
                ),
                'paid' => AdminMetricCards::currency(
                    'Pagas',
                    (float) $invoiceSnapshot->where('status', InvoiceStatus::Paid)->sum('amount'),
                    AdminMetricCards::competencyContext($billingPeriod, $branch),
                    AdminMetricCards::detailCount($invoiceSnapshot->where('status', InvoiceStatus::Paid)->count(), 'mensalidade')
                ),
                'overdue' => AdminMetricCards::currency(
                    'Atrasadas',
                    (float) $invoiceSnapshot->where('status', InvoiceStatus::Overdue)->sum('amount'),
                    AdminMetricCards::competencyContext($billingPeriod, $branch),
                    AdminMetricCards::detailCount($invoiceSnapshot->where('status', InvoiceStatus::Overdue)->count(), 'mensalidade')
                ),
            ],
            'financeFilters' => [
                'billing_period' => $billingPeriod->format('Y-m'),
            ],
            'plans' => $plans,
            'reportData' => $reportData,
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
                'status' => $status,
                'proposal_origin' => $proposalOrigin,
                'inventory_category' => $inventoryCategory,
                'billing_period' => $billingPeriod->format('Y-m'),
            ],
        ];
    }
}
