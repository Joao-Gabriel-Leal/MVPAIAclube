<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateInvoicesRequest;
use App\Http\Requests\MarkInvoicePaidRequest;
use App\Models\Branch;
use App\Models\MembershipInvoice;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceController extends Controller
{
    public function index(Request $request, BillingService $billingService)
    {
        $billingService->refreshOverdueStatuses();

        $query = MembershipInvoice::query()->with(['member.user', 'branch'])->latest('billing_period');

        if ($request->user()->isAdminBranch()) {
            $query->where('branch_id', $request->user()->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('finance.index', [
            'invoices' => $query->paginate(20)->withQueryString(),
            'branches' => Branch::query()->active()->orderBy('name')->get(),
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

        return redirect()->route('finance.index')->with('status', 'Mensalidades geradas com sucesso.');
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

        return redirect()->route('finance.index')->with('status', 'Pagamento baixado com sucesso.');
    }
}
