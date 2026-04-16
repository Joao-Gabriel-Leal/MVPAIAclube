<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Bronze', 'Prata', 'Ouro', 'Elite']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->numberBetween(1, 99),
            'description' => fake()->sentence(),
            'base_price' => fake()->randomFloat(2, 79, 299),
            'dependent_limit' => fake()->numberBetween(0, 5),
            'guest_limit_per_reservation' => fake()->numberBetween(0, 8),
            'free_reservations_per_month' => fake()->numberBetween(0, 3),
            'extra_reservation_discount_type' => fake()->randomElement([
                DiscountType::None,
                DiscountType::Percentage,
                DiscountType::Fixed,
            ]),
            'extra_reservation_discount_value' => fake()->randomFloat(2, 0, 40),
            'dependents_inherit_benefits' => fake()->boolean(),
            'is_active' => true,
            'metadata' => [
                'badge' => fake()->colorName(),
            ],
        ];
    }
}
