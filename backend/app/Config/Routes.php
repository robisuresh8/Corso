<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->options('(:any)', 'CorsController::options');

$routes->group('api/v1', static function ($routes) {
    $routes->get('ping', static function () {
        return 'pong';
    });
    $routes->get('health', 'Health::index');
    $routes->get('health/cfg', 'Health::cfg');
    $routes->get('health/db', 'Health::db');

    // Public auth routes (no JWT required)
    $routes->post('auth/register', 'AuthController::register');
    $routes->post('auth/login', 'AuthController::login');
    
    // Public verify route (no JWT required)
    $routes->get('certificates/verify', 'Certificates::verify');
    
    // Protected routes (require JWT authentication)
    $routes->group('', ['filter' => 'jwtauth'], static function ($routes) {
        $routes->get('certificates', 'Certificates::index');
        $routes->post('certificates', 'Certificates::store');
    });
});