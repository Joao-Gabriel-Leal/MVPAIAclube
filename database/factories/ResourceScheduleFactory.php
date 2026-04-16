<?php

namespace Database\Factories;

use App\Models\ClubResource;
use App\Models\ResourceSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceSchedule>
 */
class ResourceScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'club_resource_id' => ClubResource::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'opens_at' => '08:00',
            'closes_at' => '22:00',
            'slot_interval_minutes' => 60,
            'is_active' => true,
        ];
    }
}
