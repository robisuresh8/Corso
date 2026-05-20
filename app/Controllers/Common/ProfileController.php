<?php

namespace App\Controllers\Common;

use App\Services\AuthService;
use App\Models\UserModel;
use App\Controllers\BaseController;

class ProfileController extends BaseController
{
    protected $authService;
    protected $userModel;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->userModel   = new UserModel();
    }

    private function getAuthUserId(): ?int
    {
        // JWTAuth filter sets request->user_id
        if (!empty($this->request->user_id)) return (int) $this->request->user_id;
        // Fallback: session (AdminAuth sets auth_user in session)
        $decoded = session()->get('auth_user');
        if ($decoded) {
            return (int) (is_object($decoded) ? ($decoded->uid ?? 0) : ($decoded['uid'] ?? 0));
        }
        return null;
    }

    public function index()
    {
        $userId = $this->getAuthUserId();
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $userData = $this->userModel->find($userId);
        if (!$userData) {
            return $this->response->setJSON(['error' => 'User not found'])->setStatusCode(404);
        }

        // Sensitive fields hatao
        unset($userData['password'], $userData['password_hash'], $userData['activation_token'], $userData['reset_token']);

        return $this->response->setJSON($userData);
    }

    public function update()
    {
        $userId = $this->getAuthUserId();
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $data = $this->request->getJSON(true) ?? [];
        $updateData = [];

        if (isset($data['name']) && trim($data['name']) !== '') {
            $updateData['name'] = trim($data['name']);
        }
        if (isset($data['phone'])) {
            $updateData['phone'] = trim($data['phone']);
        }

        if (empty($updateData)) {
            return $this->response->setJSON(['error' => 'No data provided'])->setStatusCode(400);
        }

        $this->userModel->update($userId, $updateData);
        return $this->response->setJSON(['message' => 'Profile updated successfully']);
    }

    public function changePassword()
    {
        $userId = $this->getAuthUserId();
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $data = $this->request->getJSON(true) ?? [];

        if (empty($data['old_password']) || empty($data['new_password'])) {
            return $this->response->setJSON(['error' => 'Old and new password required'])->setStatusCode(400);
        }

        $result = $this->authService->changePassword(
            $userId,
            $data['old_password'],
            $data['new_password']
        );

        return $this->response->setJSON($result);
    }
}