<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\MediaAssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $request->user()->loadMissing([
            'member.plan',
            'member.primaryBranch',
            'dependent.branch',
            'dependent.member.user',
            'dependent.member.plan',
        ]);

        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, MediaAssetService $mediaAssetService): RedirectResponse
    {
        $user = $request->user();
        $allowedAttributes = $user->isCardHolder()
            ? ['email', 'phone']
            : ['name', 'email'];

        $user->fill($request->safe()->only($allowedAttributes));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('profile_photo')) {
            $mediaAssetService->replaceUserProfilePhoto($user, $request->file('profile_photo'));
        }

        $user->save();
        Cache::forget("dashboard.summary.{$user->id}");

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort_if($request->user()->isCardHolder(), 403);

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
