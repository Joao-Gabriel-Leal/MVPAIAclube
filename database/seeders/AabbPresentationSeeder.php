<?php

namespace Database\Seeders;

use App\Models\ClubSetting;
use App\Models\MediaAsset;
use App\Models\Plan;
use App\Support\ClubMediaSlots;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AabbPresentationSeeder extends Seeder
{
    public function run(): void
    {
        $clubSetting = ClubSetting::current();

        $clubSetting->update([
            'brand_name' => 'Clube AABB',
            'card_prefix' => 'AABB',
            'hero_title' => 'Clube AABB',
            'hero_subtitle' => 'Uma rede de clubes com esporte, lazer, convivio e estrutura para familias, associados e convidados em diferentes cidades do Brasil.',
            'about_text' => 'O Clube AABB apresenta uma experiencia completa de associacao com piscinas, quadras, ginasio, eventos, espacos de convivencia e operacao integrada por unidade. Nesta demonstracao, a plataforma mostra como a rede pode organizar adesao, atendimento, carteirinhas, reservas e acompanhamento operacional em um unico sistema.',
            'login_title' => 'Entrar no Clube AABB',
            'login_subtitle' => 'Acompanhe adesoes, reservas, carteirinha digital e a operacao da rede em um unico ambiente.',
            'home_about_title' => 'Uma apresentacao institucional mais clara para entender o Clube AABB',
            'home_gallery_title' => 'Veja a atmosfera das unidades antes de entrar em detalhes operacionais',
            'home_gallery_subtitle' => 'A galeria ajuda o cliente a visualizar quadras, convivio, lazer e a proposta da rede antes do contato comercial.',
            'home_branches_title' => 'Conheca algumas unidades da rede em cidades estrategicas',
            'home_branches_subtitle' => 'Cada filial pode publicar um resumo rapido, contatos publicos e a unidade de adesao correspondente.',
            'home_plans_title' => 'Categorias demonstrativas para apresentar a operacao do sistema',
            'home_plans_subtitle' => 'Os valores e beneficios abaixo mostram como a matriz pode editar a vitrine comercial sem alterar o codigo.',
            'home_final_cta_title' => 'Veja a rede, explore as filiais e apresente uma experiencia de clube mais completa',
            'enrollment_intro' => 'A solicitacao de adesao entra na fila da unidade e segue para avaliacao da administracao local antes da liberacao final do associado.',
            'enrollment_notice' => 'O envio nao ativa o cadastro imediatamente. A equipe da unidade valida as informacoes, confirma o plano e conclui a aprovacao.',
            'recommended_plan_id' => Plan::query()->where('slug', 'familia')->value('id'),
            'site_email' => 'contato@clubeaabb.demo',
            'site_phone' => '6130000001',
            'site_whatsapp' => '61999990001',
            'instagram_url' => 'https://instagram.com/clubeaabb',
            'facebook_url' => 'https://facebook.com/clubeaabb',
            'seo_title' => 'Clube AABB',
            'seo_description' => 'Rede de clubes com esporte, lazer, convivio, adesao online e gestao integrada por unidade.',
            'primary_color' => '#2958B8',
            'secondary_color' => '#4E79CC',
            'accent_color' => '#F2CF2F',
        ]);

        $assetMap = [
            'logo_media_asset_id' => [
                'path' => database_path('seed-assets/aabb/brand-logo.png'),
                'context' => 'branding',
                'slot' => 'logo',
                'visibility' => MediaAsset::VISIBILITY_PUBLIC,
            ],
            'hero_banner_media_asset_id' => [
                'path' => database_path('seed-assets/aabb/hero-banner.jpg'),
                'context' => ClubMediaSlots::definition('hero_banner')['context'],
                'slot' => 'hero_banner',
                'visibility' => MediaAsset::VISIBILITY_PUBLIC,
            ],
            'gallery_featured_media_asset_id' => [
                'path' => database_path('seed-assets/aabb/gallery-featured.jpg'),
                'context' => ClubMediaSlots::definition('gallery_featured')['context'],
                'slot' => 'gallery_featured',
                'visibility' => MediaAsset::VISIBILITY_PUBLIC,
            ],
        ];

        foreach ($assetMap as $column => $config) {
            if (! File::exists($config['path'])) {
                continue;
            }

            $clubSetting->{$column} = $this->upsertPublicAsset(
                $config['path'],
                $config['context'],
                $config['slot'],
                $config['visibility']
            )->id;
        }

        foreach (['gallery_1_media_asset_id', 'gallery_2_media_asset_id', 'gallery_3_media_asset_id', 'gallery_4_media_asset_id', 'gallery_5_media_asset_id'] as $column) {
            $clubSetting->{$column} = null;
        }

        $clubSetting->save();
    }

    protected function upsertPublicAsset(string $path, string $context, string $slot, string $visibility): MediaAsset
    {
        $content = File::get($path);
        $imageSize = @getimagesize($path);
        $binaryStream = fopen($path, 'rb');

        if ($binaryStream === false) {
            throw new \RuntimeException("Nao foi possivel abrir o asset {$path} em modo binario.");
        }

        return MediaAsset::query()->updateOrCreate(
            [
                'context' => $context,
                'slot' => $slot,
                'original_name' => basename($path),
            ],
            [
                'visibility' => $visibility,
                'mime_type' => File::mimeType($path) ?: 'application/octet-stream',
                'size_bytes' => File::size($path),
                'width' => $imageSize !== false ? $imageSize[0] : null,
                'height' => $imageSize !== false ? $imageSize[1] : null,
                'checksum' => hash('sha256', $content),
                'content' => $binaryStream,
            ]
        );
    }
}
