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

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página de inicio
Route::view('/', 'welcome');

// Dashboard general (usuarios autenticados y verificados)
Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Ejemplo solo para rol operador (requiere verificación como en tu versión)
    Route::view('/ejemploRol', 'ejemploRol')
        ->middleware(['verified', 'role:operador'])
        ->name('ejemploRol');

    // Dashboard exclusivo para administradores
    Route::view('/dashboard-admin', 'dashboards.admin')
        ->middleware('role:administrador')
        ->name('dashboard.admin');

    // Perfil de usuario (cualquier usuario autenticado)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gestión solo para administradores
    Route::middleware('role:administrador')->group(function () {
        // Capturistas (CRUD completo)
        Route::resource('capturistas', CapturistaController::class);
    });

    // Gestión para administradores y capturistas
    Route::middleware('role:administrador|capturista')->group(function () {
        // CRUD completos: Operadores, Vehículos, Tarjetas, Verificaciones
        // (Con Route::resources evitamos duplicar create/store y rutas manuales)
        Route::resources([
            'operadores'     => OperadorController::class,
            'vehiculos'      => VehiculoController::class,
            'tarjetas'       => TarjetaSiValeController::class,
        ]);

        Route::resource('verificaciones', VerificacionController::class)
        ->parameters(['verificaciones' => 'verificacion']); // <- clave del fix
    });

    Route::middleware(['auth','role:administrador|capturista'])
    ->scopeBindings()
    ->group(function () {
        Route::resource('vehiculos.tanques', TanqueController::class)->except(['show']);
    });

    Route::resource('cargas', CargaCombustibleController::class)
    ->parameters(['cargas' => 'carga']);
    
    Route::middleware(['auth', 'role:administrador|capturista'])->group(function () {
    // Gestor de fotos por vehículo (anidadas al vehículo)
    Route::get   ('/vehiculos/{vehiculo}/fotos',             [VehiculoFotoController::class, 'index'])->name('vehiculos.fotos.index');
    Route::post  ('/vehiculos/{vehiculo}/fotos',             [VehiculoFotoController::class, 'store'])->name('vehiculos.fotos.store');
    Route::delete('/vehiculos/{vehiculo}/fotos/{foto}',      [VehiculoFotoController::class, 'destroy'])->name('vehiculos.fotos.destroy');

    // Servir imagen privada por ID de foto (no anidada para URL corta)
    Route::get   ('/vehiculos/fotos/{foto}',                 [VehiculoFotoController::class, 'show'])->name('vehiculos.fotos.show');
});
});

// Rutas de autenticación (login, register, etc.)
require __DIR__.'/auth.php';
