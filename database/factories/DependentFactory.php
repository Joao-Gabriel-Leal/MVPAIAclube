<?php

namespace Database\Factories;

use App\Enums\DependentStatus;
use App\Models\Branch;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dependent>
 */
class DependentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->dependent(),
            'member_id' => Member::factory(),
            'branch_id' => Branch::factory(),
            'relationship' => fake()->randomElement(['Conjuge', 'Filho(a)', 'Pai', 'Mae']),
            'status' => DependentStatus::Active,
            'approved_at' => now(),
            'approved_by_user_id' => null,
        ];
    }
}
