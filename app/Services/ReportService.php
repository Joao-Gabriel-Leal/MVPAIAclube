<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ReportService
{
    public function generate(User $user, array $filters = []): array
    {
        $branchId = $user->isAdminBranch() ? $user->branch_id : ($filters['branch_id'] ?? null);
        $start = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $end = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        $status = $filters['status'] ?? null;

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

        return [
            'filters' => [
                'branch_id' => $branchId,
                'start_date' => $start,
                'end_date' => $end,
                'status' => $status,
            ],
            'branches' => $branches,
            'selectedBranch' => $branchId ? Branch::query()->find($branchId) : null,
            'summary' => [
                'members' => $members->count(),
                'dependents' => $dependents->count(),
                'reservations' => $reservations->count(),
                'resources' => $resources->count(),
                'revenue' => (float) $invoices->sum('amount'),
                'averageTicket' => (float) round($invoices->avg('amount') ?? 0, 2),
            ],
            'membersByBranch' => $membersByBranch,
            'membersByStatus' => $membersByStatus,
            'invoicesByStatus' => $invoicesByStatus,
            'resourceUsage' => $resourceUsage,
            'reservationsByBranch' => $reservationsByBranch,
            'reservationTrend' => $reservationTrend,
            'dependentsByPlan' => $dependentsByPlan,
            'dependentsByHolder' => $dependentsByHolder,
            'charts' => [
                'membersByBranch' => $this->barChart(
                    'Associados',
                    $membersByBranch->keys()->values()->all(),
                    $membersByBranch->values()->all(),
                    'rgba(124, 58, 237, 0.82)',
                    'rgba(109, 40, 217, 1)'
                ),
                'membersByStatus' => $this->doughnutChart(
                    $membersByStatus->keys()->values()->all(),
                    $membersByStatus->values()->all(),
                    ['#7c3aed', '#ec4899', '#f59e0b', '#10b981']
                ),
                'invoicesByStatus' => $this->doughnutChart(
                    $invoicesByStatus->keys()->values()->all(),
                    $invoicesByStatus->pluck('count')->values()->all(),
                    ['#14b8a6', '#f97316', '#e11d48', '#6366f1']
                ),
                'resourceUsage' => $this->barChart(
                    'Reservas',
                    $resourceUsage->keys()->values()->all(),
                    $resourceUsage->values()->all(),
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(5, 150, 105, 1)'
                ),
                'reservationTrend' => $this->lineChart(
                    'Reservas por dia',
                    $reservationTrend->keys()->values()->all(),
                    $reservationTrend->values()->all(),
                    '#7c3aed'
                ),
                'reservationsByBranch' => $this->barChart(
                    'Reservas',
                    $reservationsByBranch->keys()->values()->all(),
                    $reservationsByBranch->values()->all(),
                    'rgba(59, 130, 246, 0.78)',
                    'rgba(37, 99, 235, 1)'
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
                    'hoverOffset' => 8,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                    ],
                ],
                'cutout' => '62%',
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
                    'backgroundColor' => 'rgba(124, 58, 237, 0.15)',
                    'tension' => 0.35,
                    'fill' => true,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
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
}
