<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // 👈 Importa la clase Request (NO el Facade)

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
use App\Http\Controllers\CargaFotoWebController;
use App\Http\Controllers\TarjetaComodinController;
use App\Http\Controllers\ComodinGastoController;
use App\Services\TelegramNotifier;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Definición de rutas HTTP para la capa web. Incluye:
| - Rutas públicas (welcome)
| - Rutas protegidas por autenticación/roles
| - Recursos anidados con scopeBindings
| - Convenciones de nombres y parámetros
|
| Nota: se agregan patrones numéricos globales para IDs a fin de evitar
| coincidencias ambiguas y mejorar la validación temprana.
|--------------------------------------------------------------------------
*/

/** --------------------------------------------------------------------
 *  Patrones globales de parámetros (IDs numéricos)
 *  ------------------------------------------------------------------*/
Route::pattern('operador',  '\d+');
Route::pattern('vehiculo',  '\d+');
Route::pattern('verificacion', '\d+');
Route::pattern('tanque',    '\d+');
Route::pattern('tarjeta',   '\d+');           // SiVale
Route::pattern('tarjeta_comodin', '\d+');     // Tarjeta Comodín
Route::pattern('gasto',     '\d+');
Route::pattern('carga',     '\d+');
Route::pattern('foto',      '\d+');

/** --------------------------------------------------------------------
 *  Rutas públicas
 *  ------------------------------------------------------------------*/
Route::view('/', 'welcome')->name('welcome');

/** --------------------------------------------------------------------
 *  Rutas autenticadas
 *  ------------------------------------------------------------------*/
