<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\CapturistaController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\TarjetaSiValeController;
use App\Http\Controllers\VerificacionController;
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
});

// Rutas de autenticación (login, register, etc.)
require __DIR__.'/auth.php';
