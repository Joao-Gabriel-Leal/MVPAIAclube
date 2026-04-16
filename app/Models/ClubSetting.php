<?php

namespace App\Models;

use App\Support\ClubMediaSlots;
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
        'hero_banner_media_asset_id',
        'gallery_featured_media_asset_id',
        'gallery_1_media_asset_id',
        'gallery_2_media_asset_id',
        'gallery_3_media_asset_id',
        'gallery_4_media_asset_id',
        'gallery_5_media_asset_id',
    ];

    public function heroBannerMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'hero_banner_media_asset_id');
    }

    public function galleryFeaturedMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'gallery_featured_media_asset_id');
    }

    public function galleryOneMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'gallery_1_media_asset_id');
    }

    public function galleryTwoMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'gallery_2_media_asset_id');
    }

    public function galleryThreeMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'gallery_3_media_asset_id');
    }

    public function galleryFourMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'gallery_4_media_asset_id');
    }

    public function galleryFiveMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'gallery_5_media_asset_id');
    }

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

    public function mediaForSlot(string $slot): ?MediaAsset
    {
        $definition = ClubMediaSlots::definition($slot);
        $relation = $definition['relation'];

        $this->loadMissing($relation);

        return $this->{$relation};
    }

    public function homeMediaLibrary(): array
    {
        $this->loadMissing(ClubMediaSlots::relationNames());

        $media = [];

        foreach (ClubMediaSlots::home() as $slot => $definition) {
            $media[$slot] = $this->{$definition['relation']};
        }

        return $media;
    }
}
