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

// =====================================================================
//  RUTAS PROTEGIDAS (requieren autenticación)
// =====================================================================
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    // -----------------------------------------------------------------
    //  DASHBOARD
    // -----------------------------------------------------------------
    $routes->get('/dashboard', 'DashboardController::index');

    // -----------------------------------------------------------------
    //  PRODUCTOS
    // -----------------------------------------------------------------
    $routes->group('productos', static function ($routes) {
        $routes->get('/', 'ProductoController::index');
        $routes->post('listar', 'ProductoController::listar');           // AJAX DataTables
        $routes->post('guardar', 'ProductoController::guardar');         // AJAX
        $routes->post('editar/(:num)', 'ProductoController::editar/$1'); // AJAX
        $routes->post('actualizar', 'ProductoController::actualizar');   // AJAX
        $routes->post('eliminar', 'ProductoController::eliminar');       // AJAX
    });

    // -----------------------------------------------------------------
    //  VENTAS
    // -----------------------------------------------------------------
    $routes->group('ventas', static function ($routes) {
        $routes->get('/', 'VentaController::index');               // Vista principal
        $routes->post('listar', 'VentaController::listar');        // ✅ GET → POST (DataTables envía POST)
        $routes->post('guardar', 'VentaController::guardar');      // AJAX guardar venta
        $routes->post('detalle/(:num)', 'VentaController::detalle/$1'); // ✅ GET → POST (AJAX envía POST)
        // $routes->get('nueva', 'VentaController::nueva');        // ✘ ELIMINADA (se usa modal)
    });

    // -----------------------------------------------------------------
    //  EGRESOS
    // -----------------------------------------------------------------
    $routes->group('egresos', static function ($routes) {
        $routes->get('/', 'EgresoController::index');
        $routes->post('listar', 'EgresoController::listar');       // ✅ GET → POST (consistencia)
        $routes->post('guardar', 'EgresoController::guardar');     // AJAX
    });

    // -----------------------------------------------------------------
    //  REPORTES
    // -----------------------------------------------------------------
    $routes->group('reportes', static function ($routes) {
        $routes->get('/', 'ReporteController::index');
        $routes->post('por-fecha', 'ReporteController::porFecha');       // AJAX
        $routes->post('entre-fechas', 'ReporteController::entreFechas'); // AJAX
    });

    // -----------------------------------------------------------------
    //  PERFIL DE USUARIO
    // -----------------------------------------------------------------
    $routes->group('perfil', static function ($routes) {
        $routes->get('/', 'UsuarioController::perfil');
        $routes->post('datos', 'UsuarioController::datos');        // ✅ GET → POST (si usa isAJAX)
        $routes->post('actualizar', 'UsuarioController::actualizar');   // AJAX
    });
});