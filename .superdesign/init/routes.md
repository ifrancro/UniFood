# Routes. /login and /registro: auth views. /catalogo: catalogo.index. /carrito: carrito.index. /mis-pedidos: pedidos.index and pedidos.show. /panel/pedidos: panel.pedidos.index and show. /admin/productos: admin.productos.index and form. All extend layouts.app.
## routes/web.php
~~~
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\PanelPedidoController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Ruta raíz
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->rol === 'estudiante'
            ? redirect('/catalogo')
            : redirect('/panel/pedidos');
    }
    return redirect('/login');
});

// PÚBLICAS (invitado / guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/registro', [AuthController::class, 'formRegistro']);
    Route::post('/registro', [AuthController::class, 'registro']);

    Route::get('/password/olvide', [PasswordController::class, 'formOlvide']);
    Route::post('/password/olvide', [PasswordController::class, 'enviarEnlace']);
    Route::get('/password/reset/{token}', [PasswordController::class, 'formReset'])->name('password.reset');
    Route::post('/password/reset', [PasswordController::class, 'reset']);
});

// AUTENTICADAS
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ESTUDIANTE (auth + rol:estudiante)
    Route::middleware('rol:estudiante')->group(function () {
        Route::get('/catalogo', [CatalogoController::class, 'index']);
        Route::get('/carrito', [CarritoController::class, 'index']);
        Route::post('/carrito/agregar', [CarritoController::class, 'agregar']);
        Route::post('/carrito/actualizar', [CarritoController::class, 'actualizar']);
        Route::post('/carrito/eliminar', [CarritoController::class, 'eliminar']);
        Route::post('/pedidos', [PedidoController::class, 'store']);
        Route::get('/mis-pedidos', [PedidoController::class, 'index']);
        Route::get('/mis-pedidos/{pedido}', [PedidoController::class, 'show']);
        Route::post('/mis-pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar']);
    });

    // PERSONAL (auth + rol:empleado,admin)
    Route::middleware('rol:empleado,admin')->group(function () {
        Route::get('/panel/pedidos', [PanelPedidoController::class, 'index']);
        Route::get('/panel/pedidos/{pedido}', [PanelPedidoController::class, 'show']);
        Route::post('/panel/pedidos/{pedido}/estado', [PanelPedidoController::class, 'avanzarEstado']);
    });

    // ADMIN (auth + rol:admin)
    Route::middleware('rol:admin')->group(function () {
        Route::get('/admin/productos', [ProductoController::class, 'index']);
        Route::get('/admin/productos/crear', [ProductoController::class, 'create']);
        Route::post('/admin/productos', [ProductoController::class, 'store']);
        Route::get('/admin/productos/{producto}/editar', [ProductoController::class, 'edit']);
        Route::put('/admin/productos/{producto}', [ProductoController::class, 'update']);
        Route::post('/admin/productos/{producto}/disponibilidad', [ProductoController::class, 'toggleDisponibilidad']);
        Route::post('/admin/productos/{producto}/desactivar', [ProductoController::class, 'desactivar']);
    });
});

~~~
