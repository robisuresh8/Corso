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

    // View profile
    public function index()
    {
        $user = $this->request->user ?? null;

        if (!$user) {
            return $this->response
                ->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(401);
        }

        $userData = $this->userModel->find($user->id);

        return $this->response->setJSON($userData);
    }

    // Update profile
    public function update()
    {
        $user = $this->request->user ?? null;

        if (!$user) {
            return $this->response
                ->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(401);
        }

        $data = $this->request->getJSON(true);

        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }

        if (empty($updateData)) {
            return $this->response
                ->setJSON(['error' => 'No data provided'])
                ->setStatusCode(400);
        }

        $this->userModel->update($user->id, $updateData);

        return $this->response->setJSON([
            'message' => 'Profile updated successfully'
        ]);
    }

    // Change password
    public function changePassword()
    {
        $user = $this->request->user ?? null;

        if (!$user) {
            return $this->response
                ->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(401);
        }

        $data = $this->request->getJSON(true);

        if (empty($data['old_password']) || empty($data['new_password'])) {
            return $this->response
                ->setJSON(['error' => 'Old and new password required'])
                ->setStatusCode(400);
        }

        $result = $this->authService->changePassword(
            $user->id,
            $data['old_password'],
            $data['new_password']
        );

        return $this->response->setJSON($result);
    }
}
