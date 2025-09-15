<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\CapturistaController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\TarjetaSiValeController;
use App\Http\Controllers\VerificacionController;
use App\Http\Controllers\CargaCombustibleController;
use App\Http\Controllers\TanqueController;
use App\Http\Controllers\VehiculoFotoController;
use App\Http\Controllers\OperadorFotoController;
// === NUEVOS ===
use App\Http\Controllers\TarjetaComodinController;
use App\Http\Controllers\ComodinGastoController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página de bienvenida
Route::view('/', 'welcome');

// Dashboard general (usuarios autenticados y verificados)
Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Ejemplo solo para rol operador
    Route::view('/ejemploRol', 'ejemploRol')
        ->middleware(['verified', 'role:operador'])
        ->name('ejemploRol');

    // Dashboard exclusivo para administradores
    Route::view('/dashboard-admin', 'dashboards.admin')
        ->middleware('role:administrador')
        ->name('dashboard.admin');

    // Perfil de usuario (cualquier autenticado)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gestión solo para administradores
    Route::middleware('role:administrador')->group(function () {
        Route::resource('capturistas', CapturistaController::class);
    });

    // Gestión para administradores y capturistas
    Route::middleware('role:administrador|capturista')->group(function () {

        // ---- CRUD con resources ----

        // Operadores: forzamos el nombre del parámetro a {operador} (arregla el {operadore})
        Route::resource('operadores', OperadorController::class)
            ->parameters(['operadores' => 'operador']);

        // Vehículos y Tarjetas SiVale (parámetros por defecto: {vehiculo}, {tarjeta})
        Route::resources([
            'vehiculos' => VehiculoController::class,
            'tarjetas'  => TarjetaSiValeController::class,
        ]);

        // === Tarjeta Comodín (nuevo) ===
        Route::resource('tarjetas-comodin', TarjetaComodinController::class);

        // Listado global de gastos (filtrable por ?tarjeta=ID)
        Route::get('comodin-gastos', [ComodinGastoController::class, 'index'])
            ->name('comodin-gastos.index');

        // Gastos de Tarjeta Comodín (create/store anidados; edit/update/destroy shallow)
        Route::scopeBindings()->group(function () {
            Route::resource('tarjetas-comodin.gastos', ComodinGastoController::class)
                ->only(['create','store','edit','update','destroy'])
                ->shallow();
        });
        // Con shallow(), las rutas y names quedan así:
        // - create/store:  tarjetas-comodin.gastos.create / tarjetas-comodin.gastos.store
        // - edit/update/destroy (shallow): gastos.edit / gastos.update / gastos.destroy

        // Verificaciones: forzamos {verificacion}
        Route::resource('verificaciones', VerificacionController::class)
            ->parameters(['verificaciones' => 'verificacion']);

        // ---- Nested resource: tanques de vehículo ----
        Route::scopeBindings()->group(function () {
            Route::resource('vehiculos.tanques', TanqueController::class)->except(['show']);
        });

        // ---- Cargas de combustible ----
        Route::resource('cargas', CargaCombustibleController::class)
            ->parameters(['cargas' => 'carga']);

        // ---- Fotos de vehículos ----
        Route::scopeBindings()->group(function () {
            // Anidadas al vehículo
            Route::get   ('/vehiculos/{vehiculo}/fotos',        [VehiculoFotoController::class, 'index'])->name('vehiculos.fotos.index');
            Route::post  ('/vehiculos/{vehiculo}/fotos',        [VehiculoFotoController::class, 'store'])->name('vehiculos.fotos.store');
            Route::delete('/vehiculos/{vehiculo}/fotos/{foto}', [VehiculoFotoController::class, 'destroy'])->name('vehiculos.fotos.destroy');
        });
        // Mostrar imagen privada por ID (URL corta, no anidada)
        Route::get('/vehiculos/fotos/{foto}', [VehiculoFotoController::class, 'show'])->name('vehiculos.fotos.show');

        // ---- Fotos de operadores ----
        Route::scopeBindings()->group(function () {
            // Anidadas al operador (param forzado arriba a {operador})
            Route::get   ('/operadores/{operador}/fotos',        [OperadorFotoController::class, 'index'])->name('operadores.fotos.index');
            Route::post  ('/operadores/{operador}/fotos',        [OperadorFotoController::class, 'store'])->name('operadores.fotos.store');
            Route::delete('/operadores/{operador}/fotos/{foto}', [OperadorFotoController::class, 'destroy'])->name('operadores.fotos.destroy');
        });
        // Mostrar imagen privada por ID (URL corta, no anidada)
        Route::get('/operadores/fotos/{foto}', [OperadorFotoController::class, 'show'])->name('operadores.fotos.show');
    });
});

// Rutas de autenticación (login, register, etc.)
require __DIR__ . '/auth.php';
