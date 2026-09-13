<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Session;

class UserRepository
{
    /**
     * Centralized static mock user data.
     *
     * @var array<int, array{user_id: int, name: string, email: string, password: string, role: string, branch: string|null}>
     */
    protected static array $users = [
        [
            'user_id' => 1,
            'name' => 'Customer',
            'email' => 'customer@buildnfix.test',
            'password' => 'password',
            'role' => 'customer',
            'branch' => null,
        ],
        [
            'user_id' => 2,
            'name' => 'Admin Serdam',
            'email' => 'admin.serdam@buildnfix.test',
            'password' => 'password',
            'role' => 'admin',
            'branch' => 'Serdam',
        ],
        [
            'user_id' => 3,
            'name' => 'Admin Gajahmada',
            'email' => 'admin.gajahmada@buildnfix.test',
            'password' => 'password',
            'role' => 'admin',
            'branch' => 'Gajahmada',
        ],
        [
            'user_id' => 4,
            'name' => 'Admin Kota Baru',
            'email' => 'admin.kotabaru@buildnfix.test',
            'password' => 'password',
            'role' => 'admin',
            'branch' => 'Kota Baru',
        ],
        [
            'user_id' => 5,
            'name' => 'Owner',
            'email' => 'owner@buildnfix.test',
            'password' => 'password',
            'role' => 'owner',
            'branch' => 'all',
        ],
    ];

    /**
     * Get all users (static mock + session-registered customers) with overrides applied.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $registered = Session::get('registered_users', []);
        $all = array_merge(static::$users, $registered);
        $overrides = Session::get('user_overrides', []);

        if (! empty($overrides)) {
            foreach ($all as &$user) {
                $id = (int) ($user['user_id'] ?? $user['id'] ?? 0);
                if ($id > 0 && isset($overrides[$id])) {
                    $user = array_merge($user, $overrides[$id]);
                }
            }
        }

        return $all;
    }

    /**
     * Find a user by ID.
     *
     * @return array<string, mixed>|null
     */
    public static function find(int $userId): ?array
    {
        foreach (static::all() as $user) {
            $id = (int) ($user['user_id'] ?? $user['id'] ?? 0);
            if ($id === $userId) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Find a user by email (case-insensitive).
     *
     * @return array<string, mixed>|null
     */
    public static function findByEmail(string $email): ?array
    {
        $cleanEmail = strtolower(trim($email));

        foreach (static::all() as $user) {
            if (strtolower($user['email']) === $cleanEmail) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Authenticate credentials.
     * Returns user data WITHOUT password on success, or null on failure.
     *
     * @return array{user_id: int, name: string, email: string, role: string, branch: string|null}|null
     */
    public static function authenticate(string $email, string $password): ?array
    {
        $user = static::findByEmail($email);

        if (! $user || $user['password'] !== $password) {
            return null;
        }

        $userId = (int) ($user['user_id'] ?? $user['id'] ?? 1);

        return [
            'id' => $userId,
            'user_id' => $userId,
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'branch' => $user['branch'],
        ];
    }

    /**
     * Register a new Customer account (role is always forced to 'customer').
     *
     * @param  array{name: string, email: string, phone: string, password: string}  $data
     * @return array{success: bool, user?: array<string, mixed>, message: string}
     */
    public static function registerCustomer(array $data): array
    {
        if (static::findByEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'This email address is already registered. Please use a different email or sign in.',
            ];
        }

        $allUsers = static::all();
        $newId = count($allUsers) + 1;

        $newUser = [
            'id' => $newId,
            'user_id' => $newId,
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => trim($data['phone'] ?? ''),
            'password' => $data['password'],
            'role' => 'customer',
            'branch' => null,
        ];

        $registered = Session::get('registered_users', []);
        $registered[] = $newUser;
        Session::put('registered_users', $registered);

        return [
            'success' => true,
            'message' => 'Account created successfully! Welcome to Build n Fix.',
            'user' => [
                'id' => $newUser['id'],
                'user_id' => $newUser['user_id'],
                'name' => $newUser['name'],
                'email' => $newUser['email'],
                'role' => $newUser['role'],
                'branch' => $newUser['branch'],
            ],
        ];
    }

    /**
     * Update user's name in overrides & session.
     */
    public static function updateName(int $userId, string $newName): bool
    {
        $overrides = Session::get('user_overrides', []);
        if (! isset($overrides[$userId])) {
            $overrides[$userId] = [];
        }
        $overrides[$userId]['name'] = trim($newName);
        Session::put('user_overrides', $overrides);

        $registered = Session::get('registered_users', []);
        foreach ($registered as &$user) {
            $id = (int) ($user['user_id'] ?? $user['id'] ?? 0);
            if ($id === $userId) {
                $user['name'] = trim($newName);
            }
        }
        Session::put('registered_users', $registered);

        $sessionUser = Session::get('user');
        if (is_array($sessionUser)) {
            $sId = (int) ($sessionUser['user_id'] ?? $sessionUser['id'] ?? 0);
            if ($sId === $userId) {
                $sessionUser['name'] = trim($newName);
                Session::put('user', $sessionUser);
            }
        }

        return true;
    }

    /**
     * Verify user's current password.
     */
    public static function verifyPassword(int $userId, string $password): bool
    {
        $user = static::find($userId);
        if (! $user) {
            return false;
        }

        return ($user['password'] ?? 'password') === $password;
    }

    /**
     * Update user's password in overrides & registered users.
     */
    public static function updatePassword(int $userId, string $newPassword): bool
    {
        $overrides = Session::get('user_overrides', []);
        if (! isset($overrides[$userId])) {
            $overrides[$userId] = [];
        }
        $overrides[$userId]['password'] = $newPassword;
        Session::put('user_overrides', $overrides);

        $registered = Session::get('registered_users', []);
        foreach ($registered as &$user) {
            $id = (int) ($user['user_id'] ?? $user['id'] ?? 0);
            if ($id === $userId) {
                $user['password'] = $newPassword;
            }
        }
        Session::put('registered_users', $registered);

        return true;
    }
}
