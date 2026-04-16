<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, Member $member): bool
    {
        if ($user->isAdminBranch()) {
            return $member->allBranchIds()->contains($user->branch_id);
        }

        if ($user->isMember()) {
            return $user->member?->is($member) ?? false;
        }

        if ($user->isDependent()) {
            return $user->dependent?->member?->is($member) ?? false;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdminMatrix() || $user->isAdminBranch();
    }

    public function update(User $user, Member $member): bool
    {
        if ($user->isAdminBranch()) {
            return $member->allBranchIds()->contains($user->branch_id);
        }

        if ($user->isMember()) {
            return $user->member?->is($member) ?? false;
        }

        return $user->isAdminMatrix();
    }

    public function approve(User $user, Member $member): bool
    {
        return $user->isAdminBranch()
            ? $member->allBranchIds()->contains($user->branch_id)
            : $user->isAdminMatrix();
    }
}
