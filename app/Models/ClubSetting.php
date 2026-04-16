<?php

namespace App\Models;

use Database\Factories\ClubSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ClubSetting extends Model
{
    /** @use HasFactory<ClubSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'card_prefix',
    ];

    public static function current(): self
    {
        if (! Schema::hasTable('club_settings')) {
            return new static([
                'id' => 1,
                'card_prefix' => static::defaultCardPrefix(),
            ]);
        }

        return static::query()->find(1)
            ?? static::query()->forceCreate([
                'id' => 1,
                'card_prefix' => static::defaultCardPrefix(),
            ]);
    }

    public static function defaultCardPrefix(): string
    {
        $name = trim((string) config('app.name', 'Clube Hub'));
        $normalized = preg_replace('/[^A-Za-z0-9\s]+/', ' ', $name) ?? '';
        $words = collect(preg_split('/\s+/', $normalized) ?: [])
            ->filter()
            ->values();

        $initials = $words
            ->map(fn (string $word) => Str::upper(Str::substr($word, 0, 1)))
            ->join('');

        if (Str::length($initials) >= 2) {
            return Str::substr($initials, 0, 6);
        }

        $fallback = Str::upper(preg_replace('/[^A-Za-z0-9]+/', '', $name) ?? '');

        return Str::substr($fallback !== '' ? $fallback : 'CL', 0, 6);
    }
}
