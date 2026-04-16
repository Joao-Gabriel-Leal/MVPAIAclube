<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->isAdminBranch()) {
            return $reservation->branch_id === $user->branch_id;
        }

        if ($user->isMember()) {
            return $reservation->member_id === $user->member?->id;
        }

        if ($user->isDependent()) {
            return $reservation->reserver_type === $user->dependent?->getMorphClass()
                && $reservation->reserver_id === $user->dependent?->id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function updateStatus(User $user, Reservation $reservation): bool
    {
        return $user->isAdminBranch()
            ? $reservation->branch_id === $user->branch_id
            : $user->isAdminMatrix();
    }
}
