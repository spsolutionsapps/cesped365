<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API Routes
// CodeIgniter está montado en /api/public/, así que las URIs llegan SIN el prefijo /api/ (ej: /me, /login)
// El frontend llama a /api/me, /api/login, etc.; Apache reescribe a index.php/me, index.php/login
$routes->group('', ['filter' => 'corscustom'], function($routes) {
    // Manejar OPTIONS para todas las rutas (preflight CORS)
    $routes->options('(:any)', function() {
        return service('response')->setStatusCode(200);
    });
    
    // Rutas públicas (sin autenticación)
    $routes->post('login', 'Api\AuthController::login');
    $routes->post('registro', 'Api\ClientesController::create'); // Registro público
    $routes->match(['get', 'post'], 'payment/webhook', 'Api\PaymentController::webhook'); // Webhook Mercado Pago (GET para test del panel, POST para notificaciones reales)
    $routes->get('payment/preapproval-return', 'Api\PaymentController::preapprovalReturn'); // Retorno público Preapproval
    
    // Rutas protegidas (requieren autenticación)
    $routes->group('', ['filter' => 'auth'], function($routes) {
        // Auth
        $routes->get('me', 'Api\AuthController::me');
        $routes->put('me', 'Api\AuthController::updateProfile');
        $routes->put('me/password', 'Api\AuthController::updatePassword');
        $routes->post('logout', 'Api\AuthController::logout');

        // Pagos
        $routes->post('payment/create-preference', 'Api\PaymentController::createPreference');
        $routes->post('payment/create-subscription', 'Api\PaymentController::createSubscription');
        $routes->post('payment/cancel-subscription', 'Api\PaymentController::cancelSubscription');
        
        // Dashboard (accesible para admin y cliente)
        $routes->get('dashboard', 'Api\DashboardController::index');
        
        // Reportes (accesible para admin y cliente)
        $routes->get('reportes', 'Api\ReportesController::index');
        $routes->get('reportes/(:num)', 'Api\ReportesController::show/$1');
        
        // Historial (accesible para admin y cliente)
        $routes->get('historial', 'Api\HistorialController::index');
        
        // Jardines (accesible para admin y cliente)
        $routes->get('jardines', 'Api\JardinesController::index');
        
        // Visitas programadas (accesible para admin y cliente)
        $routes->get('scheduled-visits', 'Api\ScheduledVisitsController::index');
        $routes->get('scheduled-visits/(:num)', 'Api\ScheduledVisitsController::show/$1');
        
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
            
            // Reportes - Crear, actualizar, subir imágenes y eliminar
            $routes->post('reportes', 'Api\ReportesController::create');
            $routes->put('reportes/(:num)', 'Api\ReportesController::update/$1');
            $routes->post('reportes/(:num)/imagen', 'Api\ReportesController::uploadImage/$1');
            $routes->delete('reportes/(:num)', 'Api\ReportesController::delete/$1');
            
            // Visitas programadas - CRUD completo (solo admin)
            $routes->post('scheduled-visits', 'Api\ScheduledVisitsController::create');
            $routes->put('scheduled-visits/(:num)', 'Api\ScheduledVisitsController::update/$1');
            $routes->delete('scheduled-visits/(:num)', 'Api\ScheduledVisitsController::delete/$1');
            
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