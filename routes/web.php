<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
use App\Http\Controllers\CalendarioVerificacionController;
use App\Services\TelegramNotifier;
use App\Http\Controllers\VerificacionReglaController;
use App\Http\Controllers\ProgramaVerificacionController;
use App\Http\Controllers\AdminBackupController;
use App\Http\Controllers\Auth\PasswordResetLinkController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Rutas HTTP para la capa web.
| - Rutas públicas (welcome)
| - Rutas autenticadas (perfil, dashboard)
| - Rutas por rol (administrador, capturista)
| - Recursos anidados con scopeBindings
| - Convenciones de nombres y parámetros
|
| También se definen patrones numéricos globales para IDs con el fin de
| evitar coincidencias ambiguas y validar temprano.
*/

/* ------------------------------------------------------------------ */
/* Patrones globales de parámetros (IDs numéricos)                    */
/* ------------------------------------------------------------------ */
Route::pattern('operador', '\d+');
Route::pattern('vehiculo', '\d+');
Route::pattern('verificacion', '\d+');
Route::pattern('tanque', '\d+');
Route::pattern('tarjeta', '\d+');            // SiVale
Route::pattern('tarjeta_comodin', '\d+');    // Tarjeta Comodín
Route::pattern('gasto', '\d+');
Route::pattern('carga', '\d+');
Route::pattern('foto', '\d+');
Route::pattern('verificacion_regla', '\d+'); // id de regla de verificación

/* ------------------------------------------------------------------ */
/* Rutas públicas                                                     */
/* ------------------------------------------------------------------ */
Route::view('/', 'welcome')->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
});


