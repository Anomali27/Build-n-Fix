<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login')->with('info', 'Please sign in to access your profile settings.');
        }

        return view('profile.index', [
            'profile' => $user->toProfileArray(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update([
            'name' => trim((string) $request->validated('name')),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function password(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! Hash::check((string) $request->validated('current_password'), $user->password)) {
            return back()->with('error', 'The current password you entered is incorrect.');
        }

        $user->update([
            'password' => (string) $request->validated('new_password'),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
