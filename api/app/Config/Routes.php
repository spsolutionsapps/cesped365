<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API Routes
$routes->group('api', ['filter' => 'corscustom'], function($routes) {
    // Rutas públicas (sin autenticación)
    $routes->post('login', 'Api\AuthController::login');
    
    // Rutas protegidas (requieren autenticación)
    $routes->group('', ['filter' => 'auth'], function($routes) {
        // Auth
        $routes->get('me', 'Api\AuthController::me');
        $routes->post('logout', 'Api\AuthController::logout');
        
        // Dashboard (accesible para admin y cliente)
        $routes->get('dashboard', 'Api\DashboardController::index');
        
        // Reportes (accesible para admin y cliente)
        $routes->get('reportes', 'Api\ReportesController::index');
        $routes->get('reportes/(:num)', 'Api\ReportesController::show/$1');
        
        // Historial (accesible para admin y cliente)
        $routes->get('historial', 'Api\HistorialController::index');
        
        // Planes de suscripción (público para clientes)
        $routes->get('subscriptions/plans', 'Api\SubscriptionsController::plans');
        $routes->get('subscriptions/plans/(:num)', 'Api\SubscriptionsController::showPlan/$1');
        $routes->get('subscriptions/my-subscription', 'Api\SubscriptionsController::mySubscription');
        
        // Rutas solo para admin
        $routes->group('', ['filter' => 'role:admin'], function($routes) {
            // Clientes - CRUD completo
            $routes->get('clientes', 'Api\ClientesController::index');
            $routes->get('clientes/(:num)', 'Api\ClientesController::show/$1');
            $routes->post('clientes', 'Api\ClientesController::create');
            $routes->put('clientes/(:num)', 'Api\ClientesController::update/$1');
            $routes->delete('clientes/(:num)', 'Api\ClientesController::delete/$1');
            $routes->get('clientes/(:num)/historial', 'Api\ClientesController::historial/$1');
            
            // Reportes - Crear y subir imágenes
            $routes->post('reportes', 'Api\ReportesController::create');
            $routes->post('reportes/(:num)/imagen', 'Api\ReportesController::uploadImage/$1');
            
            // Suscripciones - Gestión completa
            $routes->get('subscriptions', 'Api\SubscriptionsController::index');
            $routes->get('subscriptions/(:num)', 'Api\SubscriptionsController::show/$1');
            $routes->post('subscriptions', 'Api\SubscriptionsController::create');
            $routes->put('subscriptions/(:num)', 'Api\SubscriptionsController::update/$1');
            $routes->post('subscriptions/(:num)/cancel', 'Api\SubscriptionsController::cancel/$1');
            $routes->post('subscriptions/(:num)/pause', 'Api\SubscriptionsController::pause/$1');
            $routes->post('subscriptions/(:num)/reactivate', 'Api\SubscriptionsController::reactivate/$1');
            
            // Planes de suscripción - Admin
            $routes->post('subscriptions/plans', 'Api\SubscriptionsController::createPlan');
        });
    });
});
