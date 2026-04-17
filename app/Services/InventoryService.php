<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Models\ClubResource;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function __construct(
        protected AuditService $auditService,
    ) {
    }

    public function createItem(array $data, User $actor): InventoryItem
    {
        $branchId = $this->resolveBranchId($actor, (int) $data['branch_id']);
        $resourceId = $this->resolveResourceId($branchId, $data['club_resource_id'] ?? null);

        $item = InventoryItem::query()->create([
            'branch_id' => $branchId,
            'club_resource_id' => $resourceId,
            'name' => $data['name'],
            'category' => $data['category'],
            'unit' => $data['unit'],
            'current_quantity' => $data['current_quantity'] ?? 0,
            'minimum_quantity' => $data['minimum_quantity'],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'notes' => $data['notes'] ?? null,
        ]);

        $this->auditService->log($actor, 'inventory.item_created', $item, [
            'category' => $item->category,
            'current_quantity' => (float) $item->current_quantity,
        ]);

        return $item->load(['branch', 'resource']);
    }

    public function updateItem(InventoryItem $item, array $data, User $actor): InventoryItem
    {
        $branchId = $this->resolveBranchId($actor, (int) $data['branch_id']);
        $resourceId = $this->resolveResourceId($branchId, $data['club_resource_id'] ?? null);

        $item->update([
            'branch_id' => $branchId,
            'club_resource_id' => $resourceId,
            'name' => $data['name'],
            'category' => $data['category'],
            'unit' => $data['unit'],
            'current_quantity' => $data['current_quantity'] ?? $item->current_quantity,
            'minimum_quantity' => $data['minimum_quantity'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'notes' => $data['notes'] ?? null,
        ]);

        $this->auditService->log($actor, 'inventory.item_updated', $item, [
            'category' => $item->category,
            'current_quantity' => (float) $item->current_quantity,
        ]);

        return $item->refresh()->load(['branch', 'resource']);
    }

    public function recordMovement(InventoryItem $item, array $data, User $actor): InventoryMovement
    {
        $this->assertActorCanManageItem($actor, $item);

        $movementType = InventoryMovementType::from($data['movement_type']);
        $quantity = (float) $data['quantity'];

        if ($movementType !== InventoryMovementType::Adjustment && $quantity <= 0) {
            throw new RuntimeException('A quantidade deve ser maior que zero para entradas e saídas.');
        }

        if ($movementType === InventoryMovementType::Adjustment && $quantity == 0.0) {
            throw new RuntimeException('Informe um ajuste diferente de zero.');
        }

        $resourceId = $this->resolveResourceId($item->branch_id, $data['club_resource_id'] ?? $item->club_resource_id);
        $reservationId = $this->resolveReservationId($item->branch_id, $data['reservation_id'] ?? null);
        $newQuantity = $this->calculateNewQuantity($item, $movementType, $quantity);

        return DB::transaction(function () use ($item, $actor, $data, $movementType, $quantity, $resourceId, $reservationId, $newQuantity) {
            $movement = InventoryMovement::query()->create([
                'inventory_item_id' => $item->id,
                'branch_id' => $item->branch_id,
                'club_resource_id' => $resourceId,
                'reservation_id' => $reservationId,
                'actor_id' => $actor->id,
                'movement_type' => $movementType,
                'reason' => $data['reason'],
                'quantity' => $quantity,
                'unit_cost' => $data['unit_cost'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $item->update([
                'current_quantity' => $newQuantity,
            ]);

            $this->auditService->log($actor, 'inventory.movement_recorded', $movement, [
                'item' => $item->name,
                'movement_type' => $movementType->value,
                'quantity' => $quantity,
                'new_quantity' => $newQuantity,
            ]);

            return $movement->load(['item', 'branch', 'resource', 'reservation', 'actor']);
        });
    }

    protected function resolveBranchId(User $actor, int $branchId): int
    {
        if ($actor->isAdminBranch()) {
            return $actor->branch_id;
        }

        return $branchId;
    }

    protected function resolveResourceId(int $branchId, mixed $resourceId): ?int
    {
        if (! $resourceId) {
            return null;
        }

        $resource = ClubResource::query()
            ->whereKey($resourceId)
            ->where('branch_id', $branchId)
            ->first();

        if (! $resource) {
            throw new RuntimeException('O recurso informado nao pertence a filial selecionada.');
        }

        return $resource->id;
    }

    protected function resolveReservationId(int $branchId, mixed $reservationId): ?int
    {
        if (! $reservationId) {
            return null;
        }

        $reservation = Reservation::query()
            ->whereKey($reservationId)
            ->where('branch_id', $branchId)
            ->first();

        if (! $reservation) {
            throw new RuntimeException('A reserva informada nao pertence a filial selecionada.');
        }

        return $reservation->id;
    }

    protected function calculateNewQuantity(InventoryItem $item, InventoryMovementType $movementType, float $quantity): float
    {
        $current = (float) $item->current_quantity;

        $next = match ($movementType) {
            InventoryMovementType::Entry => $current + $quantity,
            InventoryMovementType::Exit => $current - $quantity,
            InventoryMovementType::Adjustment => $current + $quantity,
        };

        if ($next < 0) {
            throw new RuntimeException('A movimentacao deixaria o estoque negativo.');
        }

        return round($next, 2);
    }

    protected function assertActorCanManageItem(User $actor, InventoryItem $item): void
    {
        if ($actor->isAdminMatrix()) {
            return;
        }

        if (! $actor->isAdminBranch() || $item->branch_id !== $actor->branch_id) {
            throw new RuntimeException('Voce nao pode movimentar itens de outra filial.');
        }
    }
}
