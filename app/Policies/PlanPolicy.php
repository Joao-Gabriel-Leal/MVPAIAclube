<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;

class PlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function create(User $user): bool
    {
        return $user->isAdminMatrix();
    }

    public function view(User $user, Plan $plan): bool
    {
        return $user !== null && $plan->is_active;
    }

    public function update(User $user, Plan $plan): bool
    {
        return $user->isAdminMatrix();
    }

    public function delete(User $user, Plan $plan): bool
    {
        return $user->isAdminMatrix();
    }
}
