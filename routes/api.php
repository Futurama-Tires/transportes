<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Login público
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas con token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Ejemplos:
    // Route::get('/operadores', [OperadorController::class,'index']);
    // Route::post('/vehiculos', [VehiculoController::class,'store']);
});

