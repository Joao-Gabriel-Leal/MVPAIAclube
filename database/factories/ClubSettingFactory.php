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
        ];
    }
}