Route::middleware('auth')->group(function () {

    /** --------------------------------------------------------------
     *  Dashboard y vistas generales de usuario
     *  ------------------------------------------------------------*/
    Route::middleware('verified')->group(function () {
        // Dashboard general
        Route::view('/dashboard', 'dashboard')->name('dashboard');
    });

    /** --------------------------------------------------------------
     *  Perfil (usuario autenticado)
     *  ------------------------------------------------------------*/
    Route::get   ('/profile', [ProfileController::class, 'edit'   ])->name('profile.edit');
    Route::patch ('/profile', [ProfileController::class, 'update' ])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /** --------------------------------------------------------------
     *  Endpoints de notificaciones (navbar + polling)
     *  ------------------------------------------------------------*/
    Route::get('/notificaciones/nuevas', function (Request $request) {
        $user = $request->user();

        $items = $user->unreadNotifications()
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($n) {
                return [
                    'id'      => $n->id,
                    'titulo'  => data_get($n->data, 'titulo', 'Notificación'),
                    'mensaje' => data_get($n->data, 'mensaje', ''),
                    'url'     => data_get($n->data, 'url', route('cargas.index')),
                    'fecha'   => optional($n->created_at)->diffForHumans(),
                ];
            });

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'items' => $items,
        ]);
    })->name('notificaciones.nuevas');

    Route::post('/notificaciones/{id}/leer', function (Request $request, $id) {
        $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();
        return response()->noContent();
    })->name('notificaciones.leer');

    /** --------------------------------------------------------------
     *  Administración pura (solo administradores)
     *  ------------------------------------------------------------*/
    Route::middleware('role:administrador')->group(function () {
        Route::resource('capturistas', CapturistaController::class);
    });

    /** --------------------------------------------------------------
     *  Administración y Captura (admin | capturista)
     *  ------------------------------------------------------------*/
    Route::middleware('role:administrador|capturista')->group(function () {

        /* ------------------------- Recursos base ------------------ */

        // Operadores: forzamos el nombre del parámetro a {operador}
        Route::resource('operadores', OperadorController::class)
            ->parameters(['operadores' => 'operador']);

        // Vehículos y Tarjetas SiVale
        Route::resources([
            'vehiculos' => VehiculoController::class,
            'tarjetas'  => TarjetaSiValeController::class, // parámetro {tarjeta}
        ]);

        // Verificaciones: forzamos {verificacion}
        Route::resource('verificaciones', VerificacionController::class)
            ->parameters(['verificaciones' => 'verificacion']);

        // Cargas de combustible: forzamos {carga}
        Route::resource('cargas', CargaCombustibleController::class)
            ->parameters(['cargas' => 'carga']);

        /* --------------- Recursos anidados con scopeBindings ------ */

        // Tanques anidados a Vehículo (sin show)
        Route::scopeBindings()->group(function () {
            Route::resource('vehiculos.tanques', TanqueController::class)
                ->except(['show']);
        });

        /* ------------------- Tarjeta Comodín + Gastos ------------- */

        // Tarjetas Comodín
        Route::resource('tarjetas-comodin', TarjetaComodinController::class);

        // Listado global de gastos (filtrable por ?tarjeta=ID)
        Route::get('comodin-gastos', [ComodinGastoController::class, 'index'])
            ->name('comodin-gastos.index');

        // Gastos anidados a Tarjeta Comodín: create/store anidados; edit/update/destroy "shallow"
        Route::scopeBindings()->group(function () {
            Route::resource('tarjetas-comodin.gastos', ComodinGastoController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy'])
                ->shallow();
        });
        // Con shallow():
        // - create/store:  tarjetas-comodin.gastos.create / tarjetas-comodin.gastos.store
        // - edit/update/destroy: gastos.edit / gastos.update / gastos.destroy

        /* --------------------- Fotos de Vehículos ------------------ */

        Route::prefix('vehiculos')->name('vehiculos.')->scopeBindings()->group(function () {
            Route::get   ('{vehiculo}/fotos',        [VehiculoFotoController::class, 'index' ])->name('fotos.index');
            Route::post  ('{vehiculo}/fotos',        [VehiculoFotoController::class, 'store' ])->name('fotos.store');
            Route::delete('{vehiculo}/fotos/{foto}', [VehiculoFotoController::class, 'destroy'])->name('fotos.destroy');
        });
        // Mostrar imagen privada por ID (URL corta, no anidada)
        Route::get('vehiculos/fotos/{foto}', [VehiculoFotoController::class, 'show'])
            ->name('vehiculos.fotos.show');

        /* --------------------- Fotos de Operadores ----------------- */

        Route::prefix('operadores')->name('operadores.')->scopeBindings()->group(function () {
            Route::get   ('{operador}/fotos',        [OperadorFotoController::class, 'index' ])->name('fotos.index');
            Route::post  ('{operador}/fotos',        [OperadorFotoController::class, 'store' ])->name('fotos.store');
            Route::delete('{operador}/fotos/{foto}', [OperadorFotoController::class, 'destroy'])->name('fotos.destroy');
        });
        // Mostrar imagen privada por ID (URL corta, no anidada)
        Route::get('operadores/fotos/{foto}', [OperadorFotoController::class, 'show'])
            ->name('operadores.fotos.show');

        /* --------------------- Fotos de Cargas (web) --------------- */

        Route::prefix('cargas')->name('cargas.')->group(function () {
            // Mostrar imagen privada por ID (URL corta, protegida)
            Route::get('fotos/{foto}', [CargaFotoWebController::class, 'show'])
                ->name('fotos.show');

            // Subir foto a una carga
            Route::post('{carga}/fotos', [CargaFotoWebController::class, 'store'])
                ->name('fotos.store');

            // Borrar foto de una carga
            Route::delete('{carga}/fotos/{foto}', [CargaFotoWebController::class, 'destroy'])
                ->name('fotos.destroy');
        });
    });
});

Route::middleware(['auth'])->get('/debug/telegram', function (TelegramNotifier $tg) {
    $ok = $tg->send("✅ <b>Prueba</b> de notificación desde Laravel.\n<i>Si ves esto, ya estamos listos.</i>");
    return $ok ? '✅ Enviado a Telegram' : '❌ No se pudo enviar (revisa logs).';
})->name('debug.telegram');

/** --------------------------------------------------------------------
 *  Auth scaffolding (login, register, etc.)
 *  ------------------------------------------------------------------*/
require __DIR__ . '/auth.php';
