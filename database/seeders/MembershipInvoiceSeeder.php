<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Database\Seeder;

class MembershipInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::query()->where('role', UserRole::AdminMatrix)->first();
        app(BillingService::class)->generateMonthlyInvoices(now()->startOfMonth(), null, $actor);
        app(BillingService::class)->generateMonthlyInvoices(now()->subMonth()->startOfMonth(), null, $actor);

        $member = Member::query()->where('status', 'active')->first();

        if ($member) {
            $currentInvoice = MembershipInvoice::query()
                ->where('member_id', $member->id)
                ->where('billing_period', now()->startOfMonth()->toDateString())
                ->first();

            if ($currentInvoice) {
                app(BillingService::class)->markPaid($currentInvoice, (float) $currentInvoice->amount, $actor, 'Pagamento de demonstracao');
            }
        }

        $delinquentMember = Member::query()->where('status', 'delinquent')->first();

        if ($delinquentMember) {
            $invoice = MembershipInvoice::query()
                ->where('member_id', $delinquentMember->id)
                ->whereDate('billing_period', now()->subMonth()->startOfMonth()->toDateString())
                ->first();

            if ($invoice) {
                $invoice->update([
                    'status' => InvoiceStatus::Overdue,
                    'updated_by_user_id' => $actor?->id,
                ]);
            }
        }
    }
}
