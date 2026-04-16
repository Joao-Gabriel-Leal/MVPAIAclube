<?php

namespace Tests\Feature;

use App\Models\ClubSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPngUploads;
use Tests\TestCase;

class ClubSettingControllerTest extends TestCase
{
    use CreatesPngUploads;
    use RefreshDatabase;

    public function test_admin_matrix_can_view_the_card_settings_page(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->get(route('club-settings.edit'))
            ->assertOk()
            ->assertSee('Midia da home');
    }

    public function test_admin_matrix_can_update_the_card_prefix(): void
    {
        ClubSetting::query()->updateOrCreate([
            'id' => 1,
        ], [
            'card_prefix' => 'CH',
        ]);
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->patch(route('club-settings.update'), [
                'card_prefix' => 'CS99',
            ])
            ->assertRedirect(route('club-settings.edit'));

        $this->assertSame('CS99', ClubSetting::current()->card_prefix);
    }

    public function test_admin_matrix_can_upload_the_home_banner_image(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->patch(route('club-settings.update'), [
                'card_prefix' => 'CH',
                'hero_banner' => $this->fakePngUpload('hero.png', 1600, 900),
            ])
            ->assertRedirect(route('club-settings.edit'));

        $clubSetting = ClubSetting::current()->fresh();

        $this->assertNotNull($clubSetting->hero_banner_media_asset_id);
        $this->assertDatabaseCount('media_assets', 1);

        $this->get(route('media.show', $clubSetting->hero_banner_media_asset_id))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_invalid_home_media_ratio_is_rejected(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->from(route('club-settings.edit'))
            ->patch(route('club-settings.update'), [
                'card_prefix' => 'CH',
                'hero_banner' => $this->fakePngUpload('hero-quadrado.png', 900, 900),
            ])
            ->assertSessionHasErrors('hero_banner')
            ->assertRedirect(route('club-settings.edit'));
    }

    public function test_non_admin_matrix_users_cannot_access_card_settings(): void
    {
        ClubSetting::query()->updateOrCreate(['id' => 1], ['card_prefix' => 'CH']);

        $adminBranch = User::factory()->adminBranch()->create();
        $member = User::factory()->create();

        $this->actingAs($adminBranch)
            ->get(route('club-settings.edit'))
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('club-settings.update'), [
                'card_prefix' => 'CS',
            ])
            ->assertForbidden();
    }
}
