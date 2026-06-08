<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================================
//  RUTAS PÚBLICAS (sin autenticación)
// =====================================================================
$routes->get('/', 'AuthController::index');
$routes->get('/login', 'AuthController::index');
$routes->post('/login/authenticate', 'AuthController::authenticate');
$routes->get('/logout', 'AuthController::logout');

// DEBUG TEMPORAL - PÚBLICO
$routes->get('debug-db', function () {
    return service('response')->setJSON([
        'host' => env('database.default.hostname'),
        'db'   => env('database.default.database'),
        'user' => env('database.default.username'),
        'port' => env('database.default.port'),
    ]);
});

// =====================================================================
//  RUTAS PROTEGIDAS (requieren autenticación)
// =====================================================================
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    $routes->get('/dashboard', 'DashboardController::index');

    $routes->group('productos', static function ($routes) {
        $routes->get('/', 'ProductoController::index');
        $routes->post('listar', 'ProductoController::listar');
        $routes->post('guardar', 'ProductoController::guardar');
        $routes->post('editar/(:num)', 'ProductoController::editar/$1');
        $routes->post('actualizar', 'ProductoController::actualizar');
        $routes->post('eliminar', 'ProductoController::eliminar');
    });

    $routes->group('ventas', static function ($routes) {
        $routes->get('/', 'VentaController::index');
        $routes->post('listar', 'VentaController::listar');
        $routes->post('guardar', 'VentaController::guardar');
        $routes->post('detalle/(:num)', 'VentaController::detalle/$1');
    });

    $routes->group('egresos', static function ($routes) {
        $routes->get('/', 'EgresoController::index');
        $routes->post('listar', 'EgresoController::listar');
        $routes->post('guardar', 'EgresoController::guardar');
        $routes->post('editar/(:num)', 'EgresoController::editar/$1');
        $routes->post('actualizar', 'EgresoController::actualizar');
        $routes->post('eliminar', 'EgresoController::eliminar');
    });

    $routes->group('reportes', static function ($routes) {
        $routes->get('/', 'ReporteController::index');
        $routes->get('hoy', 'ReporteController::hoy');
        $routes->get('porFecha', 'ReporteController::porFecha');
        $routes->get('porRango', 'ReporteController::porRango');
        $routes->get('semanaActual', 'ReporteController::semanaActual');
        $routes->get('mesActual', 'ReporteController::mesActual');
    });

    $routes->get('perfil', 'PerfilController::index');
});