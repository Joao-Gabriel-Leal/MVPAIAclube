<?php

namespace App\Services;

use App\Enums\FinancialTransactionType;
use App\Enums\InvoiceStatus;
use App\Enums\MembershipStatus;
use App\Models\Branch;
use App\Models\FinancialTransaction;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(
        protected AuditService $auditService,
    ) {
    }

    public function generateMonthlyInvoices(Carbon $period, ?Branch $branch = null, ?User $actor = null): Collection
    {
        $period = $period->copy()->startOfMonth();
        $members = Member::query()
            ->with(['plan', 'primaryBranch'])
            ->whereIn('status', [MembershipStatus::Active, MembershipStatus::Delinquent])
            ->when($branch, fn ($query) => $query->where('primary_branch_id', $branch->id))
            ->get();

        $created = collect();

        DB::transaction(function () use ($period, $members, $actor, &$created) {
            foreach ($members as $member) {
                $amount = $member->resolvedMonthlyFee();
                $dueDate = $period->copy()->day(10);

                $invoice = MembershipInvoice::query()->firstOrCreate(
                    [
                        'member_id' => $member->id,
                        'billing_period' => $period->toDateString(),
                    ],
                    [
                        'branch_id' => $member->primary_branch_id,
                        'due_date' => $dueDate->toDateString(),
                        'amount' => $amount,
                        'status' => $dueDate->isPast() ? InvoiceStatus::Overdue : InvoiceStatus::Pending,
                        'created_by_user_id' => $actor?->id,
                        'updated_by_user_id' => $actor?->id,
                    ]
                );

                if ($invoice->wasRecentlyCreated) {
                    FinancialTransaction::query()->create([
                        'branch_id' => $invoice->branch_id,
                        'member_id' => $invoice->member_id,
                        'membership_invoice_id' => $invoice->id,
                        'actor_id' => $actor?->id,
                        'type' => FinancialTransactionType::InvoiceGenerated,
                        'amount' => $invoice->amount,
                        'occurred_at' => now(),
                        'description' => 'Mensalidade gerada automaticamente',
                    ]);

                    $this->auditService->log($actor, 'invoice.generated', $invoice, [
                        'period' => $period->format('Y-m'),
                    ]);
                }

                $created->push($invoice);
            }
        });

        $this->refreshOverdueStatuses();

        return $created;
    }

    public function markPaid(MembershipInvoice $invoice, float $amount, User $actor, ?string $notes = null): MembershipInvoice
    {
        $invoice->update([
            'status' => InvoiceStatus::Paid,
            'paid_amount' => $amount,
            'paid_at' => now(),
            'notes' => $notes,
            'updated_by_user_id' => $actor->id,
        ]);

        FinancialTransaction::query()->create([
            'branch_id' => $invoice->branch_id,
            'member_id' => $invoice->member_id,
            'membership_invoice_id' => $invoice->id,
            'actor_id' => $actor->id,
            'type' => FinancialTransactionType::PaymentRecorded,
            'amount' => $amount,
            'occurred_at' => now(),
            'description' => 'Baixa manual de pagamento',
            'meta' => [
                'notes' => $notes,
            ],
        ]);

        $this->auditService->log($actor, 'invoice.paid', $invoice, [
            'amount' => $amount,
        ]);

        return $invoice->refresh();
    }

    public function refreshOverdueStatuses(): void
    {
        MembershipInvoice::query()
            ->where('status', InvoiceStatus::Pending)
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['status' => InvoiceStatus::Overdue]);
    }
}
