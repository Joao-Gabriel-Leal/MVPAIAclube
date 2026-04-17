<?php

namespace App\Http\Requests;

use App\Support\ClubMediaSlots;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClubSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminMatrix() ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'brand_name' => ['required', 'string', 'max:80'],
            'card_prefix' => ['required', 'string', 'regex:/^[A-Z0-9]{2,6}$/'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_subtitle' => ['required', 'string', 'max:400'],
            'about_text' => ['required', 'string', 'max:1200'],
            'login_title' => ['nullable', 'string', 'max:120'],
            'login_subtitle' => ['nullable', 'string', 'max:300'],
            'home_about_title' => ['nullable', 'string', 'max:160'],
            'home_gallery_title' => ['nullable', 'string', 'max:160'],
            'home_gallery_subtitle' => ['nullable', 'string', 'max:300'],
            'home_branches_title' => ['nullable', 'string', 'max:160'],
            'home_branches_subtitle' => ['nullable', 'string', 'max:300'],
            'home_plans_title' => ['nullable', 'string', 'max:160'],
            'home_plans_subtitle' => ['nullable', 'string', 'max:300'],
            'home_final_cta_title' => ['nullable', 'string', 'max:160'],
            'enrollment_intro' => ['nullable', 'string', 'max:400'],
            'enrollment_notice' => ['nullable', 'string', 'max:400'],
            'recommended_plan_id' => ['nullable', 'integer', Rule::exists('plans', 'id')],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9()\s\-+]+$/'],
            'site_whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9()\s\-+]+$/'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:80'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'primary_color' => ['required', 'regex:/^#[A-F0-9]{6}$/i'],
            'secondary_color' => ['required', 'regex:/^#[A-F0-9]{6}$/i'],
            'accent_color' => ['required', 'regex:/^#[A-F0-9]{6}$/i'],
            'logo' => [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml',
                'max:6144',
            ],
            'remove_logo' => ['sometimes', 'boolean'],
            'remove_slots' => ['sometimes', 'array'],
        ];

        foreach (ClubMediaSlots::keys() as $slot) {
            $definition = ClubMediaSlots::definition($slot);

            $rules[$slot] = [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:15360',
                function (string $attribute, mixed $value, \Closure $fail) use ($definition): void {
                    if (! $value) {
                        return;
                    }

                    $imageSize = @getimagesize($value->getRealPath());

                    if ($imageSize === false) {
                        $fail('Envie uma imagem valida para este espaco.');

                        return;
                    }

                    [$width, $height] = $imageSize;
                    if ($width < $definition['min_width'] || $height < $definition['min_height']) {
                        $fail("Use uma imagem com pelo menos {$definition['min_width']} x {$definition['min_height']} px.");
                    }
                },
            ];
        }

        $rules['remove_slots.*'] = ['required', Rule::in(ClubMediaSlots::keys())];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'brand_name' => trim((string) $this->input('brand_name')),
            'card_prefix' => strtoupper(trim((string) $this->input('card_prefix'))),
            'hero_title' => trim((string) $this->input('hero_title')),
            'hero_subtitle' => trim((string) $this->input('hero_subtitle')),
            'about_text' => trim((string) $this->input('about_text')),
            'login_title' => trim((string) $this->input('login_title')),
            'login_subtitle' => trim((string) $this->input('login_subtitle')),
            'home_about_title' => trim((string) $this->input('home_about_title')),
            'home_gallery_title' => trim((string) $this->input('home_gallery_title')),
            'home_gallery_subtitle' => trim((string) $this->input('home_gallery_subtitle')),
            'home_branches_title' => trim((string) $this->input('home_branches_title')),
            'home_branches_subtitle' => trim((string) $this->input('home_branches_subtitle')),
            'home_plans_title' => trim((string) $this->input('home_plans_title')),
            'home_plans_subtitle' => trim((string) $this->input('home_plans_subtitle')),
            'home_final_cta_title' => trim((string) $this->input('home_final_cta_title')),
            'enrollment_intro' => trim((string) $this->input('enrollment_intro')),
            'enrollment_notice' => trim((string) $this->input('enrollment_notice')),
            'recommended_plan_id' => $this->filled('recommended_plan_id') ? (int) $this->input('recommended_plan_id') : null,
            'site_email' => trim((string) $this->input('site_email')),
            'site_phone' => trim((string) $this->input('site_phone')),
            'site_whatsapp' => trim((string) $this->input('site_whatsapp')),
            'instagram_url' => trim((string) $this->input('instagram_url')),
            'facebook_url' => trim((string) $this->input('facebook_url')),
            'seo_title' => trim((string) $this->input('seo_title')),
            'seo_description' => trim((string) $this->input('seo_description')),
            'primary_color' => strtoupper(trim((string) $this->input('primary_color'))),
            'secondary_color' => strtoupper(trim((string) $this->input('secondary_color'))),
            'accent_color' => strtoupper(trim((string) $this->input('accent_color'))),
            'remove_logo' => $this->boolean('remove_logo'),
        ]);
    }

    public function messages(): array
    {
        $messages = [
            'brand_name.required' => 'Informe o nome principal do clube.',
            'card_prefix.required' => 'Informe o prefixo da carteirinha.',
            'card_prefix.regex' => 'Use de 2 a 6 caracteres com letras ou numeros no prefixo da carteirinha.',
            'hero_title.required' => 'Informe o titulo principal da home.',
            'hero_subtitle.required' => 'Informe o subtitulo principal da home.',
            'about_text.required' => 'Informe o texto institucional do clube.',
            'recommended_plan_id.exists' => 'Selecione um plano valido para destaque.',
            'site_email.email' => 'Informe um e-mail valido para o contato principal.',
            'site_phone.regex' => 'Use apenas numeros, espacos, parenteses, + ou tracos no telefone do site.',
            'site_whatsapp.regex' => 'Use apenas numeros, espacos, parenteses, + ou tracos no WhatsApp do site.',
            'instagram_url.url' => 'Informe uma URL valida para o Instagram.',
            'facebook_url.url' => 'Informe uma URL valida para o Facebook.',
            'primary_color.required' => 'Informe a cor primaria em hexadecimal.',
            'secondary_color.required' => 'Informe a cor secundaria em hexadecimal.',
            'accent_color.required' => 'Informe a cor de destaque em hexadecimal.',
            'primary_color.regex' => 'Use o formato hexadecimal completo, por exemplo #2958B8.',
            'secondary_color.regex' => 'Use o formato hexadecimal completo, por exemplo #4E79CC.',
            'accent_color.regex' => 'Use o formato hexadecimal completo, por exemplo #F2CF2F.',
            'logo.uploaded' => 'O servidor local recusou o logo antes da validacao. Reinicie o ambiente com limite maior e tente novamente.',
            'logo.mimetypes' => 'O logo principal deve estar em JPG, PNG, WEBP ou SVG.',
            'logo.max' => 'O logo principal deve ter no maximo 6 MB.',
            'uploaded' => 'O servidor local recusou :attribute antes da validacao. Confirme o limite de 15 MB do ambiente e tente novamente.',
            'mimetypes' => 'Use um arquivo de imagem compativel em :attribute.',
            'max' => 'O arquivo enviado em :attribute excede o tamanho permitido.',
        ];

        foreach (ClubMediaSlots::keys() as $slot) {
            $slotLabel = mb_strtolower(ClubMediaSlots::definition($slot)['title']);

            $messages["{$slot}.uploaded"] = "O servidor local recusou {$slotLabel} antes da validacao. Confirme o limite de 15 MB do ambiente e tente novamente.";
            $messages["{$slot}.mimetypes"] = 'Use JPG, PNG ou WEBP nesta imagem.';
            $messages["{$slot}.max"] = 'Esta imagem ultrapassa o limite de 15 MB.';
        }

        return $messages;
    }

    public function attributes(): array
    {
        $attributes = [
            'brand_name' => 'nome da marca',
            'card_prefix' => 'prefixo da carteirinha',
            'hero_title' => 'titulo principal',
            'hero_subtitle' => 'subtitulo principal',
            'about_text' => 'texto institucional',
            'login_title' => 'titulo do login',
            'login_subtitle' => 'subtitulo do login',
            'home_about_title' => 'titulo da secao sobre',
            'home_gallery_title' => 'titulo da secao galeria',
            'home_gallery_subtitle' => 'subtitulo da secao galeria',
            'home_branches_title' => 'titulo da secao filiais',
            'home_branches_subtitle' => 'subtitulo da secao filiais',
            'home_plans_title' => 'titulo da secao planos',
            'home_plans_subtitle' => 'subtitulo da secao planos',
            'home_final_cta_title' => 'titulo do CTA final',
            'enrollment_intro' => 'texto de abertura da adesao',
            'enrollment_notice' => 'aviso da adesao',
            'recommended_plan_id' => 'plano recomendado',
            'site_email' => 'e-mail do site',
            'site_phone' => 'telefone do site',
            'site_whatsapp' => 'WhatsApp do site',
            'instagram_url' => 'URL do Instagram',
            'facebook_url' => 'URL do Facebook',
            'seo_title' => 'titulo SEO',
            'seo_description' => 'descricao SEO',
            'primary_color' => 'cor primaria',
            'secondary_color' => 'cor secundaria',
            'accent_color' => 'cor de destaque',
            'logo' => 'logo principal',
        ];

        foreach (ClubMediaSlots::keys() as $slot) {
            $definition = ClubMediaSlots::definition($slot);
            $attributes[$slot] = mb_strtolower($definition['title']);
        }

        return $attributes;
    }
}
