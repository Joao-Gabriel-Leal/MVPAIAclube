<?php

namespace Tests\Feature;

use App\Models\ClubSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_matrix_can_view_the_card_settings_page(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->get(route('club-settings.edit'))
            ->assertOk()
            ->assertSee('Carteirinha digital');
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
