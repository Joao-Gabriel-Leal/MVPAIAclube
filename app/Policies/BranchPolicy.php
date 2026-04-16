<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminMatrix() || $user->isAdminBranch();
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->isAdminBranch() ? $user->branch_id === $branch->id : true;
    }

    public function create(User $user): bool
    {
        return $user->isAdminMatrix();
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->isAdminBranch() ? $user->branch_id === $branch->id : $user->isAdminMatrix();
    }
}
