<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// -----------------------------
// DEFAULT ROUTES
// -----------------------------
$routes->get('/', 'Home::index');
$routes->get('test', 'Test::index');

// -----------------------------
// WEB PAGES (login, verify, dashboard, etc.)
// -----------------------------
$routes->get('login', 'Pages::login');
$routes->get('verify', 'Pages::verify');
$routes->get('dashboard', 'Pages::dashboard');
$routes->get('admin', 'Pages::admin');
$routes->get('super-admin', 'Pages::superAdmin');
$routes->get('my-certificates', 'Pages::myCertificates');
$routes->get('reset-password', 'Pages::resetPassword');
$routes->get('user-login', 'Pages::userLogin');
$routes->get('temp-login', 'Pages::tempUserLogin');
$routes->get('admin-login', 'Pages::adminLogin');

// -----------------------------
// PUBLIC ROUTES (no login required)
// -----------------------------
$routes->group('public', ['namespace' => 'App\Controllers\SuperAdmin\Public'], function ($routes) {
    $routes->get('courses',        'PublicController::courses');
    $routes->get('courses/(:num)', 'PublicController::courseDetail/$1');
});

// -----------------------------
// AUTH ROUTES (API)
// -----------------------------
$routes->group('api/auth', ['namespace' => 'App\Controllers\Auth'], function($routes) {
    $routes->get('register', 'AuthController::register');
    $routes->post('register', 'AuthController::register');
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::login');
    $routes->get('verify-email/(:segment)', 'AuthController::verifyEmail/$1');
    $routes->post('forgot-password', 'AuthController::forgotPassword');
    $routes->post('reset-password/(:segment)', 'AuthController::resetPassword/$1');
    $routes->post('pre-register', 'AuthController::preRegister');
    $routes->post('activate', 'AuthController::activate');
    $routes->post('request-admin', 'AuthController::requestAdmin');
    $routes->post('change-password', 'AuthController::changePassword');
    $routes->get('logout', 'AuthController::logout');
    $routes->post('logout', 'AuthController::logout');
    $routes->post('refresh', 'AuthController::refresh');
});

// -----------------------------
// PAYMENTS (API) — Razorpay
// -----------------------------
$routes->group('api', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->post('payments/razorpay/create-order',   'Api\RazorpayController::createOrder');
    $routes->post('payments/razorpay/verify',          'Api\RazorpayController::verify');
    $routes->post('payments/razorpay/payment-failed',  'Api\RazorpayController::paymentFailed');
    $routes->post('quiz-attempts/log', 'Api\QuizAttemptLogController::log', ['filter' => 'jwtauth']);
    $routes->get('quiz/(:num)/questions', 'Api\QuizQuestionsApiController::questions/$1');

    // Certificates API (public verify; list/create require JWT)
    $routes->get('certificates/verify',                 'Certificates::verify');
    $routes->get('certificates/download/(:segment)',    'Certificates::downloadByNumber/$1');
    $routes->get('certificates',  'Certificates::index',  ['filter' => 'jwtauth']);
    $routes->get('certificates/debug', 'Certificates::debug', ['filter' => 'jwtauth']);
    $routes->post('certificates', 'Certificates::store',  ['filter' => 'jwtauth']);
});

// -----------------------------
// STUDENT ROUTES (JWT Protected)
// -----------------------------
$routes->group('student', ['filter' => 'jwtauth'], function($routes) {
    $routes->get('profile',                           'Common\ProfileController::index');
    $routes->post('profile/update',                   'Common\ProfileController::update');
    $routes->post('profile/change-password',          'Common\ProfileController::changePassword');
    $routes->get('courses',                           'Student\CoursesController::index');
    $routes->get('enrollments',                       'Student\EnrollmentController::index');
    $routes->get('quiz/(:num)',                        'Student\QuizController::index/$1');
    $routes->post('quiz/(:num)/submit',               'Student\QuizController::submit/$1');
    $routes->get('dashboard',                          'Student\DashboardController::index');
    $routes->get('quiz-attempts',                     'Student\QuizAttemptController::index');
    $routes->get('payments',                          'Student\PaymentController::index');
    $routes->get('certificates',                      'Student\CertificateController::index');
    $routes->get('certificates/(:num)/download',      'Student\CertificateController::download/$1');
});

