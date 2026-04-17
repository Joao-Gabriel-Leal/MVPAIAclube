<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Http\Requests\GenerateInvoicesRequest;
use App\Http\Requests\MarkInvoicePaidRequest;
use App\Models\Branch;
use App\Models\MembershipInvoice;
use App\Services\BillingService;
use App\Support\AdminMetricCards;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    public function index(Request $request, BillingService $billingService)
    {
        $billingService->refreshOverdueStatuses();

        $filters = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'status' => ['nullable', Rule::in(collect(InvoiceStatus::cases())->map->value->all())],
            'billing_period' => ['nullable', 'date_format:Y-m'],
        ]);

        $selectedBranchId = $request->user()->isAdminBranch()
            ? $request->user()->branch_id
            : (isset($filters['branch_id']) ? (int) $filters['branch_id'] : null);
        $selectedBranch = $selectedBranchId ? Branch::query()->find($selectedBranchId) : null;
        $statusFilter = $filters['status'] ?? null;
        $billingPeriod = isset($filters['billing_period'])
            ? Carbon::createFromFormat('Y-m', $filters['billing_period'])->startOfMonth()
            : now()->startOfMonth();

        $query = MembershipInvoice::query()
            ->with(['member.user', 'branch'])
            ->whereYear('billing_period', $billingPeriod->year)
            ->whereMonth('billing_period', $billingPeriod->month);

        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

        $summarySnapshot = (clone $query)->get();

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        return view('finance.index', [
            'invoices' => $query->latest('billing_period')->paginate(20)->withQueryString(),
            'branches' => Branch::query()->active()->orderBy('name')->get(),
            'selectedBranch' => $selectedBranch,
            'statusFilter' => $statusFilter,
            'billingPeriod' => $billingPeriod,
            'filters' => [
                'branch_id' => $selectedBranchId,
                'status' => $statusFilter,
                'billing_period' => $billingPeriod->format('Y-m'),
            ],
            'statuses' => InvoiceStatus::cases(),
            'summary' => [
                'expected' => AdminMetricCards::currency(
                    'Mensalidades previstas',
                    (float) $summarySnapshot->sum('amount'),
                    AdminMetricCards::competencyContext($billingPeriod, $selectedBranch),
                    AdminMetricCards::detailCount($summarySnapshot->count(), 'mensalidade')
                ),
                'pending' => AdminMetricCards::currency(
                    'Pendentes',
                    (float) $summarySnapshot->where('status', InvoiceStatus::Pending)->sum('amount'),
                    AdminMetricCards::competencyContext($billingPeriod, $selectedBranch),
                    AdminMetricCards::detailCount($summarySnapshot->where('status', InvoiceStatus::Pending)->count(), 'mensalidade')
                ),
                'paid' => AdminMetricCards::currency(
                    'Pagas',
                    (float) $summarySnapshot->where('status', InvoiceStatus::Paid)->sum('amount'),
                    AdminMetricCards::competencyContext($billingPeriod, $selectedBranch),
                    AdminMetricCards::detailCount($summarySnapshot->where('status', InvoiceStatus::Paid)->count(), 'mensalidade')
                ),
                'overdue' => AdminMetricCards::currency(
                    'Atrasadas',
                    (float) $summarySnapshot->where('status', InvoiceStatus::Overdue)->sum('amount'),
                    AdminMetricCards::competencyContext($billingPeriod, $selectedBranch),
                    AdminMetricCards::detailCount($summarySnapshot->where('status', InvoiceStatus::Overdue)->count(), 'mensalidade')
                ),
            ],
        ]);
    }

    public function generate(GenerateInvoicesRequest $request, BillingService $billingService)
    {
        $branch = $request->filled('branch_id')
            ? Branch::query()->findOrFail($request->integer('branch_id'))
            : ($request->user()->isAdminBranch() ? Branch::query()->findOrFail($request->user()->branch_id) : null);

        $billingService->generateMonthlyInvoices(
            Carbon::parse($request->validated('billing_period').'-01'),
            $branch,
            $request->user()
        );

        return redirect()->back()->with('status', 'Mensalidades geradas com sucesso.');
    }

    public function markPaid(MarkInvoicePaidRequest $request, MembershipInvoice $membershipInvoice, BillingService $billingService)
    {
        $this->authorize('markPaid', $membershipInvoice);

        $billingService->markPaid(
            $membershipInvoice,
            (float) $request->validated('paid_amount'),
            $request->user(),
            $request->validated('notes')
        );

        return redirect()->back()->with('status', 'Pagamento baixado com sucesso.');
    }
}
