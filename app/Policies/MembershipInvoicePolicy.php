<?php

namespace App\Policies;

use App\Models\MembershipInvoice;
use App\Models\User;

class MembershipInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, MembershipInvoice $membershipInvoice): bool
    {
        if ($user->isAdminBranch()) {
            return $membershipInvoice->branch_id === $user->branch_id;
        }

        if ($user->isMember()) {
            return $membershipInvoice->member_id === $user->member?->id;
        }

        if ($user->isDependent()) {
            return $membershipInvoice->member_id === $user->dependent?->member_id;
        }

        return true;
    }

    public function markPaid(User $user, MembershipInvoice $membershipInvoice): bool
    {
        return $user->isAdminBranch()
            ? $membershipInvoice->branch_id === $user->branch_id
            : $user->isAdminMatrix();
    }
}
