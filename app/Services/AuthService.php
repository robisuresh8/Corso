<?php

namespace App\Services;

use App\Models\UserModel;

class AuthService
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword): array
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return [
                'status' => false,
                'error'  => 'User not found',
            ];
        }

        // Ensure we treat $user as array
        $passwordHash = is_array($user) ? ($user['password'] ?? '') : ($user->password ?? '');

        if (!is_string($passwordHash) || !password_verify($oldPassword, $passwordHash)) {
            return [
                'status' => false,
                'error'  => 'Old password incorrect',
            ];
        }

        // Validate new password length
        if (strlen($newPassword) < 6) {
            return [
                'status' => false,
                'error'  => 'New password must be at least 6 characters',
            ];
        }

        $this->userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);

        return [
            'status'  => true,
            'message' => 'Password changed successfully',
        ];
    }
}
