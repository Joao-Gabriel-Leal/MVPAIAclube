<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_settings', function (Blueprint $table) {
            $table->id();
            $table->string('card_prefix', 6);
            $table->timestamps();
        });

        $name = trim((string) config('app.name', 'Clube Hub'));
        $normalized = preg_replace('/[^A-Za-z0-9\s]+/', ' ', $name) ?? '';
        $words = array_values(array_filter(preg_split('/\s+/', $normalized) ?: []));
        $initials = implode('', array_map(fn (string $word) => strtoupper(substr($word, 0, 1)), $words));

        if (strlen($initials) < 2) {
            $fallback = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $name) ?? '');
            $initials = substr($fallback !== '' ? $fallback : 'CL', 0, 6);
        } else {
            $initials = substr($initials, 0, 6);
        }

        DB::table('club_settings')->insert([
            'id' => 1,
            'card_prefix' => $initials,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('club_settings');
    }
};
