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
            'brand_name' => 'Clube AABB',
            'card_prefix' => 'CH',
            'hero_title' => 'Clube AABB',
            'hero_subtitle' => 'Esporte, lazer e convivio para associados e familias.',
            'about_text' => 'O Clube AABB conecta esporte, lazer e vida social em uma experiencia institucional mais acolhedora.',
            'login_title' => 'Entrar no Clube AABB',
            'login_subtitle' => 'Acesso a reservas, carteirinha e administracao.',
            'home_about_title' => 'Uma apresentacao institucional mais clara para entender o Clube AABB',
            'home_gallery_title' => 'Veja a atmosfera das unidades antes de entrar em detalhes operacionais',
            'home_gallery_subtitle' => null,
            'home_branches_title' => 'Conheca algumas unidades da rede em cidades estrategicas',
            'home_branches_subtitle' => null,
            'home_plans_title' => 'Categorias demonstrativas para apresentar a operacao do sistema',
            'home_plans_subtitle' => null,
            'home_final_cta_title' => 'Veja a rede, explore as filiais e apresente uma experiencia de clube mais completa',
            'enrollment_intro' => 'O cadastro entra primeiro na fila de propostas da unidade. A equipe local faz a analise antes de liberar o associado na base ativa.',
            'enrollment_notice' => 'O envio nao ativa o cadastro imediatamente. A administracao da unidade avalia a solicitacao antes da aprovacao final.',
            'recommended_plan_id' => null,
            'site_email' => 'contato@clubeaabb.demo',
            'site_phone' => '6130000001',
            'site_whatsapp' => '61999990001',
            'instagram_url' => 'https://instagram.com/clubeaabb',
            'facebook_url' => 'https://facebook.com/clubeaabb',
            'seo_title' => 'Clube AABB',
            'seo_description' => 'Rede de clubes com esporte, lazer e convivio para associados e familias.',
            'primary_color' => '#2446A8',
            'secondary_color' => '#1B2F72',
            'accent_color' => '#F7D117',
            'logo_media_asset_id' => null,
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
