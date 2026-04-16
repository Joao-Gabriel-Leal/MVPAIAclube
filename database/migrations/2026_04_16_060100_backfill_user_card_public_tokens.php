<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $generateUniqueToken = function (): string {
            do {
                $candidate = Str::lower(Str::random(24));
            } while (DB::table('users')->where('card_public_token', $candidate)->exists());

            return $candidate;
        };

        DB::table('users')
            ->whereIn('role', ['member', 'dependent'])
            ->whereNull('card_public_token')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $userId) use ($generateUniqueToken) {
                DB::table('users')
                    ->where('id', $userId)
                    ->update([
                        'card_public_token' => $generateUniqueToken(),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        //
    }
};
