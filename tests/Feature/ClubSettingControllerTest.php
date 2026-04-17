<?php

namespace Tests\Feature;

use App\Models\ClubSetting;
use App\Models\Plan;
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
            ->patch(route('club-settings.update'), $this->validPayload([
                'card_prefix' => 'CS99',
            ]))
            ->assertRedirect(route('club-settings.edit'));

        $this->assertSame('CS99', ClubSetting::current()->card_prefix);
    }

    public function test_admin_matrix_can_update_public_content_settings(): void
    {
        $user = User::factory()->adminMatrix()->create();
        $recommendedPlan = Plan::factory()->create([
            'name' => 'Plano Premium Clube',
            'slug' => 'premium-clube',
        ]);

        $this->actingAs($user)
            ->patch(route('club-settings.update'), $this->validPayload([
                'login_title' => 'Area exclusiva da rede',
                'login_subtitle' => 'Acesse reservas e atendimento em um unico portal.',
                'home_about_title' => 'Conheca a rede do clube',
                'home_gallery_title' => 'Galeria principal',
                'home_gallery_subtitle' => 'Fotos oficiais da estrutura.',
                'home_branches_title' => 'Unidades em destaque',
                'home_branches_subtitle' => 'Veja as filiais com adesao publica.',
                'home_plans_title' => 'Planos publicados',
                'home_plans_subtitle' => 'Escolha a vitrine comercial da home.',
                'home_final_cta_title' => 'Pronto para entrar no clube?',
                'enrollment_intro' => 'A adesao passa primeiro pela fila da unidade.',
                'enrollment_notice' => 'A equipe local revisa os dados antes da aprovacao.',
                'recommended_plan_id' => (string) $recommendedPlan->id,
                'site_email' => 'contato@redeclube.test',
                'site_phone' => '(61) 3000-1234',
                'site_whatsapp' => '(61) 99999-1234',
                'instagram_url' => 'https://instagram.com/redeclube',
                'facebook_url' => 'https://facebook.com/redeclube',
                'seo_title' => 'Rede Clube Oficial',
                'seo_description' => 'Portal oficial da rede para adesao, reservas e atendimento.',
            ]))
            ->assertRedirect(route('club-settings.edit'));

        $clubSetting = ClubSetting::current()->fresh();

        $this->assertSame('Area exclusiva da rede', $clubSetting->login_title);
        $this->assertSame('Conheca a rede do clube', $clubSetting->home_about_title);
        $this->assertSame($recommendedPlan->id, $clubSetting->recommended_plan_id);
        $this->assertSame('contato@redeclube.test', $clubSetting->site_email);
        $this->assertSame('(61) 3000-1234', $clubSetting->site_phone);
        $this->assertSame('(61) 99999-1234', $clubSetting->site_whatsapp);
        $this->assertSame('https://instagram.com/redeclube', $clubSetting->instagram_url);
        $this->assertSame('Rede Clube Oficial', $clubSetting->seo_title);
    }

    public function test_success_status_message_is_rendered_only_once_on_the_settings_page(): void
    {
        $user = User::factory()->adminMatrix()->create();
        $message = 'Configuracoes do clube atualizadas com sucesso.';

        $response = $this->actingAs($user)
            ->withSession(['status' => $message])
            ->get(route('club-settings.edit'));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), $message));
    }

    public function test_admin_matrix_can_upload_the_home_banner_image(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->patch(route('club-settings.update'), $this->validPayload([
                'hero_banner' => $this->fakePngUpload('hero.png', 1600, 900),
            ]))
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
            ->patch(route('club-settings.update'), $this->validPayload([
                'hero_banner' => $this->fakePngUpload('hero-quadrado.png', 900, 900),
            ]))
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
            ->patch(route('club-settings.update'), $this->validPayload([
                'card_prefix' => 'CS',
            ]))
            ->assertForbidden();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'brand_name' => 'Clube AABB',
            'card_prefix' => 'CH',
            'hero_title' => 'Clube AABB',
            'hero_subtitle' => 'Esporte, lazer e convivio para associados e familias.',
            'about_text' => 'O Clube AABB conecta esporte, lazer e vida social em uma experiencia institucional mais acolhedora.',
            'primary_color' => '#2446A8',
            'secondary_color' => '#1B2F72',
            'accent_color' => '#F7D117',
        ], $overrides);
    }
}
