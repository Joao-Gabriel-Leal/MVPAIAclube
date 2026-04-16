<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateClubSettingRequest;
use App\Models\ClubSetting;
use App\Services\MediaAssetService;
use App\Support\ClubMediaSlots;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClubSettingController extends Controller
{
    public function edit(): View
    {
        $clubSetting = ClubSetting::current();

        return view('club-settings.edit', [
            'clubSetting' => $clubSetting,
            'mediaSlots' => ClubMediaSlots::home(),
            'homeMediaLibrary' => $clubSetting->homeMediaLibrary(),
        ]);
    }

    public function update(UpdateClubSettingRequest $request, MediaAssetService $mediaAssetService): RedirectResponse
    {
        $clubSetting = ClubSetting::current();
        $clubSetting->update($request->safe()->only(['card_prefix']));
        $slotsMarkedForRemoval = collect((array) $request->input('remove_slots', []));

        foreach (ClubMediaSlots::keys() as $slot) {
            if ($slotsMarkedForRemoval->contains($slot)) {
                $mediaAssetService->removeHomeSlot($clubSetting, $slot);
            }

            if ($request->hasFile($slot)) {
                $mediaAssetService->replaceHomeSlot($clubSetting, $slot, $request->file($slot));
            }
        }

        return redirect()
            ->route('club-settings.edit')
            ->with('status', 'Configuracoes do clube atualizadas com sucesso.');
    }
}
