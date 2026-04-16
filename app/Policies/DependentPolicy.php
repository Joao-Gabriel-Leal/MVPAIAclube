<?php

namespace App\Policies;

use App\Models\Dependent;
use App\Models\User;

class DependentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, Dependent $dependent): bool
    {
        if ($user->isAdminBranch()) {
            return $dependent->branch_id === $user->branch_id;
        }

        if ($user->isMember()) {
            return $user->member?->is($dependent->member) ?? false;
        }

        if ($user->isDependent()) {
            return $user->dependent?->is($dependent) ?? false;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdminMatrix() || $user->isAdminBranch() || $user->isMember();
    }

    public function update(User $user, Dependent $dependent): bool
    {
        if ($user->isAdminBranch()) {
            return $dependent->branch_id === $user->branch_id;
        }

        if ($user->isMember()) {
            return $user->member?->is($dependent->member) ?? false;
        }

        if ($user->isDependent()) {
            return $user->dependent?->is($dependent) ?? false;
        }

        return true;
    }

    public function approve(User $user, Dependent $dependent): bool
    {
        return $user->isAdminBranch()
            ? $dependent->branch_id === $user->branch_id
            : $user->isAdminMatrix();
    }
}
