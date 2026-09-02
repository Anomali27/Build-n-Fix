<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    // ──────────────────────────────────────────────
    // LOGIN
    // ──────────────────────────────────────────────

    public function showLoginForm(): View|RedirectResponse
    {
        if ($this->authService->check()) {
            return redirect()->to(
                $this->authService->getRedirectUrl(
                    $this->authService->user()['role'] ?? 'customer'
                )
            );
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ]);

        $result = $this->authService->attempt(
            (string) $request->input('email'),
            (string) $request->input('password')
        );

        if (! $result['success']) {
            return back()
                ->withInput($request->only('email'))
                ->with('auth_error', $result['message']);
        }

        return redirect()
            ->to($result['redirect'])
            ->with('success', $result['message']);
    }

    // ──────────────────────────────────────────────
    // REGISTER
    // ──────────────────────────────────────────────

    public function showRegisterForm(): View|RedirectResponse
    {
        if ($this->authService->check()) {
            return redirect()->to(
                $this->authService->getRedirectUrl(
                    $this->authService->user()['role'] ?? 'customer'
                )
            );
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'name.required' => 'Full name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => 'Phone number is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy.',
        ]);

        $result = $this->authService->registerCustomer([
            'name' => (string) $request->input('name'),
            'email' => (string) $request->input('email'),
            'phone' => (string) $request->input('phone'),
            'password' => (string) $request->input('password'),
        ]);

        if (! $result['success']) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('auth_error', $result['message']);
        }

        return redirect()
            ->to($result['redirect'])
            ->with('success', $result['message']);
    }

    // ──────────────────────────────────────────────
    // LOGOUT
    // ──────────────────────────────────────────────

    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('login')->with('success', 'You have been signed out successfully.');
    }
}
