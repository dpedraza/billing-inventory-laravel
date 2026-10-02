<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\ClienteController;
use App\Http\Controllers\Web\CompraController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InventarioController;
use App\Http\Controllers\Web\PerfilController;
use App\Http\Controllers\Web\ProductoController;
use App\Http\Controllers\Web\ProveedorController;
use App\Http\Controllers\Web\ReporteController;
use App\Http\Controllers\Web\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('productos', ProductoController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('proveedores', ProveedorController::class)->parameters([
        'proveedores' => 'proveedor',
    ]);

    Route::resource('compras', CompraController::class)->only([
        'index', 'create', 'store', 'show',
    ]);
    Route::get('/compras/{compra}/pdf', [CompraController::class, 'pdf'])->name('compras.pdf');
    Route::post('/compras/{compra}/confirmar', [CompraController::class, 'confirmar'])->name('compras.confirmar');
    Route::post('/compras/{compra}/anular', [CompraController::class, 'anular'])->name('compras.anular');

    Route::resource('ventas', VentaController::class)->only([
        'index', 'create', 'store', 'show',
    ]);
    Route::get('/ventas/{venta}/pdf', [VentaController::class, 'pdf'])->name('ventas.pdf');
    Route::post('/ventas/{venta}/confirmar', [VentaController::class, 'confirmar'])->name('ventas.confirmar');
    Route::post('/ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');

    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('/inventario/kardex/{producto}', [InventarioController::class, 'kardex'])->name('inventario.kardex');
    Route::get('/inventario/stock-bajo', [InventarioController::class, 'stockBajo'])->name('inventario.stock-bajo');

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/stock/{formato}', [ReporteController::class, 'exportStock'])->name('reportes.stock');
    Route::get('/reportes/ventas/{formato}', [ReporteController::class, 'exportVentas'])->name('reportes.ventas');
    Route::get('/reportes/compras/{formato}', [ReporteController::class, 'exportCompras'])->name('reportes.compras');

    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
});
