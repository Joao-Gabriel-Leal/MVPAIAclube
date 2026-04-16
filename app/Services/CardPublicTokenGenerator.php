<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class CardPublicTokenGenerator
{
    public function generate(int $length = 24): string
    {
        do {
            $token = Str::lower(Str::random($length));
        } while (User::query()->where('card_public_token', $token)->exists());

        return $token;
    }
}
