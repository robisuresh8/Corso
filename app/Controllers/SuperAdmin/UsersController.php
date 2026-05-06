<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\PermissionService;
use Config\Permissions;

class UsersController extends BaseController
{
    protected $userModel;
    protected $permService;
    protected $config;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->permService = new PermissionService();
        $this->config      = new Permissions();
    }

    /** GET /api/super-admin/users – list all users */
    public function index()
    {
        $users = $this->userModel->select('id, name, email, role, status, created_at')
            ->orderBy('created_at', 'DESC')
            ->findAll();
        return $this->response->setJSON(['users' => $users]);
    }

    /** POST /api/super-admin/users – create user */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? [];
        $name     = trim($data['name'] ?? '');
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role     = $data['role'] ?? 'student';

        if (empty($name) || empty($email) || empty($password)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Name, email and password are required']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Invalid email format']);
        }
        if (strlen($password) < 8) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Password must be at least 8 characters']);
        }
        $allowed = array_keys($this->config->assignableRoles);
        if (!in_array($role, $allowed, true)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Invalid role. Allowed: ' . implode(', ', $allowed)]);
        }
        if ($this->userModel->where('email', $email)->first()) {
            return $this->response->setStatusCode(409)
                ->setJSON(['error' => 'Email already registered']);
        }

        $db = \Config\Database::connect();
        $insertData = [
            'name'  => $name,
            'email' => $email,
            'role'  => $role,
            'status' => 'active',
            'email_verified' => 1,
        ];
        $insertData[$db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password']
            = password_hash($password, PASSWORD_DEFAULT);

        $id = $this->userModel->insert($insertData);
        if (!$id) {
            return $this->response->setStatusCode(500)
                ->setJSON(['error' => 'Failed to create user']);
        }
        $user = $this->userModel->find($id);
        unset($user['password'], $user['password_hash']);
        return $this->response->setStatusCode(201)->setJSON(['user' => $user]);
    }

    /** PATCH /api/super-admin/users/(:num) – update user role */
    public function update($id)
    {
        $data = $this->request->getJSON(true) ?? [];
        $role = $data['role'] ?? null;
        $status = $data['status'] ?? null;

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }
        if (($user['role'] ?? '') === 'super_admin') {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'Cannot modify super admin user']);
        }

        $updates = [];
        if ($role !== null) {
            $allowed = array_keys($this->config->assignableRoles);
            if (!in_array($role, $allowed, true)) {
                return $this->response->setStatusCode(400)
                    ->setJSON(['error' => 'Invalid role']);
            }
            $updates['role'] = $role;
        }
        if ($status !== null && in_array($status, ['active', 'inactive'], true)) {
            if (\Config\Database::connect()->fieldExists('status', 'users')) {
                $updates['status'] = $status;
            }
        }
        if (empty($updates)) {
            return $this->response->setJSON(['user' => $user]);
        }
        $this->userModel->update($id, $updates);
        $user = $this->userModel->find($id);
        unset($user['password'], $user['password_hash']);
        return $this->response->setJSON(['user' => $user]);
    }
}
