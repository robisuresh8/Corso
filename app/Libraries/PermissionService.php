<?php

namespace App\Libraries;

use Config\Permissions;

/**
 * Check if a role has a given permission.
 * Super_admin always has full access. Others are checked against role_permissions table.
 */
class PermissionService
{
    protected $db;
    protected $config;

    public function __construct()
    {
        $this->db     = \Config\Database::connect();
        $this->config = new Permissions();
    }

    /** Normalize role slug for DB lookup (role_permissions uses lowercase) */
    private function normalizeRoleSlug(string $roleSlug): string
    {
        return strtolower(trim($roleSlug));
    }

    /**
     * Built-in fallback permissions used when role_permissions is unavailable
     * or has no entries for a role yet.
     */
    private function getFallbackPermissionsForRole(string $roleSlug): array
    {
        $roleSlug = $this->normalizeRoleSlug($roleSlug);

        $map = [
            // Sensible defaults — align with Config\RoleTemplates admin_full.
            'admin' => [
                'access_admin_panel',
                'reports_view',
                'users_view',
                'users_manage',
                'certificates_view',
                'certificates_manage',
                'categories_manage',
                'courses_manage',
                'quizzes_manage',
                'questions_manage',
                'enrollments_view',
                'payments_view',
            ],
            'hr' => [
                'access_admin_panel',
                'users_view',
                'certificates_view',
                'reports_view',
            ],
        ];

        return $map[$roleSlug] ?? [];
    }

    /** Ensure role_permissions table exists before writes */
    private function ensureRolePermissionsTable(): bool
    {
        if ($this->db->tableExists('role_permissions')) {
            return true;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `role_permissions` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `role_slug` VARCHAR(32) NOT NULL,
            `permission_slug` VARCHAR(64) NOT NULL,
            `created_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `role_perm` (`role_slug`, `permission_slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        try {
            $this->db->query($sql);
        } catch (\Throwable $e) {
            return false;
        }

        return $this->db->tableExists('role_permissions');
    }

    /** Check if role has permission (super_admin always true) */
    public function can(string $roleSlug, string $permissionSlug): bool
    {
        $roleSlug = $this->normalizeRoleSlug($roleSlug);
        if ($roleSlug === 'super_admin') {
            return true;
        }
        if (!$this->db->tableExists('role_permissions')) {
            // Development fallback if the table isn't created yet.
            return in_array($permissionSlug, $this->getFallbackPermissionsForRole($roleSlug), true);
        }
        $row = $this->db->table('role_permissions')
            ->where('role_slug', $roleSlug)
            ->where('permission_slug', $permissionSlug)
            ->get()
            ->getRow();
        return $row !== null;
    }

    /** Get all permission slugs for a role */
    public function getPermissionsForRole(string $roleSlug): array
    {
        $roleSlug = $this->normalizeRoleSlug($roleSlug);
        if ($roleSlug === 'super_admin') {
            return array_keys($this->config->list);
        }
        if (!$this->db->tableExists('role_permissions')) {
            return $this->getFallbackPermissionsForRole($roleSlug);
        }
        $rows = $this->db->table('role_permissions')
            ->where('role_slug', $roleSlug)
            ->get()
            ->getResultArray();
        $perms = array_column($rows, 'permission_slug');
        // Table exists but no rows (never seeded / cleared) — avoid empty UI for admin/hr.
        if ($perms === [] && in_array($roleSlug, ['admin', 'hr'], true)) {
            return $this->getFallbackPermissionsForRole($roleSlug);
        }

        return $perms;
    }

    /** Set permissions for a role (replaces existing) */
    public function setPermissionsForRole(string $roleSlug, array $permissionSlugs): bool
    {
        $roleSlug = $this->normalizeRoleSlug($roleSlug);
        if ($roleSlug === 'super_admin') {
            return false; // cannot modify super_admin permissions
        }
        if (!$this->ensureRolePermissionsTable()) {
            return false;
        }
        $this->db->table('role_permissions')->where('role_slug', $roleSlug)->delete();
        foreach ($permissionSlugs as $slug) {
            $this->db->table('role_permissions')->insert([
                'role_slug'       => $roleSlug,
                'permission_slug'  => $slug,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }
        return true;
    }

    public function getAllPermissionList(): array
    {
        return $this->config->list;
    }

    public function getAssignableRoles(): array
    {
        return $this->config->assignableRoles;
    }
}
