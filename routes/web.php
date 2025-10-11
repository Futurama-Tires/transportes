<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* ================================
 | Controllers
 *=============================== */
use App\Http\Controllers\AdminBackupController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\CalendarioVerificacionController;
use App\Http\Controllers\CapturistaController;
use App\Http\Controllers\CargaCombustibleController;
use App\Http\Controllers\CargaFotoWebController;
use App\Http\Controllers\ComodinGastoController;
use App\Http\Controllers\LicenciaArchivoController;
use App\Http\Controllers\LicenciaConducirController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\OperadorFotoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramaVerificacionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TanqueController;
use App\Http\Controllers\TarjetaComodinController;
use App\Http\Controllers\TarjetaSiValeController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VehiculoFotoController;
use App\Http\Controllers\VerificacionController;
use App\Http\Controllers\VerificacionReglaController;
use App\Http\Controllers\PrecioCombustibleController;

use App\Services\TelegramNotifier;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Estructura:
| - Rutas públicas (welcome, forgot password).
| - Rutas autenticadas (dashboard, perfil, notificaciones).
| - Rutas por rol (administrador / capturista).
| - Recursos anidados con scopeBindings.
| - Patrones globales para IDs.
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
Route::pattern('verificacion_regla', '\d+'); // Regla de verificación
Route::pattern('archivo', '\d+');            // Archivos de licencia

/* ------------------------------------------------------------------ */
/* Rutas públicas                                                     */
/* ------------------------------------------------------------------ */
Route::view('/', 'welcome')->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password',  [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
});

