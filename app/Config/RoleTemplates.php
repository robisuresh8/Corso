<?php

namespace Config;

/**
 * Role permission presets for Super Admin "Apply template" feature.
 * Key = template slug, value = [ 'label' => string, 'permissions' => string[] ]
 */
class RoleTemplates
{
    public static array $templates = [
        'hr_view_only' => [
            'label' => 'HR view only',
            'permissions' => ['access_admin_panel', 'users_view', 'certificates_view', 'reports_view'],
        ],
        'course_manager' => [
            'label' => 'Course manager',
            'permissions' => ['access_admin_panel', 'reports_view', 'categories_manage', 'courses_manage', 'quizzes_manage', 'questions_manage', 'enrollments_view'],
        ],
        'support' => [
            'label' => 'Support (view + verify)',
            'permissions' => ['access_admin_panel', 'users_view', 'certificates_view', 'certificates_manage', 'reports_view'],
        ],
        'admin_full' => [
            'label' => 'Admin full',
            'permissions' => ['access_admin_panel', 'reports_view', 'users_view', 'users_manage', 'certificates_view', 'certificates_manage', 'courses_manage', 'quizzes_manage', 'questions_manage', 'enrollments_view', 'payments_view'],
        ],
    ];
}
