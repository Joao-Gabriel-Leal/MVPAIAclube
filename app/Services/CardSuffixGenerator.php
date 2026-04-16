<?php

namespace App\Services;

use App\Models\User;

class CardSuffixGenerator
{
    public function generate(?callable $candidateResolver = null, int $length = 6): string
    {
        do {
            $suffix = strtoupper(($candidateResolver ? $candidateResolver($length) : $this->random($length)));
        } while (User::query()->where('card_suffix', $suffix)->exists());

        return $suffix;
    }

    protected function random(int $length): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $characters = [];

        for ($index = 0; $index < $length; $index++) {
            $characters[] = $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return implode('', $characters);
    }
}