/* ------------------------------------------------------------------ */
/* Rutas autenticadas                                                 */
/* ------------------------------------------------------------------ */
Route::middleware('auth')->group(function () {

    /* ---------------------------- Dashboard ---------------------------- */
    Route::view('/dashboard', 'dashboard')->middleware('verified')->name('dashboard');

    /* ------------------------- Perfil de usuario ----------------------- */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',    [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/',  [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    /* --------------------------- Notificaciones ------------------------ */
    Route::name('notificaciones.')->prefix('notificaciones')->group(function () {
        // Últimas no leídas (máx. 8)
        Route::get('/nuevas', function (Request $request) {
            $user  = $request->user();
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
            $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
            $n->markAsRead();
            return response()->noContent();
        })->name('leer');
    });

    // Marcar TODAS las notificaciones no leídas del usuario (se deja fuera del grupo para conservar el nombre plano si ya existe en front)
    Route::post('/leer-todas', function (Request $request) {
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        return response()->noContent(); // 204
    })->name('leer_todas');

    /* =============================================================== */
    /*   SOLO ADMINISTRADORES                                          */
    /* =============================================================== */
    Route::middleware('role:administrador')->group(function () {

        // Capturistas
        Route::resource('capturistas', CapturistaController::class);

        // Backups (se centraliza en un solo prefijo, evitando duplicados)
        Route::prefix('admin/backup')->name('admin.backup.')->group(function () {
            Route::get('/',          [AdminBackupController::class, 'index'])->name('index');
            Route::post('/download', [AdminBackupController::class, 'download'])->name('download'); // POST para evitar prefetch
            Route::post('/restore',  [AdminBackupController::class, 'restore'])->name('restore');   // POST idem
        });
    });

    /* =============================================================== */
    /*   ADMINISTRADOR | CAPTURISTA                                    */
    /* =============================================================== */
    Route::middleware('role:administrador|capturista')->group(function () {

        /* -------------------------- Recursos base ------------------------- */
        Route::resource('operadores', OperadorController::class)
            ->parameters(['operadores' => 'operador']);

        Route::resources([
            'vehiculos' => VehiculoController::class,
            'tarjetas'  => TarjetaSiValeController::class, // param implícito {tarjeta}
        ]);

        Route::resource('verificaciones', VerificacionController::class)
            ->parameters(['verificaciones' => 'verificacion']);

        Route::resource('cargas', CargaCombustibleController::class)
            ->parameters(['cargas' => 'carga']);

        /* ---------------------- Tanques por Vehículo ---------------------- */
        Route::scopeBindings()->group(function () {
            Route::resource('vehiculos.tanques', TanqueController::class)
                ->except(['show']);
        });

        /* ------------------ Tarjetas Comodín y Gastos --------------------- */
        Route::resource('tarjetas-comodin', TarjetaComodinController::class);

        // Listado global de gastos (filtrable por ?tarjeta=ID)
        Route::get('comodin-gastos', [ComodinGastoController::class, 'index'])
            ->name('comodin-gastos.index');

        // Gastos anidados a Tarjeta Comodín (shallow: edit/update/destroy fuera del prefijo)
        Route::scopeBindings()->group(function () {
            Route::resource('tarjetas-comodin.gastos', ComodinGastoController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy'])
                ->shallow();
        });

        /* -------------------------- Fotos: Vehículos ----------------------- */
        Route::prefix('vehiculos')->name('vehiculos.')->scopeBindings()->group(function () {
            Route::get('{vehiculo}/fotos',           [VehiculoFotoController::class, 'index'])->name('fotos.index');
            Route::post('{vehiculo}/fotos',          [VehiculoFotoController::class, 'store'])->name('fotos.store');
            Route::delete('{vehiculo}/fotos/{foto}', [VehiculoFotoController::class, 'destroy'])->name('fotos.destroy');
        });
        // Visualización directa por ID
        Route::get('vehiculos/fotos/{foto}', [VehiculoFotoController::class, 'show'])
            ->name('vehiculos.fotos.show');

        /* -------------------------- Fotos: Operadores ---------------------- */
        Route::prefix('operadores')->name('operadores.')->scopeBindings()->group(function () {
            Route::get('{operador}/fotos',           [OperadorFotoController::class, 'index'])->name('fotos.index');
            Route::post('{operador}/fotos',          [OperadorFotoController::class, 'store'])->name('fotos.store');
            Route::delete('{operador}/fotos/{foto}', [OperadorFotoController::class, 'destroy'])->name('fotos.destroy');
        });
        // Visualización directa por ID
        Route::get('operadores/fotos/{foto}', [OperadorFotoController::class, 'show'])
            ->name('operadores.fotos.show');

        /* ---------------------------- Fotos: Cargas ------------------------ */
        Route::prefix('cargas')->name('cargas.')->group(function () {
            // Visualización directa por ID (protegida)
            Route::get('fotos/{foto}', [CargaFotoWebController::class, 'show'])->name('fotos.show');
            // Subir/Borrar foto de una carga
            Route::post('{carga}/fotos',          [CargaFotoWebController::class, 'store'])->name('fotos.store');
            Route::delete('{carga}/fotos/{foto}', [CargaFotoWebController::class, 'destroy'])->name('fotos.destroy');

            // Aprobar carga (POST)
            Route::post('{carga}/aprobar', [CargaCombustibleController::class, 'approve'])->name('approve');
        });

        /* ---------------------- Calendario de Verificación ----------------- */
        Route::resource('calendarios', CalendarioVerificacionController::class)
            ->parameters(['calendarios' => 'calendario'])
            ->names('calendarios');

        /* --------------- Reglas de Verificación + Generación --------------- */
        Route::prefix('verificacion-reglas')->name('verificacion-reglas.')->group(function () {
            // Endpoint auxiliar JSON
            Route::get('/estados-disponibles', [VerificacionReglaController::class, 'estadosDisponibles'])
                ->name('estados-disponibles');

            // CRUD
            Route::get('/',                          [VerificacionReglaController::class, 'index'])->name('index');
            Route::get('/create',                    [VerificacionReglaController::class, 'create'])->name('create');
            Route::post('/',                         [VerificacionReglaController::class, 'store'])->name('store');
            Route::get('/{verificacion_regla}/edit', [VerificacionReglaController::class, 'edit'])->name('edit');
            Route::put('/{verificacion_regla}',      [VerificacionReglaController::class, 'update'])->name('update');
            Route::delete('/{verificacion_regla}',   [VerificacionReglaController::class, 'destroy'])->name('destroy');

            // Generación de periodos
            Route::get('/{verificacion_regla}/generar',  [VerificacionReglaController::class, 'generarForm'])->name('generar.form');
            Route::post('/{verificacion_regla}/generar', [VerificacionReglaController::class, 'generar'])->name('generar');
        });

        /* ----------------------- Programa de Verificación ------------------ */
        Route::prefix('programa-verificacion')->name('programa-verificacion.')->group(function () {
            Route::get('/',        [ProgramaVerificacionController::class, 'index'])->name('index');
            Route::post('/marcar', [ProgramaVerificacionController::class, 'marcar'])->name('marcar');
        });

        /* ------------------------------- Reportes -------------------------- */
        // Vista principal (Dashboard)
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

        // JSON API
        Route::prefix('api/reportes')->name('reportes.api.')->group(function () {
            Route::get('/rendimiento',  [ReporteController::class, 'rendimientoJson'])->name('rendimiento');
            Route::get('/costo-km',     [ReporteController::class, 'costoKmJson'])->name('costo_km');
            Route::get('/auditoria',    [ReporteController::class, 'auditoriaJson'])->name('auditoria');
            Route::get('/verificacion', [ReporteController::class, 'verificacionJson'])->name('verificacion');
        });

        // Exportación PDF
        Route::prefix('reportes')->name('reportes.export.')->group(function () {
            Route::match(['get','post'], '/rendimiento/export.pdf',  [ReporteController::class, 'exportRendimientoPdf'])->name('rendimiento_pdf');
            Route::match(['get','post'], '/costo-km/export.pdf',     [ReporteController::class, 'exportCostoKmPdf'])->name('costo_km_pdf');
            Route::match(['get','post'], '/auditoria/export.pdf',    [ReporteController::class, 'exportAuditoriaPdf'])->name('auditoria_pdf');
            Route::match(['get','post'], '/verificacion/export.pdf', [ReporteController::class, 'exportVerificacionPdf'])->name('verificacion_pdf');
        });

        /* ------------------------ Licencias de Conducir -------------------- */
        Route::resource('licencias', LicenciaConducirController::class);

        // Archivos asociados a licencias
        Route::prefix('licencias')->name('licencias.archivos.')->group(function () {
            Route::post('{licencia}/archivos',        [LicenciaArchivoController::class, 'store'])->name('store');
            Route::delete('archivos/{archivo}',       [LicenciaArchivoController::class, 'destroy'])->name('destroy');
            Route::get('archivos/{archivo}/download', [LicenciaArchivoController::class, 'download'])->name('download');
            Route::get('archivos/{archivo}/inline',   [LicenciaArchivoController::class, 'inline'])->name('inline');
        });

        /* ------------------------ Precios de Combustible ------------------- */
        Route::prefix('precios-combustible')->name('precios-combustible.')->group(function () {
            Route::get('/',                                     [PrecioCombustibleController::class, 'index'])->name('index');
            Route::post('/',                                    [PrecioCombustibleController::class, 'store'])->name('store');
            Route::put('/{precioCombustible}',                  [PrecioCombustibleController::class, 'update'])->name('update');
            Route::get('/json',                                 [PrecioCombustibleController::class, 'current'])->name('current');
            Route::post('/bulk',                                [PrecioCombustibleController::class, 'upsertMany'])->name('bulk');
            Route::post('/recalcular-tanques',                  [PrecioCombustibleController::class, 'recalc'])->name('recalc');
        });

        /* -------------------------- Vistas utilitarias --------------------- */
        Route::view('/operadores/confirmacion', 'operadores.confirmacion')
            ->name('operadores.confirmacion');
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
