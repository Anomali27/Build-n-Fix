<?php

namespace App\Services;

use App\Repositories\UserRepositories;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthService
{
    /**
     * Attempt to authenticate a user and store session data (without password).
     *
     * @return array{success: bool, user?: array<string, mixed>, message: string, redirect?: string}
     */
    public function attempt(string $email, string $password): array
    {
        $user = UserRepositories::authenticate($email, $password);

        if (! $user) {
            return [
                'success' => false,
                'message' => 'The email or password you entered is incorrect. Please try again.',
            ];
        }

        $this->createSession($user);

        return [
            'success' => true,
            'user' => $user,
            'message' => 'Welcome back, '.$user['name'].'!',
            'redirect' => $this->getRedirectUrl($user['role']),
        ];
    }

    /**
     * Register a new Customer account (role always forced to customer).
     *
     * @param  array{name: string, email: string, phone: string, password: string}  $data
     * @return array{success: bool, user?: array<string, mixed>, message: string, redirect?: string}
     */
    public function registerCustomer(array $data): array
    {
        $result = UserRepositories::registerCustomer($data);

        if (! $result['success']) {
            return $result;
        }

        $this->createSession($result['user']);

        return [
            'success' => true,
            'user' => $result['user'],
            'message' => $result['message'],
            'redirect' => '/',
        ];
    }

    /**
     * Get redirect URL by role.
     */
    public function getRedirectUrl(string $role): string
    {
        return match ($role) {
            'admin', 'owner' => '/dashboard',
            default => '/',
        };
    }

    /**
     * Store user data in session (never includes password).
     *
     * @param  array<string, mixed>  $user
     */
    public function createSession(array $user): void
    {
        // Session stores only safe fields — no password, no remember_token.
        Session::put('user', $user);

        // GenericUser needs remember_token to satisfy Laravel Auth internals.
        // It is NOT persisted to the session.
        Auth::setUser(new GenericUser(array_merge($user, ['remember_token' => null])));
    }

    /**
     * Check if a user is authenticated via session.
     */
    public function check(): bool
    {
        return Session::has('user');
    }

    /**
     * Get current user from session.
     *
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        return Session::get('user');
    }

    /**
     * Log out the current user.
     */
    public function logout(): void
    {
        Session::forget('user');
        Session::invalidate();
        Session::regenerateToken();
        Auth::logout();
    }
}
