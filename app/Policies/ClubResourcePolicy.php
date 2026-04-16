<?php

namespace App\Policies;

use App\Models\ClubResource;
use App\Models\User;

class ClubResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, ClubResource $clubResource): bool
    {
        if ($user->isAdminBranch()) {
            return $clubResource->branch_id === $user->branch_id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdminMatrix() || $user->isAdminBranch();
    }

    public function update(User $user, ClubResource $clubResource): bool
    {
        return $user->isAdminBranch()
            ? $clubResource->branch_id === $user->branch_id
            : $user->isAdminMatrix();
    }
}
