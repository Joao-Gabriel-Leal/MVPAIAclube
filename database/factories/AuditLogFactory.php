<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'event' => fake()->randomElement(['member.created', 'invoice.generated', 'reservation.confirmed']),
            'payload' => [
                'source' => 'factory',
            ],
        ];
    }
}