// -----------------------------
// ADMIN API (for admin panel frontend)
// -----------------------------
$routes->group('api/admin', ['filter' => 'adminauth', 'namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('stats',                              'Admin::stats');
    $routes->get('my-permissions',                     'Admin::myPermissions');
    $routes->get('certificates',                       'Admin::certificates');
    $routes->get('users',                              'Admin::users');
    $routes->post('certificates/(:segment)/revoke',    'Admin::certificateRevoke/$1');
    $routes->post('certificates/(:segment)/reissue',   'Admin::certificateReissue/$1');
    $routes->get('categories',                         'Admin::categories');
    $routes->get('courses',                            'Admin::courses');
    $routes->post('courses',                           'Admin::courseCreate');
    $routes->put('courses/(:num)',                     'Admin::courseUpdate/$1');
    $routes->delete('courses/(:num)',                  'Admin::courseDelete/$1');
    $routes->get('payments',                           'Admin::payments');
    $routes->get('quizzes',                            'Admin::quizzes');
    $routes->get('quizzes/(:num)/questions',           'Admin::quizQuestions/$1');
    $routes->post('quizzes/(:num)/questions',          'Admin::questionCreate/$1');
    $routes->put('questions/(:num)',                   'Admin::questionUpdate/$1');
    $routes->delete('questions/(:num)',                'Admin::questionDelete/$1');
    $routes->get('reports/analytics',                  'Admin::reportsAnalytics');
    $routes->post('reset-user-password', '\App\Controllers\Auth\AuthController::adminResetUserPassword');
    // Student permission management (admin can manage student permissions)
    $routes->get('permissions/students',               'SuperAdmin\PermissionController::studentPermissionsList');
    $routes->get('permissions/student/(:num)',         'SuperAdmin\PermissionController::getStudentPermissions/$1');
    $routes->put('permissions/student/(:num)',         'SuperAdmin\PermissionController::updateStudentPermissions/$1');
});

// -----------------------------
// ADMIN ROUTES
// -----------------------------
$routes->group('admin', ['filter' => 'adminauth'], function($routes) {
    // Dashboard
    $routes->get('dashboard',           'Admin\Dashboard::index');
    $routes->get('dashboard/analytics', 'Admin\Dashboard::analytics');

    // Categories
    $routes->get('categories',             'Admin\CategoryController::index');
    $routes->get('categories/(:num)/edit', 'Admin\CategoryController::edit/$1');
    $routes->post('categories',            'Admin\CategoryController::store');
    $routes->put('categories/(:num)',      'Admin\CategoryController::update/$1');
    $routes->delete('categories/(:num)',   'Admin\CategoryController::delete/$1');

    // Courses
    $routes->get('courses',             'Admin\CourseController::index');
    $routes->get('courses/(:num)/edit', 'Admin\CourseController::edit/$1');
    $routes->post('courses',            'Admin\CourseController::store');
    $routes->put('courses/(:num)',       'Admin\CourseController::update/$1');
    $routes->delete('courses/(:num)',    'Admin\CourseController::delete/$1');

    // Quizzes
    $routes->get('quizzes',             'Admin\QuizController::index');
    $routes->get('quizzes/(:num)/edit', 'Admin\QuizController::edit/$1');
    $routes->post('quizzes',            'Admin\QuizController::store');
    $routes->put('quizzes/(:num)',       'Admin\QuizController::update/$1');
    $routes->delete('quizzes/(:num)',    'Admin\QuizController::delete/$1');

    // Questions
    $routes->get('courses/(:num)/questions',  'Admin\QuestionController::index/$1');
    $routes->get('questions/(:num)/edit',     'Admin\QuestionController::edit/$1');
    $routes->post('courses/(:num)/questions', 'Admin\QuestionController::store/$1');
    $routes->put('questions/(:num)',           'Admin\QuestionController::update/$1');
    $routes->delete('questions/(:num)',        'Admin\QuestionController::delete/$1');

    // Certificates
    $routes->get('certificates', 'Admin\CertificateController::index');
});

// -----------------------------
// SUPER ADMIN ROUTES (super_admin role only)
// -----------------------------
$routes->group('api/super-admin', [
    'filter'    => 'superadminauth',
    'namespace' => 'App\Controllers\SuperAdmin',
], function ($routes) {
    $routes->get('stats',        'Dashboard::stats');
    $routes->get('users',        'UsersController::index');
    $routes->post('users',       'UsersController::create');
    $routes->patch('users/(:num)', 'UsersController::update/$1');
    $routes->get('roles',                              'RolesController::index');
    $routes->put('roles/(:segment)',                   'RolesController::update/$1');
    $routes->get('role-templates',                     'RolesController::templates');
    $routes->post('roles/(:segment)/apply-template',   'RolesController::applyTemplate/$1');
    $routes->get('announcements',         'AnnouncementsController::index');
    $routes->post('announcements',        'AnnouncementsController::create');
    $routes->put('announcements/(:num)',  'AnnouncementsController::update/$1');
    $routes->delete('announcements/(:num)', 'AnnouncementsController::delete/$1');

    // Permission management (superadmin manages admin permissions)
    $routes->get('permissions',                'PermissionController::index');
    $routes->get('permissions/admins',         'PermissionController::adminPermissionsList');
    $routes->get('permissions/user/(:num)',    'PermissionController::getUserPermissions/$1');
    $routes->put('permissions/user/(:num)',    'PermissionController::updateUserPermissions/$1');
    $routes->get('permissions/students',       'PermissionController::studentPermissionsList');
    $routes->get('permissions/student/(:num)', 'PermissionController::getStudentPermissions/$1');
    $routes->put('permissions/student/(:num)', 'PermissionController::updateStudentPermissions/$1');
});

// Public: active announcements for dashboard banner
$routes->get('api/announcements/active', 'Api\AnnouncementsController::active', ['namespace' => 'App\Controllers']);