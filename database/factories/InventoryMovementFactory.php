<?php

namespace Database\Factories;

use App\Enums\InventoryMovementReason;
use App\Enums\InventoryMovementType;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'branch_id' => null,
            'club_resource_id' => null,
            'reservation_id' => null,
            'actor_id' => User::factory(),
            'movement_type' => InventoryMovementType::Entry,
            'reason' => InventoryMovementReason::Purchase,
            'quantity' => fake()->randomFloat(2, 1, 10),
            'unit_cost' => fake()->randomFloat(2, 5, 80),
            'occurred_at' => now(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (InventoryMovement $movement) {
            if (! $movement->branch_id && $movement->item) {
                $movement->branch_id = $movement->item->branch_id;
                $movement->club_resource_id ??= $movement->item->club_resource_id;
            }
        })->afterCreating(function (InventoryMovement $movement) {
            if (! $movement->branch_id) {
                $movement->update([
                    'branch_id' => $movement->item->branch_id,
                    'club_resource_id' => $movement->club_resource_id ?? $movement->item->club_resource_id,
                ]);
            }
        });
    }
}
