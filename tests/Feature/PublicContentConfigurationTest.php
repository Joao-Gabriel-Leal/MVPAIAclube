<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ClubSetting;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_uses_configured_content_and_selected_recommended_plan(): void
    {
        $fallbackPlan = Plan::factory()->create([
            'name' => 'Plano Familia',
            'slug' => 'familia',
        ]);
        $recommendedPlan = Plan::factory()->create([
            'name' => 'Plano Premium',
            'slug' => 'premium',
        ]);
        Branch::factory()->create([
            'slug' => 'brasilia',
            'settings' => [
                'city' => 'Brasilia',
                'summary' => 'Filial principal para adesao.',
            ],
        ]);

        ClubSetting::query()->updateOrCreate([
            'id' => 1,
        ], [
            'home_about_title' => 'Conheca a rede oficial',
            'home_gallery_title' => 'Espacos em destaque',
            'home_gallery_subtitle' => 'Fotos selecionadas da estrutura.',
            'home_branches_title' => 'Filiais abertas',
            'home_branches_subtitle' => 'Veja onde a adesao esta disponivel.',
            'home_plans_title' => 'Planos publicados',
            'home_plans_subtitle' => 'A vitrine comercial da rede.',
            'home_final_cta_title' => 'Entre na experiencia completa do clube',
            'recommended_plan_id' => $recommendedPlan->id,
            'site_email' => 'contato@redeclube.test',
            'site_phone' => '6130001234',
            'site_whatsapp' => '61999991234',
            'instagram_url' => 'https://instagram.com/redeclube',
            'facebook_url' => 'https://facebook.com/redeclube',
            'seo_title' => 'Rede Clube Oficial',
            'seo_description' => 'A rede oficial com adesao online.',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText('Conheca a rede oficial');
        $response->assertSeeText('Espacos em destaque');
        $response->assertSeeText('Filiais abertas');
        $response->assertSeeText('Planos publicados');
        $response->assertSeeText('Entre na experiencia completa do clube');
        $response->assertSeeText('contato@redeclube.test');
        $response->assertSeeText('Instagram');
        $response->assertSeeText('Facebook');
        $response->assertSee('Rede Clube Oficial', false);
        $response->assertSee('A rede oficial com adesao online.', false);

        $content = $response->getContent();

        $this->assertSame(1, preg_match('/Mais equilibrado.*Plano Premium/s', $content));
        $this->assertSame(0, preg_match('/Mais equilibrado.*Plano Familia/s', $content));
    }

    public function test_landing_hides_global_contacts_when_no_site_channels_are_configured(): void
    {
        ClubSetting::query()->updateOrCreate([
            'id' => 1,
        ], [
            'site_email' => null,
            'site_phone' => null,
            'site_whatsapp' => null,
            'instagram_url' => null,
            'facebook_url' => null,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Instagram');
        $response->assertDontSee('Facebook');
        $response->assertDontSee('mailto:');
        $response->assertDontSee('wa.me');
    }

    public function test_login_page_uses_configured_title_and_subtitle(): void
    {
        ClubSetting::query()->updateOrCreate([
            'id' => 1,
        ], [
            'login_title' => 'Portal oficial da rede',
            'login_subtitle' => 'Acompanhe reservas, carteirinhas e atendimento.',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertSeeText('Portal oficial da rede')
            ->assertSeeText('Acompanhe reservas, carteirinhas e atendimento.');
    }

    public function test_public_enrollment_page_uses_configured_content_and_branch_contact_data(): void
    {
        $branch = Branch::factory()->create([
            'name' => 'AABB Goiania',
            'settings' => [
                'city' => 'Goiania',
                'summary' => 'Resumo da unidade goiana.',
                'public_phone' => '6233334444',
                'public_whatsapp' => '62999994444',
                'public_hours' => 'Seg a Dom, 08h as 21h',
            ],
        ]);
        Plan::factory()->create();

        ClubSetting::query()->updateOrCreate([
            'id' => 1,
        ], [
            'enrollment_intro' => 'Sua adesao sera analisada pela administracao local.',
            'enrollment_notice' => 'Os dados sao revisados antes da aprovacao final.',
        ]);

        $this->get(route('enrollment.create', $branch))
            ->assertOk()
            ->assertSeeText('Sua adesao sera analisada pela administracao local.')
            ->assertSeeText('Os dados sao revisados antes da aprovacao final.')
            ->assertSeeText('Resumo da unidade goiana.')
            ->assertSeeText('(62) 3333-4444')
            ->assertSeeText('WhatsApp (62) 99999-4444')
            ->assertSeeText('Seg a Dom, 08h as 21h');
    }
}
