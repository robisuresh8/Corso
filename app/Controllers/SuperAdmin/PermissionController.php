<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\PermissionModel;
use App\Models\UserPermissionModel;
use App\Models\UserModel;

class PermissionController extends BaseController
{
    protected PermissionModel     $permissionModel;
    protected UserPermissionModel $userPermissionModel;
    protected UserModel           $userModel;

    public function __construct()
    {
        $this->permissionModel     = new PermissionModel();
        $this->userPermissionModel = new UserPermissionModel();
        $this->userModel           = new UserModel();
    }

    // =========================================================
    // GET /api/super-admin/permissions
    // List all permissions grouped by type
    // =========================================================
    public function index()
    {
        try {
            $adminPermissions   = $this->permissionModel->where('type', 'admin')->findAll();
            $studentPermissions = $this->permissionModel->where('type', 'student')->findAll();

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => [
                    'admin_permissions'   => $adminPermissions,
                    'student_permissions' => $studentPermissions,
                ],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Permission index error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    // =========================================================
    // GET /api/super-admin/permissions/admins
    // List all admins with their permission summary
    // =========================================================
    public function adminPermissionsList()
    {
        try {
            $db     = \Config\Database::connect();
            $admins = $this->userModel
                ->select('id, name, email, role, status')
                ->whereIn('role', ['admin', 'superadmin'])
                ->orderBy('id', 'DESC')
                ->findAll();

            $totalAdminPermissions = $this->permissionModel->where('type', 'admin')->countAllResults();

            $result = [];
            foreach ($admins as $admin) {
                $count = $db->table('user_permissions')
                    ->where('user_id', $admin['id'])
                    ->countAllResults();

                $result[] = [
                    'id'               => $admin['id'],
                    'name'             => $admin['name'],
                    'email'            => $admin['email'],
                    'role'             => $admin['role'],
                    'status'           => $admin['status'],
                    'permission_count' => $count === 0 ? $totalAdminPermissions . ' (default all)' : $count,
                    'is_restricted'    => $count > 0,
                ];
            }

            return $this->response->setJSON(['status' => 'success', 'data' => $result]);
        } catch (\Exception $e) {
            log_message('error', 'Admin permissions list error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    // =========================================================
    // GET /api/super-admin/permissions/user/{user_id}
    // Get all permissions for a specific admin user
    // =========================================================
    public function getUserPermissions($userId)
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'User not found']);
            }

            if (!in_array($user['role'], ['admin', 'superadmin'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Permissions can only be managed for admin users',
                ]);
            }

            $db = \Config\Database::connect();

            $assigned = $db->table('user_permissions up')
                ->select('p.id, p.name, p.slug, p.description')
                ->join('permissions p', 'p.id = up.permission_id')
                ->where('up.user_id', $userId)
                ->where('p.type', 'admin')
                ->get()
                ->getResultArray();

            $assignedSlugs       = array_column($assigned, 'slug');
            $allAdminPermissions = $this->permissionModel->where('type', 'admin')->findAll();

            $result = [];
            foreach ($allAdminPermissions as $permission) {
                $result[] = [
                    'id'          => $permission['id'],
                    'name'        => $permission['name'],
                    'slug'        => $permission['slug'],
                    'description' => $permission['description'],
                    'assigned'    => empty($assignedSlugs) ? true : in_array($permission['slug'], $assignedSlugs),
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'user'   => ['id' => $user['id'], 'name' => $user['name'], 'role' => $user['role']],
                'data'   => $result,
            ]);
        } catch (\Exception $e) {
            log_message('error', "Get user permissions error | user_id:{$userId} " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    // =========================================================
    // PUT /api/super-admin/permissions/user/{user_id}
    // Replace permissions for a specific admin user
    // Body: { "permission_ids": [1, 2, 3] }
    // =========================================================
    public function updateUserPermissions($userId)
    {
        try {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'User not found']);
            }

            if ($user['role'] === 'superadmin') {
                return $this->response->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'message' => 'Cannot change superadmin permissions',
                ]);
            }

