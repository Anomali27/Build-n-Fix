<?php

namespace App\Services;

use App\Repositories\UserRepositories;
use Illuminate\Support\Facades\Session;

class ProfileService
{
    /**
     * Get current authenticated profile.
     *
     * @return array<string, mixed>|null
     */
    public function getProfile(): ?array
    {
        $sessionUser = Session::get('user');
        if (! is_array($sessionUser)) {
            return null;
        }

        $userId = (int) ($sessionUser['user_id'] ?? $sessionUser['id'] ?? 0);
        $userData = UserRepositories::find($userId);

        if (! $userData) {
            return [
                'user_id' => $userId > 0 ? $userId : 1,
                'name' => $sessionUser['name'] ?? 'User',
                'email' => $sessionUser['email'] ?? 'user@buildnfix.test',
                'role' => $sessionUser['role'] ?? 'customer',
                'branch' => $sessionUser['branch'] ?? null,
                'status' => 'Active',
            ];
        }

        return [
            'user_id' => (int) ($userData['user_id'] ?? $userData['id'] ?? 1),
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $userData['role'] ?? 'customer',
            'branch' => $userData['branch'] ?? null,
            'status' => 'Active',
        ];
    }

    /**
     * Update user profile name.
     *
     * @param  array{name: string}  $data
     * @return array{success: bool, message: string}
     */
    public function updateProfile(array $data): array
    {
        $profile = $this->getProfile();
        if (! $profile) {
            return [
                'success' => false,
                'message' => 'User session expired. Please sign in again.',
            ];
        }

        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            return [
                'success' => false,
                'message' => 'Full Name is required.',
            ];
        }

        UserRepositories::updateName((int) $profile['user_id'], $name);

        return [
            'success' => true,
            'message' => 'Profile updated successfully.',
        ];
    }

    /**
     * Mock change account password.
     *
     * @param  array{current_password: string, new_password: string, confirm_password: string}  $data
     * @return array{success: bool, message: string}
     */
    public function changePassword(array $data): array
    {
        $profile = $this->getProfile();
        if (! $profile) {
            return [
                'success' => false,
                'message' => 'User session expired. Please sign in again.',
            ];
        }

        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return [
                'success' => false,
                'message' => 'All password fields are required.',
            ];
        }

        if (! UserRepositories::verifyPassword((int) $profile['user_id'], $currentPassword)) {
            return [
                'success' => false,
                'message' => 'The current password you entered is incorrect.',
            ];
        }

        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'New password and password confirmation do not match.',
            ];
        }

        if (strlen($newPassword) < 6) {
            return [
                'success' => false,
                'message' => 'New password must be at least 6 characters long.',
            ];
        }

        UserRepositories::updatePassword((int) $profile['user_id'], $newPassword);

        return [
            'success' => true,
            'message' => 'Password updated successfully.',
        ];
    }
}