/* ------------------------------------------------------------------ */
/* Rutas autenticadas                                                 */
/* ------------------------------------------------------------------ */
Route::middleware('auth')->group(function () {

    /* Dashboard (usuarios autenticados y verificados) */
    Route::view('/dashboard', 'dashboard')
        ->middleware('verified')
        ->name('dashboard');

    /* -------------------------------------------------------------- */
    /* Perfil del usuario autenticado                                 */
    /* -------------------------------------------------------------- */
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    /* -------------------------------------------------------------- */
    /* Notificaciones (navbar + polling)                              */
    /* -------------------------------------------------------------- */
    Route::name('notificaciones.')->prefix('notificaciones')->group(function () {
        // Últimas no leídas (máx. 8)
        Route::get('/nuevas', function (Request $request) {
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
        })->name('nuevas');

        // Marcar una notificación como leída
        Route::post('/{id}/leer', function (Request $request, $id) {
            $n = $request->user()
                ->notifications()
                ->where('id', $id)
                ->firstOrFail();

            $n->markAsRead();

            return response()->noContent();
        })->name('leer');
    });

    /* -------------------------------------------------------------- */
    /* Administración pura (solo administradores)                      */
    /* -------------------------------------------------------------- */
    Route::middleware('role:administrador')->group(function () {
        Route::resource('capturistas', CapturistaController::class);

        Route::get('/admin/backup', [AdminBackupController::class, 'index'])
        ->name('admin.backup.index');

        // Usamos POST para evitar que bots o prefetch ejecuten descargas/restores sin intención
        Route::post('/admin/backup/download', [AdminBackupController::class, 'download'])
            ->name('admin.backup.download');

        Route::post('/admin/backup/restore', [AdminBackupController::class, 'restore'])
            ->name('admin.backup.restore');
    });

    /* -------------------------------------------------------------- */
    /* Administración y captura (administrador | capturista)          */
    /* -------------------------------------------------------------- */
    Route::middleware('role:administrador|capturista')->group(function () {

        /* Recursos base */
        Route::resource('operadores', OperadorController::class)
            ->parameters(['operadores' => 'operador']);

        Route::resources([
            'vehiculos' => VehiculoController::class,
            'tarjetas'  => TarjetaSiValeController::class, // parámetro {tarjeta}
        ]);

        Route::resource('verificaciones', VerificacionController::class)
            ->parameters(['verificaciones' => 'verificacion']);

        Route::resource('cargas', CargaCombustibleController::class)
            ->parameters(['cargas' => 'carga']);

        /* Tanques anidados a Vehículo (sin show) */
        Route::scopeBindings()->group(function () {
            Route::resource('vehiculos.tanques', TanqueController::class)
                ->except(['show']);
        });

        /* Tarjeta Comodín + Gastos */
        Route::resource('tarjetas-comodin', TarjetaComodinController::class);

        // Listado global de gastos (filtrable por ?tarjeta=ID)
        Route::get('comodin-gastos', [ComodinGastoController::class, 'index'])
            ->name('comodin-gastos.index');

        // Gastos anidados a Tarjeta Comodín con rutas "shallow" para edición/actualización/borrado
        Route::scopeBindings()->group(function () {
            Route::resource('tarjetas-comodin.gastos', ComodinGastoController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy'])
                ->shallow();
        });

        /* Fotos de Vehículos */
        Route::prefix('vehiculos')->name('vehiculos.')->scopeBindings()->group(function () {
            Route::get('{vehiculo}/fotos',        [VehiculoFotoController::class, 'index'])->name('fotos.index');
            Route::post('{vehiculo}/fotos',       [VehiculoFotoController::class, 'store'])->name('fotos.store');
            Route::delete('{vehiculo}/fotos/{foto}', [VehiculoFotoController::class, 'destroy'])->name('fotos.destroy');
        });
        // Visualización directa por ID (no anidada)
        Route::get('vehiculos/fotos/{foto}', [VehiculoFotoController::class, 'show'])
            ->name('vehiculos.fotos.show');

        /* Fotos de Operadores */
        Route::prefix('operadores')->name('operadores.')->scopeBindings()->group(function () {
            Route::get('{operador}/fotos',        [OperadorFotoController::class, 'index'])->name('fotos.index');
            Route::post('{operador}/fotos',       [OperadorFotoController::class, 'store'])->name('fotos.store');
            Route::delete('{operador}/fotos/{foto}', [OperadorFotoController::class, 'destroy'])->name('fotos.destroy');
        });
        // Visualización directa por ID (no anidada)
        Route::get('operadores/fotos/{foto}', [OperadorFotoController::class, 'show'])
            ->name('operadores.fotos.show');

        /* Fotos de Cargas (web) */
        Route::prefix('cargas')->name('cargas.')->group(function () {
            // Visualización directa por ID (protegida)
            Route::get('fotos/{foto}', [CargaFotoWebController::class, 'show'])->name('fotos.show');
            // Subir foto a una carga
            Route::post('{carga}/fotos', [CargaFotoWebController::class, 'store'])->name('fotos.store');
            // Borrar foto de una carga
            Route::delete('{carga}/fotos/{foto}', [CargaFotoWebController::class, 'destroy'])->name('fotos.destroy');
        });

        Route::resource('calendarios', CalendarioVerificacionController::class)
            ->parameters(['calendarios' => 'calendario'])
            ->names('calendarios');

        // --- Verificación: Reglas y generación de periodos ---
        Route::prefix('verificacion-reglas')->name('verificacion-reglas.')->group(function () {

            // Primero el endpoint JSON para evitar choques con rutas dinámicas
            Route::get('/estados-disponibles', [VerificacionReglaController::class, 'estadosDisponibles'])
                ->name('estados-disponibles');

            // CRUD
            Route::get('/',                          [VerificacionReglaController::class, 'index'])->name('index');
            Route::get('/create',                    [VerificacionReglaController::class, 'create'])->name('create');
            Route::post('/',                         [VerificacionReglaController::class, 'store'])->name('store');
            Route::get('/{verificacion_regla}/edit', [VerificacionReglaController::class, 'edit'])->name('edit');
            Route::put('/{verificacion_regla}',      [VerificacionReglaController::class, 'update'])->name('update');
            Route::delete('/{verificacion_regla}',   [VerificacionReglaController::class, 'destroy'])->name('destroy');

            // Generar periodos
            Route::get('/{verificacion_regla}/generar',  [VerificacionReglaController::class, 'generarForm'])->name('generar.form');
            Route::post('/{verificacion_regla}/generar', [VerificacionReglaController::class, 'generar'])->name('generar');
        });

        Route::prefix('programa-verificacion')->name('programa-verificacion.')->group(function () {
            Route::get('/',         [ProgramaVerificacionController::class, 'index'])->name('index');
            Route::post('/marcar',  [ProgramaVerificacionController::class, 'marcar'])->name('marcar');
        });
    });
});

/* ------------------------------------------------------------------ */
/* Ruta de prueba para Telegram                                       */
/* ------------------------------------------------------------------ */
Route::middleware('auth')->get('/debug/telegram', function (TelegramNotifier $tg) {
    $ok = $tg->send("✅ <b>Prueba</b> de notificación desde Laravel.\n<i>Si ves esto, ya estamos listos.</i>");
    return $ok ? '✅ Enviado a Telegram' : '❌ No se pudo enviar (revisa logs).';
})->name('debug.telegram');

/* ------------------------------------------------------------------ */
/* Auth scaffolding (login, register, etc.)                           */
/* ------------------------------------------------------------------ */
require __DIR__ . '/auth.php';
