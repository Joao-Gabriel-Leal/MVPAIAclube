<?php

namespace App\Services;

use App\Enums\DependentStatus;
use App\Enums\InventoryMovementType;
use App\Enums\ProposalOrigin;
use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\Dependent;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\Reservation;
use App\Models\User;
use App\Support\AdminMetricCards;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ReportService
{
    protected const BRAND_BLUE = '#2958B8';

    protected const BRAND_BLUE_SOFT = '#4E79CC';

    protected const BRAND_YELLOW = '#F2CF2F';

    protected const BRAND_YELLOW_DEEP = '#D4A919';

    public function generate(User $user, array $filters = []): array
    {
        $branchId = $user->isAdminBranch() ? $user->branch_id : ($filters['branch_id'] ?? null);
        $start = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $end = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        $status = $filters['status'] ?? null;
        $proposalOrigin = $filters['proposal_origin'] ?? null;
        $inventoryCategory = $filters['inventory_category'] ?? null;
        $selectedBranch = $branchId ? Branch::query()->find($branchId) : null;

        $branches = $user->isAdminMatrix()
            ? Branch::query()->active()->orderBy('name')->get()
            : Branch::query()->active()->whereKey($branchId)->orderBy('name')->get();

        $members = Member::query()
            ->with(['user', 'primaryBranch', 'plan'])
            ->when($branchId, fn (Builder $query) => $query->where('primary_branch_id', $branchId))
            ->when($status, fn (Builder $query, $selectedStatus) => $query->where('status', $selectedStatus))
            ->get();

        $dependents = Dependent::query()
            ->with(['member.plan', 'member.user', 'user', 'branch'])
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->when($status, fn (Builder $query, $selectedStatus) => $query->where('status', $selectedStatus))
            ->get();

        $invoices = MembershipInvoice::query()
            ->with(['member.user', 'branch'])
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->whereBetween('billing_period', [$start, $end])
            ->get();

        $reservations = Reservation::query()
            ->with(['member.user', 'resource', 'branch'])
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->whereBetween('reservation_date', [$start, $end])
            ->get();

        $resources = ClubResource::query()
            ->with('branch')
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->get();

        $pendingMembers = Member::query()
            ->with(['user', 'primaryBranch', 'plan'])
            ->where('status', \App\Enums\MembershipStatus::Pending)
            ->when($branchId, fn (Builder $query) => $query->where('primary_branch_id', $branchId))
            ->when($proposalOrigin, fn (Builder $query, $selectedOrigin) => $query->where('source', $selectedOrigin))
            ->get();

        $pendingDependents = Dependent::query()
            ->with(['member.user', 'user', 'branch'])
            ->where('status', DependentStatus::Pending)
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->when(
                $proposalOrigin === ProposalOrigin::Public->value,
                fn (Builder $query) => $query->whereRaw('1 = 0'),
                fn (Builder $query) => $query->when($proposalOrigin, fn (Builder $dependentQuery, $selectedOrigin) => $dependentQuery->where('source', $selectedOrigin))
            )
            ->get();

        $inventoryItems = InventoryItem::query()
            ->with(['branch', 'resource'])
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->when($inventoryCategory, fn (Builder $query, $selectedCategory) => $query->where('category', $selectedCategory))
            ->get();

        $inventoryMovements = InventoryMovement::query()
            ->with(['item', 'branch', 'resource', 'reservation'])
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->when($inventoryCategory, fn (Builder $query, $selectedCategory) => $query->whereHas('item', fn (Builder $itemQuery) => $itemQuery->where('category', $selectedCategory)))
            ->whereBetween('occurred_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->get();

        $membersByBranch = $members
            ->groupBy(fn (Member $member) => $member->primaryBranch?->name ?? 'Sem filial')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $membersByStatus = $members
            ->groupBy(fn (Member $member) => $member->status->label())
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $invoicesByStatus = $invoices
            ->groupBy(fn (MembershipInvoice $invoice) => $invoice->status->label())
            ->map(fn ($group) => [
                'count' => $group->count(),
                'amount' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('count');

        $resourceUsage = $reservations
            ->groupBy(fn (Reservation $reservation) => $reservation->resource?->name ?? 'Sem recurso')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->take(8);

        $reservationsByBranch = $reservations
            ->groupBy(fn (Reservation $reservation) => $reservation->branch?->name ?? 'Sem filial')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $reservationTrend = $reservations
            ->groupBy(fn (Reservation $reservation) => $reservation->reservation_date->format('Y-m-d'))
            ->map(fn ($group) => $group->count())
            ->sortKeys()
            ->mapWithKeys(fn ($count, $date) => [Carbon::parse($date)->format('d/m') => $count]);

        $dependentsByPlan = $dependents
            ->groupBy(fn (Dependent $dependent) => $dependent->member?->plan?->name ?? 'Sem plano')
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $dependentsByHolder = $dependents
            ->groupBy(fn (Dependent $dependent) => $dependent->member?->user?->name ?? 'Sem titular')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->take(8);

        $proposalTypes = collect([
            'Associados' => $pendingMembers->count(),
            'Dependentes' => $pendingDependents->count(),
        ]);

        $proposalOrigins = collect([
            ProposalOrigin::Manual->label() => $pendingMembers->where('source', ProposalOrigin::Manual)->count() + $pendingDependents->where('source', ProposalOrigin::Manual)->count(),
            ProposalOrigin::Public->label() => $pendingMembers->where('source', ProposalOrigin::Public)->count(),
        ]);

        $lowStockItems = $inventoryItems
            ->filter(fn (InventoryItem $item) => $item->is_low_stock)
            ->sortBy('current_quantity')
            ->values();

        $consumptionMovements = $inventoryMovements
            ->filter(fn (InventoryMovement $movement) => $movement->movement_type === InventoryMovementType::Exit);

        $consumptionByCategory = $consumptionMovements
            ->groupBy(fn (InventoryMovement $movement) => $movement->item?->category ?? 'Sem categoria')
            ->map(fn ($group) => (float) round($group->sum(fn (InventoryMovement $movement) => abs((float) $movement->quantity)), 2))
            ->sortDesc();

        $consumptionByItem = $consumptionMovements
            ->groupBy(fn (InventoryMovement $movement) => $movement->item?->name ?? 'Item removido')
            ->map(fn ($group) => (float) round($group->sum(fn (InventoryMovement $movement) => abs((float) $movement->quantity)), 2))
            ->sortDesc()
            ->take(8);

        return [
            'filters' => [
                'branch_id' => $branchId,
                'start_date' => $start,
                'end_date' => $end,
                'status' => $status,
                'proposal_origin' => $proposalOrigin,
                'inventory_category' => $inventoryCategory,
            ],
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'inventoryCategories' => InventoryItem::query()
                ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
                ->select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
            'summaryCards' => [
                'members' => AdminMetricCards::count(
                    'Associados',
                    $members->count(),
                    AdminMetricCards::dateRangeContext($start, $end, $selectedBranch)
                ),
                'dependents' => AdminMetricCards::count(
                    'Dependentes',
                    $dependents->count(),
                    AdminMetricCards::dateRangeContext($start, $end, $selectedBranch)
                ),
                'reservations' => AdminMetricCards::count(
                    'Reservas',
                    $reservations->count(),
                    AdminMetricCards::dateRangeContext($start, $end, $selectedBranch)
                ),
                'revenue' => AdminMetricCards::currency(
                    'Mensalidades previstas no periodo',
                    (float) $invoices->sum('amount'),
                    AdminMetricCards::dateRangeContext($start, $end, $selectedBranch),
                    AdminMetricCards::detailCount($invoices->count(), 'mensalidade')
                ),
                'pendingProposals' => AdminMetricCards::count(
                    'Propostas pendentes',
                    $pendingMembers->count() + $pendingDependents->count(),
                    AdminMetricCards::scopeContext('Fila atual', $selectedBranch)
                ),
                'inventoryAlerts' => AdminMetricCards::count(
                    'Alertas de estoque',
                    $lowStockItems->count(),
                    AdminMetricCards::scopeContext('Estoque atual', $selectedBranch)
                ),
            ],
            'summary' => [
                'members' => $members->count(),
                'dependents' => $dependents->count(),
                'reservations' => $reservations->count(),
                'resources' => $resources->count(),
                'revenue' => (float) $invoices->sum('amount'),
                'averageTicket' => (float) round($invoices->avg('amount') ?? 0, 2),
                'pendingProposals' => $pendingMembers->count() + $pendingDependents->count(),
                'inventoryItems' => $inventoryItems->count(),
                'inventoryAlerts' => $lowStockItems->count(),
                'reservationLinkedConsumption' => (float) round($consumptionMovements->whereNotNull('reservation_id')->sum(fn (InventoryMovement $movement) => abs((float) $movement->quantity)), 2),
            ],
            'membersByBranch' => $membersByBranch,
            'membersByStatus' => $membersByStatus,
            'invoicesByStatus' => $invoicesByStatus,
            'resourceUsage' => $resourceUsage,
            'reservationsByBranch' => $reservationsByBranch,
            'reservationTrend' => $reservationTrend,
            'dependentsByPlan' => $dependentsByPlan,
            'dependentsByHolder' => $dependentsByHolder,
            'proposalTypes' => $proposalTypes,
            'proposalOrigins' => $proposalOrigins,
            'lowStockItems' => $lowStockItems,
            'inventoryConsumptionByCategory' => $consumptionByCategory,
            'inventoryConsumptionByItem' => $consumptionByItem,
            'charts' => [
                'membersByBranch' => $this->barChart(
                    'Associados',
                    $membersByBranch->keys()->values()->all(),
                    $membersByBranch->values()->all(),
                    $this->hexToRgba(self::BRAND_BLUE, 0.82),
                    self::BRAND_BLUE
                ),
                'membersByStatus' => $this->doughnutChart(
                    $membersByStatus->keys()->values()->all(),
                    $membersByStatus->values()->all(),
                    [self::BRAND_BLUE, self::BRAND_YELLOW, '#F59E0B', '#94A3B8']
                ),
                'invoicesByStatus' => $this->doughnutChart(
                    $invoicesByStatus->keys()->values()->all(),
                    $invoicesByStatus->pluck('count')->values()->all(),
                    [self::BRAND_BLUE, self::BRAND_YELLOW, '#10B981', '#F59E0B']
                ),
                'resourceUsage' => $this->barChart(
                    'Reservas',
                    $resourceUsage->keys()->values()->all(),
                    $resourceUsage->values()->all(),
                    $this->hexToRgba(self::BRAND_YELLOW, 0.82),
                    self::BRAND_YELLOW_DEEP
                ),
                'reservationTrend' => $this->lineChart(
                    'Reservas por dia',
                    $reservationTrend->keys()->values()->all(),
                    $reservationTrend->values()->all(),
                    self::BRAND_BLUE
                ),
                'reservationsByBranch' => $this->barChart(
                    'Reservas',
                    $reservationsByBranch->keys()->values()->all(),
                    $reservationsByBranch->values()->all(),
                    $this->hexToRgba(self::BRAND_BLUE_SOFT, 0.78),
                    self::BRAND_BLUE_SOFT
                ),
                'proposalTypes' => $this->barChart(
                    'Propostas',
                    $proposalTypes->keys()->values()->all(),
                    $proposalTypes->values()->all(),
                    $this->hexToRgba(self::BRAND_BLUE_SOFT, 0.76),
                    self::BRAND_BLUE_SOFT
                ),
                'proposalOrigins' => $this->doughnutChart(
                    $proposalOrigins->keys()->values()->all(),
                    $proposalOrigins->values()->all(),
                    [self::BRAND_BLUE, self::BRAND_YELLOW]
                ),
                'inventoryConsumptionByCategory' => $this->barChart(
                    'Saidas',
                    $consumptionByCategory->keys()->values()->all(),
                    $consumptionByCategory->values()->all(),
                    $this->hexToRgba(self::BRAND_YELLOW, 0.82),
                    self::BRAND_YELLOW_DEEP
                ),
                'inventoryConsumptionByItem' => $this->barChart(
                    'Saidas',
                    $consumptionByItem->keys()->values()->all(),
                    $consumptionByItem->values()->all(),
                    $this->hexToRgba(self::BRAND_BLUE, 0.82),
                    self::BRAND_BLUE
                ),
            ],
        ];
    }

    protected function barChart(string $label, array $labels, array $data, string $backgroundColor, string $borderColor): array
    {
        return [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'borderWidth' => 1,
                    'borderRadius' => 10,
                    'maxBarThickness' => 34,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'grid' => ['color' => 'rgba(226, 232, 240, 0.8)'],
                    ],
                    'x' => [
                        'grid' => ['display' => false],
                    ],
                ],
            ],
        ];
    }

    protected function doughnutChart(array $labels, array $data, array $colors): array
    {
        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                    'hoverOffset' => 4,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                    ],
                ],
                'cutout' => '74%',
            ],
        ];
    }

    protected function lineChart(string $label, array $labels, array $data, string $borderColor): array
    {
        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                    'borderColor' => $borderColor,
                    'backgroundColor' => $this->hexToRgba($borderColor, 0.14),
                    'tension' => 0.32,
                    'fill' => true,
                    'pointRadius' => 2.5,
                    'pointHoverRadius' => 4,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'grid' => ['color' => 'rgba(226, 232, 240, 0.8)'],
                    ],
                    'x' => [
                        'grid' => ['display' => false],
                    ],
                ],
            ],
        ];
    }

    protected function hexToRgba(string $hex, float $alpha): string
    {
        $normalized = ltrim($hex, '#');

        if (strlen($normalized) !== 6) {
            return "rgba(41, 88, 184, {$alpha})";
        }

        $red = hexdec(substr($normalized, 0, 2));
        $green = hexdec(substr($normalized, 2, 2));
        $blue = hexdec(substr($normalized, 4, 2));

        return "rgba({$red}, {$green}, {$blue}, {$alpha})";
    }
}
