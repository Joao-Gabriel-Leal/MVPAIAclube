<?php

namespace Database\Factories;

use App\Models\ClubSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClubSetting>
 */
class ClubSettingFactory extends Factory
{
    protected $model = ClubSetting::class;

    public function definition(): array
    {
        return [
            'card_prefix' => 'CH',
            'hero_banner_media_asset_id' => null,
            'gallery_featured_media_asset_id' => null,
            'gallery_1_media_asset_id' => null,
            'gallery_2_media_asset_id' => null,
            'gallery_3_media_asset_id' => null,
            'gallery_4_media_asset_id' => null,
            'gallery_5_media_asset_id' => null,
        ];
    }
}
