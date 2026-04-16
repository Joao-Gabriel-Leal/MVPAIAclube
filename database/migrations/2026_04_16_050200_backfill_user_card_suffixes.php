<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $generateUniqueSuffix = function () use ($alphabet): string {
            do {
                $candidate = '';

                for ($index = 0; $index < 6; $index++) {
                    $candidate .= $alphabet[random_int(0, strlen($alphabet) - 1)];
                }
            } while (DB::table('users')->where('card_suffix', $candidate)->exists());

            return $candidate;
        };

        DB::table('users')
            ->whereIn('role', ['member', 'dependent'])
            ->whereNull('card_suffix')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $userId) use ($generateUniqueSuffix) {
                DB::table('users')
                    ->where('id', $userId)
                    ->update([
                        'card_suffix' => $generateUniqueSuffix(),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        //
    }
};
