<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateClubSettingRequest;
use App\Models\ClubSetting;
use App\Models\Plan;
use App\Services\MediaAssetService;
use App\Support\ClubMediaSlots;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class ClubSettingController extends Controller
{
    private const EXPECTED_UPLOAD_LIMIT_BYTES = 15728640;
    private const EXPECTED_POST_LIMIT_BYTES = 18874368;

    public function edit(): View
    {
        $clubSetting = ClubSetting::current();
        $uploadLimitRaw = (string) ini_get('upload_max_filesize');
        $postLimitRaw = (string) ini_get('post_max_size');
        $uploadLimitBytes = $this->iniSizeToBytes($uploadLimitRaw);
        $postLimitBytes = $this->iniSizeToBytes($postLimitRaw);

        return view('club-settings.edit', [
            'clubSetting' => $clubSetting,
            'mediaSlots' => ClubMediaSlots::home(),
            'homeMediaLibrary' => $clubSetting->homeMediaLibrary(),
            'plans' => Plan::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'uploadRuntimeWarning' => $uploadLimitBytes < self::EXPECTED_UPLOAD_LIMIT_BYTES || $postLimitBytes < self::EXPECTED_POST_LIMIT_BYTES
                ? [
                    'upload_limit' => $uploadLimitRaw !== '' ? $uploadLimitRaw : 'desconhecido',
                    'post_limit' => $postLimitRaw !== '' ? $postLimitRaw : 'desconhecido',
                ]
                : null,
        ]);
    }

    public function update(UpdateClubSettingRequest $request, MediaAssetService $mediaAssetService): RedirectResponse
    {
        $clubSetting = ClubSetting::current();
        $clubSetting->update($request->safe()->only([
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
        ]));
        $slotsMarkedForRemoval = collect((array) $request->input('remove_slots', []));

        if ($request->boolean('remove_logo')) {
            $mediaAssetService->removeClubLogo($clubSetting);
        }

        if ($request->hasFile('logo')) {
            try {
                $mediaAssetService->replaceClubLogo($clubSetting, $request->file('logo'));
            } catch (RuntimeException) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'logo' => 'Nao foi possivel enviar o logo principal. Tente novamente com outra imagem.',
                    ]);
            }
        }

        foreach (ClubMediaSlots::keys() as $slot) {
            if ($slotsMarkedForRemoval->contains($slot)) {
                $mediaAssetService->removeHomeSlot($clubSetting, $slot);
            }

            if ($request->hasFile($slot)) {
                try {
                    $mediaAssetService->replaceHomeSlot($clubSetting, $slot, $request->file($slot));
                } catch (RuntimeException) {
                    $slotLabel = mb_strtolower(ClubMediaSlots::definition($slot)['title']);

                    return back()
                        ->withInput()
                        ->withErrors([
                            $slot => "Nao foi possivel enviar {$slotLabel}. Tente novamente com outra imagem.",
                        ]);
                }
            }
        }

        return redirect()
            ->route('club-settings.edit')
            ->with('status', 'Configuracoes do clube atualizadas com sucesso.');
    }

    private function iniSizeToBytes(string $value): int
    {
        $normalized = trim($value);

        if ($normalized === '') {
            return 0;
        }

        $unit = strtolower(substr($normalized, -1));
        $number = (float) $normalized;

        return match ($unit) {
            'g' => (int) round($number * 1024 * 1024 * 1024),
            'm' => (int) round($number * 1024 * 1024),
            'k' => (int) round($number * 1024),
            default => (int) round($number),
        };
    }
}
