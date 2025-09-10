<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CargaCombustibleController;

// Login público
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas con token
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Login (limitamos intentos para evitar fuerza bruta)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

// Rutas protegidas con token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

     // POST: crear carga (responde JSON)
    Route::post('/cargas', [CargaCombustibleController::class, 'storeApi']);

    // Catálogos mínimos para el formulario móvil
    Route::get('/vehiculos-min', function () {
        return \App\Models\Vehiculo::orderBy('unidad')
            ->get(['id','unidad','placa']);
    });

    Route::get('/operadores-min', function () {
        return \App\Models\Operador::orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->get(['id','nombre','apellido_paterno','apellido_materno']);
    });

    Route::get('/catalogos/cargas', function () {
        return [
            'ubicaciones' => \App\Models\CargaCombustible::UBICACIONES,
            'tipos'       => \App\Models\CargaCombustible::TIPOS_COMBUSTIBLE ?? ['Magna','Diesel','Premium'],
        ];
    });
});

