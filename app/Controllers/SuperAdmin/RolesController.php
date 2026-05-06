<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Libraries\PermissionService;
use Config\RoleTemplates;

class RolesController extends BaseController
{
    protected $permService;

    public function __construct()
    {
        $this->permService = new PermissionService();
    }

    /** GET /api/super-admin/role-templates – list permission presets */
    public function templates()
    {
        $list = [];
        foreach (RoleTemplates::$templates as $slug => $def) {
            $list[$slug] = $def['label'] ?? $slug;
        }
        return $this->response->setJSON(['templates' => $list]);
    }

    /** POST /api/super-admin/roles/(:segment)/apply-template – set role permissions from a template */
    public function applyTemplate($roleSlug)
    {
        if ($roleSlug === 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Cannot modify super_admin']);
        }
        $roles = $this->permService->getAssignableRoles();
        if (!isset($roles[$roleSlug])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid role']);
        }
        $data = $this->request->getJSON(true) ?? [];
        $templateSlug = $data['template'] ?? '';
        if (!isset(RoleTemplates::$templates[$templateSlug])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid template']);
        }
        $perms = RoleTemplates::$templates[$templateSlug]['permissions'] ?? [];
        $allSlugs = array_keys($this->permService->getAllPermissionList());
        $perms = array_values(array_intersect($perms, $allSlugs));
        if (!$this->permService->setPermissionsForRole($roleSlug, $perms)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to save role permissions']);
        }
        return $this->response->setJSON([
            'role'        => $roleSlug,
            'template'    => $templateSlug,
            'permissions' => $this->permService->getPermissionsForRole($roleSlug),
        ]);
    }

    /** GET /api/super-admin/roles – list roles and their permissions */
    public function index()
    {
        $roles = $this->permService->getAssignableRoles();
        $list  = $this->permService->getAllPermissionList();
        $matrix = [];
        foreach (array_keys($roles) as $roleSlug) {
            $matrix[$roleSlug] = $this->permService->getPermissionsForRole($roleSlug);
        }
        return $this->response->setJSON([
            'roles'       => $roles,
            'permissions' => $list,
            'matrix'      => $matrix,
        ]);
    }

    /** PUT /api/super-admin/roles/(:segment) – set permissions for a role */
    public function update($roleSlug)
    {
        if ($roleSlug === 'super_admin') {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'Cannot modify super_admin permissions']);
        }
        $roles = $this->permService->getAssignableRoles();
        if (!isset($roles[$roleSlug])) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Invalid role']);
        }
        $data = $this->request->getJSON(true) ?? [];
        $permissions = $data['permissions'] ?? [];
        if (!is_array($permissions)) {
            $permissions = [];
        }
        $list = array_keys($this->permService->getAllPermissionList());
        $permissions = array_intersect($permissions, $list);
        if (!$this->permService->setPermissionsForRole($roleSlug, array_values($permissions))) {
            return $this->response->setStatusCode(500)
                ->setJSON(['error' => 'Failed to save role permissions']);
        }
        return $this->response->setJSON([
            'role'        => $roleSlug,
            'permissions' => $this->permService->getPermissionsForRole($roleSlug),
        ]);
    }
}
