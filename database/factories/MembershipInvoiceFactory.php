<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Branch;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipInvoice>
 */
class MembershipInvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'member_id' => Member::factory(),
            'billing_period' => now()->startOfMonth(),
            'due_date' => now()->startOfMonth()->addDays(10),
            'amount' => fake()->randomFloat(2, 90, 300),
            'paid_amount' => null,
            'status' => InvoiceStatus::Pending,
            'paid_at' => null,
            'notes' => fake()->optional()->sentence(),
            'created_by_user_id' => User::factory(),
            'updated_by_user_id' => null,
        ];
    }
}
