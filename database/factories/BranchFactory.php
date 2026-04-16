<?php

namespace Database\Factories;

use App\Enums\BranchType;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company().' Clube';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 99),
            'type' => BranchType::Branch,
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('(##) ####-####'),
            'address' => fake()->streetAddress(),
            'monthly_fee_default' => fake()->randomFloat(2, 99, 299),
            'is_active' => true,
            'settings' => [
                'timezone' => config('app.timezone'),
            ],
        ];
    }

    public function headquarters(): static
    {
        return $this->state(fn () => [
            'type' => BranchType::Headquarters,
            'slug' => 'matriz',
        ]);
    }
}
