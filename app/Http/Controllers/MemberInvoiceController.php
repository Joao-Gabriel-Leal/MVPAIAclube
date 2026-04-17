<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\MembershipInvoice;
use Illuminate\Http\Request;

class MemberInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = MembershipInvoice::query()
            ->with(['branch', 'member.user'])
            ->latest('billing_period');

        if ($request->user()->isMember()) {
            $query->where('member_id', $request->user()->member?->id);
        } elseif ($request->user()->isDependent()) {
            $query->where('member_id', $request->user()->dependent?->member_id);
        } else {
            abort(403);
        }

        $invoices = (clone $query)->paginate(12)->withQueryString();
        $allInvoices = (clone $query)->get();

        return view('member-invoices.index', [
            'invoices' => $invoices,
            'summary' => [
                'open' => $allInvoices->whereIn('status', [InvoiceStatus::Pending, InvoiceStatus::Overdue])->count(),
                'paid' => $allInvoices->where('status', InvoiceStatus::Paid)->count(),
                'overdue' => $allInvoices->where('status', InvoiceStatus::Overdue)->count(),
            ],
        ]);
    }

    public function show(MembershipInvoice $membershipInvoice)
    {
        $this->authorize('view', $membershipInvoice);
        $membershipInvoice->load(['branch', 'member.user']);

        return view('member-invoices.show', [
            'invoice' => $membershipInvoice,
        ]);
    }

    public function receipt(MembershipInvoice $membershipInvoice)
    {
        $this->authorize('view', $membershipInvoice);
        $membershipInvoice->load(['branch', 'member.user']);
        abort_unless($membershipInvoice->status === InvoiceStatus::Paid, 404);

        return view('member-invoices.receipt', [
            'invoice' => $membershipInvoice,
        ]);
    }
}