            if ($user['role'] !== 'admin') {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Permissions can only be assigned to admin users',
                ]);
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['permission_ids']) || !is_array($data['permission_ids'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'permission_ids array is required',
                ]);
            }

            $db  = \Config\Database::connect();
            $now = date('Y-m-d H:i:s');

            // Delete existing admin permissions for this user
            $adminPermissionIds = $this->permissionModel->where('type', 'admin')->findColumn('id');
            if ($adminPermissionIds) {
                $db->table('user_permissions')
                    ->where('user_id', $userId)
                    ->whereIn('permission_id', $adminPermissionIds)
                    ->delete();
            }

            // Insert new permissions
            if (!empty($data['permission_ids'])) {
                $insert = [];
                foreach ($data['permission_ids'] as $permissionId) {
                    $permission = $this->permissionModel->find($permissionId);
                    if ($permission && $permission['type'] === 'admin') {
                        $insert[] = [
                            'user_id'       => (int) $userId,
                            'permission_id' => (int) $permissionId,
                            'created_at'    => $now,
                        ];
                    }
                }
                if (!empty($insert)) {
                    $db->table('user_permissions')->insertBatch($insert);
                }
            }

            log_message('info', "Admin permissions updated | user_id:{$userId} name:{$user['name']}");

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Permissions updated for ' . $user['name'],
            ]);
        } catch (\Exception $e) {
            log_message('error', "Update user permissions error | user_id:{$userId} " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    // =========================================================
    // GET /api/super-admin/permissions/students  (called by admin too)
    // List all students with their permission summary
    // =========================================================
    public function studentPermissionsList()
    {
        try {
            $db = \Config\Database::connect();

            $students = $this->userModel
                ->select('id, name, email, status')
                ->where('role', 'student')
                ->orderBy('id', 'DESC')
                ->findAll();

            $totalStudentPermissions = $this->permissionModel->where('type', 'student')->countAllResults();

            $result = [];
            foreach ($students as $student) {
                $count = $db->table('user_permissions up')
                    ->join('permissions p', 'p.id = up.permission_id')
                    ->where('up.user_id', $student['id'])
                    ->where('p.type', 'student')
                    ->countAllResults();

                $result[] = [
                    'id'               => $student['id'],
                    'name'             => $student['name'],
                    'email'            => $student['email'],
                    'status'           => $student['status'],
                    'permission_count' => $count === 0 ? $totalStudentPermissions . ' (default all)' : $count,
                    'is_restricted'    => $count > 0,
                ];
            }

            return $this->response->setJSON(['status' => 'success', 'data' => $result]);
        } catch (\Exception $e) {
            log_message('error', 'Student permissions list error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    // =========================================================
    // GET /api/super-admin/permissions/student/{student_id}
    // =========================================================
    public function getStudentPermissions($studentId)
    {
        try {
            $user = $this->userModel->find($studentId);

            if (!$user || $user['role'] !== 'student') {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Student not found']);
            }

            $db = \Config\Database::connect();

            $assigned = $db->table('user_permissions up')
                ->select('p.slug')
                ->join('permissions p', 'p.id = up.permission_id')
                ->where('up.user_id', $studentId)
                ->where('p.type', 'student')
                ->get()
                ->getResultArray();

            $assignedSlugs         = array_column($assigned, 'slug');
            $allStudentPermissions = $this->permissionModel->where('type', 'student')->findAll();

            $result = [];
            foreach ($allStudentPermissions as $permission) {
                $result[] = [
                    'id'          => $permission['id'],
                    'name'        => $permission['name'],
                    'slug'        => $permission['slug'],
                    'description' => $permission['description'],
                    'assigned'    => empty($assignedSlugs) ? true : in_array($permission['slug'], $assignedSlugs),
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'user'   => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']],
                'data'   => $result,
            ]);
        } catch (\Exception $e) {
            log_message('error', "Get student permissions error | student_id:{$studentId} " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    // =========================================================
    // PUT /api/super-admin/permissions/student/{student_id}
    // Body: { "permission_ids": [1, 2, 3] }
    // =========================================================
    public function updateStudentPermissions($studentId)
    {
        try {
            $user = $this->userModel->find($studentId);

            if (!$user || $user['role'] !== 'student') {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Student not found']);
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['permission_ids']) || !is_array($data['permission_ids'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'permission_ids array is required',
                ]);
            }

            $db  = \Config\Database::connect();
            $now = date('Y-m-d H:i:s');

            $studentPermissionIds = $this->permissionModel->where('type', 'student')->findColumn('id');
            if ($studentPermissionIds) {
                $db->table('user_permissions')
                    ->where('user_id', $studentId)
                    ->whereIn('permission_id', $studentPermissionIds)
                    ->delete();
            }

            if (!empty($data['permission_ids'])) {
                $insert = [];
                foreach ($data['permission_ids'] as $permissionId) {
                    $permission = $this->permissionModel->find($permissionId);
                    if ($permission && $permission['type'] === 'student') {
                        $insert[] = [
                            'user_id'       => (int) $studentId,
                            'permission_id' => (int) $permissionId,
                            'created_at'    => $now,
                        ];
                    }
                }
                if (!empty($insert)) {
                    $db->table('user_permissions')->insertBatch($insert);
                }
            }

            log_message('info', "Student permissions updated | student_id:{$studentId} name:{$user['name']}");

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Permissions updated for ' . $user['name'],
            ]);
        } catch (\Exception $e) {
            log_message('error', "Update student permissions error | student_id:{$studentId} " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Something went wrong']);
        }
    }
}