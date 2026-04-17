<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'club_resource_id' => null,
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(['Limpeza', 'Bebidas', 'Manutencao', 'Cozinha']),
            'unit' => fake()->randomElement(['un', 'kg', 'l', 'pct']),
            'current_quantity' => fake()->randomFloat(2, 0, 50),
            'minimum_quantity' => fake()->randomFloat(2, 0, 10),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forResource(?ClubResource $resource = null): static
    {
        return $this->state(function () use ($resource) {
            $resource ??= ClubResource::factory()->create();

            return [
                'branch_id' => $resource->branch_id,
                'club_resource_id' => $resource->id,
            ];
        });
    }
}
