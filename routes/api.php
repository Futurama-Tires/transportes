<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controladores (importa SIEMPRE con namespace correcto)
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CargaCombustibleController;
use App\Http\Controllers\Api\OcrController;
use App\Http\Controllers\Api\CargaFotoController; // ← FALTA EN TU VERSIÓN

// Login (limitamos intentos para evitar fuerza bruta)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

// Rutas protegidas con token
Route::middleware('auth:sanctum')->group(function () {

    // Info de usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // === API móvil: crear carga (operador se infiere del token) ===
    // (Solo una vez; eliminé el duplicado)
    Route::post('/cargas', [CargaCombustibleController::class, 'storeApi']);

    // Catálogos mínimos para el formulario móvil
    Route::get('/vehiculos-min', function () {
        // Incluye 'kilometros' porque la app Android lo usa para prellenar km_inicial
        return \App\Models\Vehiculo::orderBy('unidad')->get(['id','unidad','placa','kilometros']);
    });

    Route::get('/operadores-min', function () {
        return \App\Models\Operador::orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->get(['id','nombre','apellido_paterno','apellido_materno']);
    });

    Route::get('/catalogos/cargas', function () {
        return [
            'tipos' => \App\Models\CargaCombustible::TIPOS_COMBUSTIBLE ?? ['Magna','Diesel','Premium'],
        ];
    });

    // OCR — Paso 1 (solo reconocimiento, sin guardar en BD aún)
    Route::post('/ocr/ticket',   [OcrController::class, 'ticket']);
    Route::post('/ocr/voucher',  [OcrController::class, 'voucher']);
    Route::post('/ocr/odometro', [OcrController::class, 'odometro']);

    // Fotos por carga (listado/descarga/subida/borrado)
    Route::get   ('/cargas/{carga}/fotos',                    [CargaFotoController::class, 'index']);
    Route::get   ('/cargas/{carga}/fotos/{foto}',             [CargaFotoController::class, 'show']);
    Route::get   ('/cargas/{carga}/fotos/{foto}/download',    [CargaFotoController::class, 'download']);
    Route::post  ('/cargas/{carga}/fotos',                    [CargaFotoController::class, 'store']);
    Route::delete('/cargas/{carga}/fotos/{foto}',             [CargaFotoController::class, 'destroy']);
});
