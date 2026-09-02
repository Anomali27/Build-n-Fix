<?php

namespace App\Data;

use Illuminate\Support\Facades\Session;

class UserData
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
     * Get all users (static mock + session-registered customers).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $registered = Session::get('registered_users', []);

        return array_merge(static::$users, $registered);
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

        return [
            'user_id' => $user['user_id'],
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
                'user_id' => $newUser['user_id'],
                'name' => $newUser['name'],
                'email' => $newUser['email'],
                'role' => $newUser['role'],
                'branch' => $newUser['branch'],
            ],
        ];
    }
}
