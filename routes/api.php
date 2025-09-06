<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
});

