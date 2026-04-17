<?php

namespace App\Models;

use App\Support\ClubMediaSlots;
use App\Support\MaskFormatter;
use Database\Factories\ClubSettingFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ClubSetting extends Model
{
    /** @use HasFactory<ClubSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'brand_name',
        'card_prefix',
        'hero_title',
        'hero_subtitle',
        'about_text',
        'login_title',
        'login_subtitle',
        'home_about_title',
        'home_gallery_title',
        'home_gallery_subtitle',
        'home_branches_title',
        'home_branches_subtitle',
        'home_plans_title',
        'home_plans_subtitle',
        'home_final_cta_title',
        'enrollment_intro',
        'enrollment_notice',
        'recommended_plan_id',
        'site_email',
        'site_phone',
        'site_whatsapp',
        'instagram_url',
        'facebook_url',
        'seo_title',
        'seo_description',
        'primary_color',
        'secondary_color',
        'accent_color',
        'logo_media_asset_id',
        'hero_banner_media_asset_id',
        'gallery_featured_media_asset_id',
        'gallery_1_media_asset_id',
        'gallery_2_media_asset_id',
        'gallery_3_media_asset_id',
        'gallery_4_media_asset_id',
        'gallery_5_media_asset_id',
    ];

    protected function casts(): array
    {
        return [
            'recommended_plan_id' => 'integer',
        ];
    }

    public function heroBannerMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'hero_banner_media_asset_id');
    }

    public function logoMedia()
    {
        return $this->belongsTo(MediaAsset::class, 'logo_media_asset_id');
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

    public function recommendedPlan()
    {
        return $this->belongsTo(Plan::class, 'recommended_plan_id');
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

    public function resolvedBrandName(): string
    {
        return trim((string) ($this->brand_name ?: config('app.name', 'Clube AABB'))) ?: 'Clube AABB';
    }

    public function resolvedHeroTitle(): string
    {
        return trim((string) $this->hero_title) !== ''
            ? trim((string) $this->hero_title)
            : 'Clube AABB';
    }

    public function resolvedHeroSubtitle(): string
    {
        return trim((string) $this->hero_subtitle) !== ''
            ? trim((string) $this->hero_subtitle)
            : 'Esporte, lazer, convivio e uma rede de clubes preparada para receber familias, associados e convidados em diferentes cidades do Brasil.';
    }

    public function resolvedAboutText(): string
    {
        return trim((string) $this->about_text) !== ''
            ? trim((string) $this->about_text)
            : 'O Clube AABB conecta esporte, lazer, vida social e experiencias familiares em unidades com infraestrutura completa, agenda ativa e um ambiente acolhedor para quem quer viver o clube de forma simples.';
    }

    public function resolvedLoginTitle(): string
    {
        return $this->resolvedText($this->login_title, 'Entrar no '.$this->resolvedBrandName());
    }

    public function resolvedLoginSubtitle(): string
    {
        return $this->resolvedText($this->login_subtitle, 'Acesso a reservas, carteirinha e administracao.');
    }

    public function resolvedHomeAboutTitle(): string
    {
        return $this->resolvedText(
            $this->home_about_title,
            'Uma apresentacao institucional mais clara para entender o '.$this->resolvedBrandName()
        );
    }

    public function resolvedHomeGalleryTitle(): string
    {
        return $this->resolvedText(
            $this->home_gallery_title,
            'Veja a atmosfera das unidades antes de entrar em detalhes operacionais'
        );
    }

    public function resolvedHomeGallerySubtitle(): string
    {
        return trim((string) $this->home_gallery_subtitle);
    }

    public function resolvedHomeBranchesTitle(): string
    {
        return $this->resolvedText(
            $this->home_branches_title,
            'Conheca algumas unidades da rede em cidades estrategicas'
        );
    }

    public function resolvedHomeBranchesSubtitle(): string
    {
        return trim((string) $this->home_branches_subtitle);
    }

    public function resolvedHomePlansTitle(): string
    {
        return $this->resolvedText(
            $this->home_plans_title,
            'Categorias demonstrativas para apresentar a operacao do sistema'
        );
    }

    public function resolvedHomePlansSubtitle(): string
    {
        return trim((string) $this->home_plans_subtitle);
    }

    public function resolvedHomeFinalCtaTitle(): string
    {
        return $this->resolvedText(
            $this->home_final_cta_title,
            'Veja a rede, explore as filiais e apresente uma experiencia de clube mais completa'
        );
    }

    public function resolvedEnrollmentIntro(): string
    {
        return $this->resolvedText(
            $this->enrollment_intro,
            'O cadastro entra primeiro na fila de propostas da unidade. A equipe local faz a analise antes de liberar o associado na base ativa.'
        );
    }

    public function resolvedEnrollmentNotice(): string
    {
        return $this->resolvedText(
            $this->enrollment_notice,
            'O envio nao ativa o cadastro imediatamente. A administracao da unidade avalia a solicitacao antes da aprovacao final.'
        );
    }

    public function resolvedSeoTitle(): string
    {
        return $this->resolvedText($this->seo_title, $this->resolvedBrandName());
    }

    public function resolvedSeoDescription(): string
    {
        return $this->resolvedText($this->seo_description, $this->resolvedAboutText());
    }

    public function sitePhoneLink(): ?string
    {
        $digits = MaskFormatter::digits($this->getRawOriginal('site_phone'));

        return $digits ? 'tel:'.$digits : null;
    }

    public function siteWhatsappLink(): ?string
    {
        $digits = MaskFormatter::digits($this->getRawOriginal('site_whatsapp'));

        if (! $digits) {
            return null;
        }

        return 'https://wa.me/'.(Str::startsWith($digits, '55') ? $digits : '55'.$digits);
    }

    public function hasPublicContactChannels(): bool
    {
        return filled($this->site_email)
            || filled($this->site_phone)
            || filled($this->site_whatsapp)
            || filled($this->instagram_url)
            || filled($this->facebook_url);
    }

    public function themeCssVariables(): array
    {
        $primary = $this->primary_color ?: '#2958B8';
        $secondary = $this->secondary_color ?: '#4E79CC';
        $accent = $this->accent_color ?: '#F2CF2F';

        return [
            '--club-surface-soft' => static::mixHexWithWhite($accent, 0.94),
            '--club-surface-muted' => static::mixHexWithWhite($accent, 0.88),
            '--club-brand' => static::hexToRgbChannels($primary, '41 88 184'),
            '--club-brand-strong' => static::hexToRgbChannels($secondary, '78 121 204'),
            '--club-brand-soft' => static::mixHexWithWhite($primary, 0.9),
            '--club-brand-glow' => static::mixHexWithWhite($accent, 0.72),
            '--club-accent' => static::hexToRgbChannels($accent, '242 207 47'),
            '--club-accent-soft' => static::mixHexWithWhite($accent, 0.82),
        ];
    }

    public function themeCssVariablesInline(): string
    {
        return collect($this->themeCssVariables())
            ->map(fn (string $value, string $key) => "{$key}: {$value};")
            ->implode(' ');
    }

    protected static function hexToRgbChannels(string $hex, string $fallback): string
    {
        $normalized = ltrim(trim($hex), '#');

        if (strlen($normalized) === 3) {
            $normalized = collect(str_split($normalized))
                ->map(fn (string $character) => $character.$character)
                ->implode('');
        }

        if (strlen($normalized) !== 6 || ! ctype_xdigit($normalized)) {
            return $fallback;
        }

        $rgb = [
            hexdec(substr($normalized, 0, 2)),
            hexdec(substr($normalized, 2, 2)),
            hexdec(substr($normalized, 4, 2)),
        ];

        return implode(' ', $rgb);
    }

    protected static function mixHexWithWhite(string $hex, float $whiteWeight): string
    {
        $whiteWeight = max(0, min(1, $whiteWeight));
        $base = array_map('intval', explode(' ', static::hexToRgbChannels($hex, '0 0 0')));

        $mixed = array_map(
            fn (int $channel) => (int) round(($channel * (1 - $whiteWeight)) + (255 * $whiteWeight)),
            $base
        );

        return implode(' ', $mixed);
    }

    protected function sitePhone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => MaskFormatter::phone($value),
            set: fn (?string $value) => MaskFormatter::digits($value),
        );
    }

    protected function siteWhatsapp(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => MaskFormatter::phone($value),
            set: fn (?string $value) => MaskFormatter::digits($value),
        );
    }

    protected function resolvedText(?string $value, string $fallback): string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : $fallback;
    }
}
