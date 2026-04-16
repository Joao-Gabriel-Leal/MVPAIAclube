<?php

namespace App\Support;

use InvalidArgumentException;

class ClubMediaSlots
{
    public static function home(): array
    {
        return [
            'hero_banner' => [
                'field' => 'hero_banner_media_asset_id',
                'relation' => 'heroBannerMedia',
                'title' => 'Banner principal',
                'description' => 'Imagem grande da primeira dobra da home.',
                'recommended_size' => '1920 x 1080 px',
                'min_width' => 1200,
                'min_height' => 675,
                'ratio_width' => 16,
                'ratio_height' => 9,
                'formats' => 'JPG, PNG ou WEBP',
                'context' => 'landing',
                'visibility' => 'public',
                'placeholder_label' => 'Banner principal',
                'gallery_title' => null,
            ],
            'gallery_featured' => [
                'field' => 'gallery_featured_media_asset_id',
                'relation' => 'galleryFeaturedMedia',
                'title' => 'Galeria em destaque',
                'description' => 'Primeira foto da galeria, ocupando a maior area.',
                'recommended_size' => '1440 x 1080 px',
                'min_width' => 960,
                'min_height' => 720,
                'ratio_width' => 4,
                'ratio_height' => 3,
                'formats' => 'JPG, PNG ou WEBP',
                'context' => 'landing',
                'visibility' => 'public',
                'placeholder_label' => 'Foto destaque',
                'gallery_title' => 'Ambiente social',
            ],
            'gallery_1' => [
                'field' => 'gallery_1_media_asset_id',
                'relation' => 'galleryOneMedia',
                'title' => 'Galeria 1',
                'description' => 'Foto complementar para esporte ou quadras.',
                'recommended_size' => '1200 x 900 px',
                'min_width' => 800,
                'min_height' => 600,
                'ratio_width' => 4,
                'ratio_height' => 3,
                'formats' => 'JPG, PNG ou WEBP',
                'context' => 'landing',
                'visibility' => 'public',
                'placeholder_label' => 'Quadras',
                'gallery_title' => 'Esporte',
            ],
            'gallery_2' => [
                'field' => 'gallery_2_media_asset_id',
                'relation' => 'galleryTwoMedia',
                'title' => 'Galeria 2',
                'description' => 'Foto complementar para piscina ou lazer.',
                'recommended_size' => '1200 x 900 px',
                'min_width' => 800,
                'min_height' => 600,
                'ratio_width' => 4,
                'ratio_height' => 3,
                'formats' => 'JPG, PNG ou WEBP',
                'context' => 'landing',
                'visibility' => 'public',
                'placeholder_label' => 'Piscina',
                'gallery_title' => 'Lazer',
            ],
            'gallery_3' => [
                'field' => 'gallery_3_media_asset_id',
                'relation' => 'galleryThreeMedia',
                'title' => 'Galeria 3',
                'description' => 'Foto complementar para lounge ou convivio.',
                'recommended_size' => '1200 x 900 px',
                'min_width' => 800,
                'min_height' => 600,
                'ratio_width' => 4,
                'ratio_height' => 3,
                'formats' => 'JPG, PNG ou WEBP',
                'context' => 'landing',
                'visibility' => 'public',
                'placeholder_label' => 'Convivio',
                'gallery_title' => 'Convivio',
            ],
            'gallery_4' => [
                'field' => 'gallery_4_media_asset_id',
                'relation' => 'galleryFourMedia',
                'title' => 'Galeria 4',
                'description' => 'Foto complementar para academia ou bem-estar.',
                'recommended_size' => '1200 x 900 px',
                'min_width' => 800,
                'min_height' => 600,
                'ratio_width' => 4,
                'ratio_height' => 3,
                'formats' => 'JPG, PNG ou WEBP',
                'context' => 'landing',
                'visibility' => 'public',
                'placeholder_label' => 'Bem-estar',
                'gallery_title' => 'Bem-estar',
            ],
            'gallery_5' => [
                'field' => 'gallery_5_media_asset_id',
                'relation' => 'galleryFiveMedia',
                'title' => 'Galeria 5',
                'description' => 'Foto complementar para eventos ou encontros.',
                'recommended_size' => '1200 x 900 px',
                'min_width' => 800,
                'min_height' => 600,
                'ratio_width' => 4,
                'ratio_height' => 3,
                'formats' => 'JPG, PNG ou WEBP',
                'context' => 'landing',
                'visibility' => 'public',
                'placeholder_label' => 'Eventos',
                'gallery_title' => 'Eventos',
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(static::home());
    }

    public static function definition(string $slot): array
    {
        $definition = static::home()[$slot] ?? null;

        if (! $definition) {
            throw new InvalidArgumentException("Slot de midia desconhecido: {$slot}");
        }

        return $definition;
    }

    public static function relationNames(): array
    {
        return array_column(static::home(), 'relation');
    }

    public static function foreignKeyColumns(): array
    {
        return array_column(static::home(), 'field');
    }

    public static function ratioLabel(array $definition): string
    {
        return $definition['ratio_width'].' : '.$definition['ratio_height'];
    }
}
