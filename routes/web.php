<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\CapturistaController;
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
    // Operadores
    Route::get('/operadores/create', [OperadorController::class, 'create'])->name('operadores.create');
    Route::post('/operadores', [OperadorController::class, 'store'])->name('operadores.store');

    // Capturistas (CRUD completo)
    Route::resource('capturistas', CapturistaController::class);
});

// Rutas de autenticación (login, register, etc.)
require __DIR__.'/auth.php';
