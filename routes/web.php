<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\CapturistaController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\TarjetaSiValeController;

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

// Ejemplo solo para rol operador
Route::view('/ejemploRol', 'ejemploRol')
    ->middleware(['auth', 'verified', 'role:operador'])
    ->name('ejemploRol');

// Dashboard exclusivo para administradores
Route::view('/dashboard-admin', 'dashboards.admin')
    ->middleware(['auth', 'role:administrador'])
    ->name('dashboard.admin');

// Perfil de usuario (cualquier usuario autenticado)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Gestión solo para administradores
Route::middleware(['auth', 'role:administrador'])->group(function () {
    
    // Capturistas (CRUD completo)
    Route::resource('capturistas', CapturistaController::class);
});

// Gestión solo para administradores y capturistas
Route::middleware(['auth', 'role:administrador|capturista'])->group(function () {
    // Rutas accesibles para administradores y capturistas

    // Operadores
    Route::get('/operadores/create', [OperadorController::class, 'create'])->name('operadores.create');
    Route::post('/operadores', [OperadorController::class, 'store'])->name('operadores.store');

    // CRUD OPERADORES
    Route::resource('operadores', OperadorController::class);
    
    // CRUD Vehiculos.
    Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos.index');
    Route::get('/vehiculos/create', [VehiculoController::class, 'create'])->name('vehiculos.create');
    Route::get('/vehiculos/{vehiculo}/edit', [VehiculoController::class, 'edit'])->name('vehiculos.edit');
    Route::put('/vehiculos/{vehiculo}', [VehiculoController::class, 'update'])->name('vehiculos.update');
    Route::get('/vehiculos/{vehiculo}', [VehiculoController::class, 'show'])->name('vehiculos.show');

    
    Route::post('/vehiculos', [VehiculoController::class, 'store'])->name('vehiculos.store');
    Route::delete('/vehiculos/{vehiculo}', [VehiculoController::class, 'destroy'])->name('vehiculos.destroy'); 

    Route::resource('tarjetas', TarjetaSiValeController::class);

    });

// Rutas de autenticación (login, register, etc.)
require __DIR__.'/auth.php';
