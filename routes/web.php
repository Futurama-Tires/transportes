<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\CapturistaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/ejemploRol', function () {
    return view('ejemploRol');
})->middleware(['auth', 'verified', 'role:operador'])->name('ejemploRol');

Route::get('/dashboard-admin', function () {
    return view('dashboards.admin');
})->middleware(['auth', 'role:administrador'])->name('dashboard.admin');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/operadores/create', [OperadorController::class, 'create'])->name('operadores.create');
    Route::post('/operadores', [OperadorController::class, 'store'])->name('operadores.store');

    Route::get('/capturistas/create', [CapturistaController::class, 'create'])->name('capturistas.create');
    Route::post('/capturistas', [CapturistaController::class, 'store'])->name('capturistas.store');
});

require __DIR__.'/auth.php';
