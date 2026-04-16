<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\ResourceBlock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceBlock>
 */
class ResourceBlockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'club_resource_id' => ClubResource::factory(),
            'branch_id' => Branch::factory(),
            'block_date' => fake()->dateTimeBetween('now', '+1 month'),
            'start_time' => '12:00',
            'end_time' => '14:00',
            'reason' => fake()->sentence(3),
            'blocked_by_user_id' => User::factory(),
        ];
    }
}
