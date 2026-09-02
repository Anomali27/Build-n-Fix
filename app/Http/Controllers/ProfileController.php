<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    /**
     * Display the profile settings page.
     */
    public function index(): View|RedirectResponse
    {
        if (! Session::has('user') && ! auth()->check()) {
            return redirect()->route('login')->with('info', 'Please sign in to access your profile settings.');
        }

        $profile = $this->profileService->getProfile();
        if (! $profile) {
            return redirect()->route('login');
        }

        return view('profile.index', [
            'profile' => $profile,
        ]);
    }

    /**
     * Update user profile information (Name).
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Full Name is required.',
        ]);

        $result = $this->profileService->updateProfile([
            'name' => (string) $request->input('name'),
        ]);

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Change user account password.
     */
    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6'],
            'confirm_password' => ['required', 'string', 'same:new_password'],
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 6 characters.',
            'confirm_password.required' => 'Please confirm your new password.',
            'confirm_password.same' => 'Password confirmation does not match.',
        ]);

        $result = $this->profileService->changePassword([
            'current_password' => (string) $request->input('current_password'),
            'new_password' => (string) $request->input('new_password'),
            'confirm_password' => (string) $request->input('confirm_password'),
        ]);

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}
