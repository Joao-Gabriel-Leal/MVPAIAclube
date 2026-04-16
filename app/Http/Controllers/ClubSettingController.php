<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateClubSettingRequest;
use App\Models\ClubSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClubSettingController extends Controller
{
    public function edit(): View
    {
        return view('club-settings.edit', [
            'clubSetting' => ClubSetting::current(),
        ]);
    }

    public function update(UpdateClubSettingRequest $request): RedirectResponse
    {
        $clubSetting = ClubSetting::current();
        $clubSetting->update($request->validated());

        return redirect()
            ->route('club-settings.edit')
            ->with('status', 'Prefixo da carteirinha atualizado com sucesso.');
    }
}
