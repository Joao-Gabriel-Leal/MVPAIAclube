<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\ClubResource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ClubResource>
 */
class ClubResourceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Churrasqueira', 'Quadra', 'Salao']);

        return [
            'branch_id' => Branch::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->numberBetween(1, 999),
            'type' => $name,
            'description' => fake()->sentence(),
            'max_capacity' => fake()->numberBetween(8, 120),
            'default_price' => fake()->randomFloat(2, 0, 250),
            'is_active' => true,
            'settings' => [
                'color' => fake()->safeColorName(),
            ],
        ];
    }
}
