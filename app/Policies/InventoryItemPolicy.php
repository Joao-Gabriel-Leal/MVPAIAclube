<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminMatrix() || $user->isAdminBranch();
    }

    public function view(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->isAdminBranch()
            ? $inventoryItem->branch_id === $user->branch_id
            : $user->isAdminMatrix();
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, InventoryItem $inventoryItem): bool
    {
        return $this->view($user, $inventoryItem);
    }
}
