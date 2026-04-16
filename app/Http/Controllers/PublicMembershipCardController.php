<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Response;

class PublicMembershipCardController extends Controller
{
    public function show(string $token): Response
    {
        $user = User::query()
            ->where('card_public_token', $token)
            ->whereIn('role', [UserRole::Member->value, UserRole::Dependent->value])
            ->first();

        if ($user) {
            $user->loadMissing([
                'member.plan',
                'member.primaryBranch',
                'dependent.branch',
                'dependent.member.user',
                'dependent.member.plan',
            ]);
        }

        return response()->view('membership-cards.show', [
            'user' => $user,
            'validatedAt' => now(),
        ], $user ? 200 : 404);
    }
}
