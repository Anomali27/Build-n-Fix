<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(Auth::user()->homeUrl());
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->credentials())) {
            return back()
                ->withInput($request->only('email'))
                ->with('auth_error', 'The email or password you entered is incorrect. Please try again.');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        return redirect()
            ->to($user->homeUrl())
            ->with('success', 'Welcome back, '.$user->name.'!');
    }

    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(Auth::user()->homeUrl());
        }

        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $email = strtolower(trim((string) $request->validated('email')));

        if (User::where('email', $email)->exists()) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('auth_error', 'This email address is already registered. Please use a different email or sign in.');
        }

        $user = User::registerCustomer($request->safe()->only(['name', 'email', 'phone', 'password']));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->to($user->homeUrl())
            ->with('success', 'Account created successfully! Welcome to Build n Fix.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been signed out successfully.');
    }
}
