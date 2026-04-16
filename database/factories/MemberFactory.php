<?php

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Models\Branch;
use App\Models\Member;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'primary_branch_id' => Branch::factory(),
            'plan_id' => Plan::factory(),
            'status' => MembershipStatus::Active,
            'custom_monthly_fee' => null,
            'approved_at' => now(),
            'approved_by_user_id' => null,
            'cancelled_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => MembershipStatus::Pending,
            'approved_at' => null,
        ]);
    }
}
