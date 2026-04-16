<?php

namespace Database\Factories;

use App\Enums\FinancialTransactionType;
use App\Models\Branch;
use App\Models\FinancialTransaction;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTransaction>
 */
class FinancialTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'member_id' => Member::factory(),
            'membership_invoice_id' => MembershipInvoice::factory(),
            'actor_id' => User::factory(),
            'type' => FinancialTransactionType::InvoiceGenerated,
            'amount' => fake()->randomFloat(2, 10, 300),
            'occurred_at' => now(),
            'description' => fake()->sentence(),
            'meta' => [
                'channel' => 'manual',
            ],
        ];
    }
}
