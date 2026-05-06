<?php

namespace Config;

/**
 * Permission slugs that super_admin can assign to roles (admin, hr, etc.).
 * Super_admin has full access and is not stored in role_permissions.
 */
class Permissions
{
    /** Permission slug => human label for UI */
    public array $list = [
        'access_admin_panel'   => 'Access admin panel',
        'users_view'            => 'View users',
        'users_manage'          => 'Create / edit / delete users',
        'roles_manage'          => 'Manage role permissions (admin/hr access)',
        'categories_manage'     => 'Manage categories',
        'courses_manage'        => 'Manage courses',
        'quizzes_manage'        => 'Manage quizzes',
        'questions_manage'      => 'Manage quiz questions',
        'certificates_view'     => 'View certificates',
        'certificates_manage'   => 'Manage certificates',
        'reports_view'          => 'View reports & analytics',
        'enrollments_view'      => 'View enrollments',
        'payments_view'         => 'View payments',
    ];

    /** Roles that can be assigned to users (excluding super_admin – set manually in DB) */
    public array $assignableRoles = [
        'admin'   => 'Admin',
        'hr'      => 'HR / Non-technical',
        'student' => 'Student',
        'instructor' => 'Instructor',
    ];
}
