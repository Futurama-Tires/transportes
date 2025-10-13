<?php

namespace App\Http\Controllers;

use App\Models\CargaCombustible;
use App\Models\CargaFoto;
use App\Models\Operador;
use App\Models\Vehiculo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Notifications\NuevaCarga;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CargasExport;

class CargaCombustibleController extends Controller
{
    public function index(Request $request)
    {
        // Exportar a Excel con filtros/orden actuales (sin paginar)
        if ($request->get('export') === 'xlsx') {
            $filename = 'cargas_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new CargasExport($request), $filename);
        }

        $filters = $request->only([
            'search',
            'vehiculo_id', 'operador_id',
            'tipo_combustible',
            'from', 'to',
            'litros_min','litros_max',
            'precio_min','precio_max',
            'total_min','total_max',
            'rend_min','rend_max',
            'km_ini_min','km_ini_max',
            'km_fin_min','km_fin_max',
            'destino','custodio',
            'estado',
            'sort_by','sort_dir',
        ]);

        $vehiculos  = Vehiculo::orderBy('unidad')->get(['id','unidad','placa','kilometros']);
        $operadores = Operador::select('id','nombre','apellido_paterno','apellido_materno')
            ->orderBy('nombre')->orderBy('apellido_paterno')->get();

        $tipos = CargaCombustible::TIPOS_COMBUSTIBLE;

        $cargas = CargaCombustible::query()
            ->with(['vehiculo','operador'])
            ->filter($filters)
            ->paginate(25)
            ->withQueryString();

        return view('cargas.index', compact('cargas','vehiculos','operadores','tipos'));
    }

    public function create()
    {
        return view('cargas.create', [
            'carga'      => new CargaCombustible(),
            'operadores' => Operador::orderBy('nombre')->get(),
            'vehiculos'  => Vehiculo::orderBy('unidad')->get(['id','unidad','placa','kilometros']),
            'tipos'      => CargaCombustible::TIPOS_COMBUSTIBLE,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha'            => ['required', 'date'],
            'precio'           => ['required', 'numeric', 'min:0'],
            'tipo_combustible' => ['required', 'in:Magna,Diesel,Premium'],
            'litros'           => ['required', 'numeric', 'min:0.001'],
            'total'            => ['required', 'numeric', 'min:0.01'],
            'custodio'         => ['nullable', 'string', 'max:255'],
            'operador_id'      => ['required', 'exists:operadores,id'],
            'vehiculo_id'      => ['required', 'exists:vehiculos,id'],
            'km_final'         => ['required', 'integer', 'min:0'], // lectura posterior
            'destino'          => ['nullable', 'string', 'max:255'],
            'observaciones'    => ['nullable', 'string', 'max:2000'],
        ]);

        // Todo lo creado en web sale Aprobada
        $data['estado'] = 'Aprobada';

        return DB::transaction(function () use ($data) {
            $vehiculo = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);

            // Base cronológica: km_inicial = km_final de la carga previa (no usar odómetro del vehículo)
            $previa = $this->findPrevCarga($vehiculo->id, $data['fecha'], null);
            $kmInicial = (int) ($previa?->km_final ?? 0);

            if ($data['km_final'] < $kmInicial) {
                throw ValidationException::withMessages([
                    'km_final' => "El KM final ({$data['km_final']}) no puede ser menor que el KM de la carga previa ({$kmInicial}).",
                ]);
            }

            // Derivados con base en la cadena cronológica
            $this->applyDerived($data, $kmInicial); // NO recalcula total

            $carga = new CargaCombustible();
            $carga->forceFill($data)->save();

            // Recalcular en cadena desde esta carga
            $this->reflowFromCarga($carga, true); // y sincroniza odómetro del vehículo

            // Notificación
            $carga->loadMissing('vehiculo','operador');
            DB::afterCommit(function () use ($carga) {
                $destinatarios = User::role(['administrador','capturista'], 'web')->get();
                Notification::send($destinatarios, new NuevaCarga($carga));
            });

            return redirect()->route('cargas.index')
                ->with('success', 'Carga registrada y kilometraje recalculado correctamente.');
        });
    }

    public function edit(CargaCombustible $carga)
    {
        $carga->load(['fotos', 'vehiculo', 'operador']);

        // Solo la más reciente puede editar KM (fecha desc, id desc)
        $kmEditable = $this->isLatestCarga($carga->vehiculo_id, $carga->fecha, $carga->id);

        return view('cargas.edit', [
            'carga'        => $carga,
            'operadores'   => Operador::orderBy('nombre')->get(),
            'vehiculos'    => Vehiculo::orderBy('unidad')->get(['id','unidad','placa','kilometros']),
            'tipos'        => CargaCombustible::TIPOS_COMBUSTIBLE,
            'km_editable'  => $kmEditable, // en la vista bloquea el input de km_final si es false
        ]);
    }

    public function update(Request $request, CargaCombustible $carga)
    {
        // ¿Es la más reciente ANTES de editar? (regla de edición en servidor)
        $eraMasReciente = $this->isLatestCarga($carga->vehiculo_id, $carga->fecha, $carga->id);

        // Validación condicional: km_final requerido solo si es la última
        $rules = [
            'fecha'            => ['required', 'date'],
            'precio'           => ['required', 'numeric', 'min:0'],
            'tipo_combustible' => ['required', 'in:Magna,Diesel,Premium'],
            'litros'           => ['required', 'numeric', 'min:0.001'],
            'total'            => ['required', 'numeric', 'min:0.01'],
            'custodio'         => ['nullable', 'string', 'max:255'],
            'operador_id'      => ['required', 'exists:operadores,id'],
            'vehiculo_id'      => ['required', 'exists:vehiculos,id'],
            'km_final'         => $eraMasReciente ? ['required','integer','min:0'] : ['sometimes','integer','min:0'],
            'destino'          => ['nullable', 'string', 'max:255'],
            'observaciones'    => ['nullable', 'string', 'max:2000'],
            'estado'           => ['required', 'in:Pendiente,Aprobada'],
        ];
        $data = $request->validate($rules);

        return DB::transaction(function () use ($request, $carga, $data, $eraMasReciente) {
            $oldVehiculoId  = $carga->vehiculo_id;
            $oldFecha       = $carga->fecha;

            // Bloquear cambio de vehículo si NO es la más reciente (salvo admin)
            $vehiculoCambia = (int)$data['vehiculo_id'] !== (int)$oldVehiculoId;
            if (!$eraMasReciente && $vehiculoCambia && !$request->user()->hasRole('administrador')) {
                throw ValidationException::withMessages([
                    'vehiculo_id' => 'Solo un administrador puede mover una carga que no es la más reciente a otro vehículo.',
                ]);
            }

            $vehiculoNuevo = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);

            // Si NO es la más reciente → km_final inmutable (ignorar cambios)
            if (!$eraMasReciente) {
                $data['km_final'] = (int)$carga->km_final;
            }

            // Base cronológica para derivados: carga previa al NUEVO (vehiculo/fecha)
            $previaNuevaPos = $this->findPrevCarga($vehiculoNuevo->id, $data['fecha'], $carga->id);
            $kmInicial = (int) ($previaNuevaPos?->km_final ?? 0);

            // Validar consistencia: km_final (ya fijado si no es última) no puede ser < kmInicial
            if (isset($data['km_final']) && $data['km_final'] < $kmInicial) {
                throw ValidationException::withMessages([
                    'km_final' => "El KM final ({$data['km_final']}) no puede ser menor que el KM de la carga previa ({$kmInicial}).",
                ]);
            }

            // Derivados inmediatos (después se reflowea la cadena completa)
            $this->applyDerived($data, $kmInicial);

            // Guardar cambios
            $carga->forceFill($data)->save();

            // Calcular si (tras guardar) esta carga quedó como última
            $shouldUpdateVehicleOdometer = $this->isLatestCarga($vehiculoNuevo->id, $carga->fecha, $carga->id);

            // 🔁 Reflow en el vehículo NUEVO desde esta carga
            $this->reflowFromCarga($carga, $shouldUpdateVehicleOdometer);

            // Si cambió de vehículo, reflow también en el vehículo ANTERIOR
            if ($vehiculoCambia) {
                // Tomar la primera carga que quedó posterior en el vehículo viejo con respecto a la POSICIÓN ANTIGUA
                $siguienteOld = $this->findNextCarga($oldVehiculoId, $oldFecha, $carga->id);
                if ($siguienteOld) {
                    $this->reflowFromCarga($siguienteOld, true);
                } else {
                    // Si ya no hay siguientes, solo alinear el odómetro del vehículo viejo
                    $this->syncVehicleOdometer($oldVehiculoId);
                }
            }

            return redirect()->route('cargas.index')
                ->with('success', $eraMasReciente
                    ? 'Carga actualizada; kilometraje y cadena recalculados.'
                    : 'Carga actualizada (kilometraje bloqueado por no ser la más reciente); cadena recalculada.');
        });
    }

    public function destroy(CargaCombustible $carga)
    {
        return DB::transaction(function () use ($carga) {
            $vehiculoId = $carga->vehiculo_id;

            // Guardar referencia del primer "siguiente" (posición actual antes de borrar)
            $siguiente = $this->findNextCarga($vehiculoId, $carga->fecha, $carga->id);

            $deleted = $carga->delete();

            // Si había cargas posteriores, recalcular desde la primera siguiente
            if ($deleted && $siguiente) {
                $this->reflowFromCarga($siguiente, true);
            } else {
                // Alinear odómetro al nuevo último
                $this->syncVehicleOdometer($vehiculoId);
            }

            return redirect()->route('cargas.index')
                ->with('success', $deleted ? 'Carga eliminada y kilometraje recalculado.' : 'No se pudo eliminar la carga.');
        });
    }

    /** ===================== Revisión: Aprobar ===================== */

    public function approve(Request $request, CargaCombustible $carga)
    {
        if (!$request->user()->hasAnyRole(['administrador','capturista'])) {
            abort(403, 'No autorizado.');
        }

        DB::transaction(function () use ($request, $carga) {
            $updated = CargaCombustible::whereKey($carga->id)
                ->where('estado', CargaCombustible::ESTADO_PENDIENTE)
                ->update([
                    'estado'       => CargaCombustible::ESTADO_APROBADA,
                    'revisado_por' => $request->user()->id,
                    'revisado_en'  => now(),
                ]);

            if (!$updated) {
                throw ValidationException::withMessages([
                    'estado' => 'La carga ya fue aprobada o su estado cambió.'
                ]);
            }
        });

        return back()->with('success', "Carga #{$carga->id} aprobada.");
    }

    // ===================== Helpers de cálculo =====================

    /**
     * Calcula y normaliza campos derivados:
     * - mes
     * - km_inicial (si es null => 0)
     * - recorrido = max(0, km_final - km_inicial) si existe km_final
     * - rendimiento = recorrido / litros
     * - diferencia (misma lógica que tenías)
     */
    protected function applyDerived(array &$data, ?int $kmInicial): void
    {
        $data['mes'] = ucfirst(Carbon::parse($data['fecha'])->locale('es')->translatedFormat('F'));

        // NO calcular total. Solo normalizar si viene.
        if (isset($data['total'])) {
            $data['total'] = round((float) $data['total'], 2);
        }

        // Si km_inicial es null, tómalo como 0 (evita errores de cálculo)
        $kmBase = (int) ($kmInicial ?? 0);
        $data['km_inicial'] = $kmBase;

        $kmFinal = isset($data['km_final']) ? (int) $data['km_final'] : null;

        $recorrido = isset($kmFinal) ? max(0, $kmFinal - $kmBase) : null;
        $data['recorrido'] = is_null($recorrido) ? null : (int)$recorrido;

        $data['rendimiento'] = (!is_null($recorrido) && (float)$data['litros'] > 0)
            ? round($recorrido / (float)$data['litros'], 2)
            : null;

        if (!is_null($recorrido) && isset($data['litros'], $data['precio'])) {
            $data['diferencia'] = round(-(((float)$data['litros'] - ($recorrido / 14)) * (float)$data['precio']), 2);
        } else {
            $data['diferencia'] = null;
        }
    }

    /**
     * Recalcula km_inicial/recorrido/rendimiento/diferencia desde $start hacia adelante,
     * con orden (fecha asc, id asc). Opcionalmente sincroniza el odómetro del vehículo.
     */
    protected function reflowFromCarga(CargaCombustible $start, bool $updateVehicleOdometer = true): void
    {
        $vehiculoId = $start->vehiculo_id;

        // Carga inmediatamente anterior al inicio (para base de comparación)
        $prev = $this->findPrevCarga($vehiculoId, $start->fecha, $start->id);

        // Todas las cargas DESPUÉS de "start" (NO incluir "start")
        $cadena = CargaCombustible::where('vehiculo_id', $vehiculoId)
            ->where(function ($q) use ($start) {
                $q->where('fecha', '>', $start->fecha)
                  ->orWhere(function ($q2) use ($start) {
                      // evitar duplicar $start
                      $q2->where('fecha', $start->fecha)->where('id', '>', $start->id);
                  });
            })
            ->orderBy('fecha', 'asc')->orderBy('id', 'asc')
            ->get();

        // Procesar primero la propia $start y luego las siguientes
        $lista = collect([$start])->merge($cadena);

        $anterior = $prev; // puede ser null
        foreach ($lista as $c) {
            $payload = [
                'fecha'            => $c->fecha,
                'precio'           => $c->precio,
                'litros'           => $c->litros,
                'total'            => $c->total,
                'km_final'         => $c->km_final,
                'tipo_combustible' => $c->tipo_combustible,
            ];

            // km_inicial = km_final de la carga anterior cronológica; si no hay, 0
            $kmBase = $anterior?->km_final; // null => 0 en applyDerived
            $this->applyDerived($payload, $kmBase);

            $c->forceFill([
                'mes'         => $payload['mes'],
                'km_inicial'  => $payload['km_inicial'],
                'recorrido'   => $payload['recorrido'],
                'rendimiento' => $payload['rendimiento'],
                'diferencia'  => $payload['diferencia'],
            ])->save();

            $anterior = $c;
        }

        if ($updateVehicleOdometer) {
            $this->syncVehicleOdometer($vehiculoId);
        }
    }

    /** Fuente de verdad del odómetro: vehiculos.kilometros = km_final de la última carga */
    protected function syncVehicleOdometer(int $vehiculoId): void
    {
        $vehiculo = Vehiculo::lockForUpdate()->find($vehiculoId);
        if ($vehiculo) {
            $ultima = $this->latestCarga($vehiculoId);
            $vehiculo->update(['kilometros' => $ultima?->km_final]);
        }
    }

    /** Última carga cronológica del vehículo (fecha desc, id desc) */
    protected function latestCarga(int $vehiculoId): ?CargaCombustible
    {
        return CargaCombustible::where('vehiculo_id', $vehiculoId)
            ->orderBy('fecha','desc')->orderBy('id','desc')
            ->first();
    }

    /** ¿(fecha,id) es la última para el vehículo? */
    protected function isLatestCarga(int $vehiculoId, $fecha, int $id): bool
    {
        $ultima = $this->latestCarga($vehiculoId);
        return $ultima && $ultima->fecha == $fecha && $ultima->id == $id;
    }

    /**
     * Carga inmediatamente anterior a (fecha,id). Si $excludeId se pasa, lo excluye.
     * Orden canónico: fecha DESC, id DESC.
     */
    protected function findPrevCarga(int $vehiculoId, $fecha, ?int $excludeId = null): ?CargaCombustible
    {
        return CargaCombustible::where('vehiculo_id', $vehiculoId)
            ->when($excludeId, fn($q) => $q->where('id','!=',$excludeId))
            ->where(function ($q) use ($fecha, $excludeId) {
                $q->where('fecha','<',$fecha)
                  ->orWhere(function ($q2) use ($fecha, $excludeId) {
                      $q2->where('fecha',$fecha);
                      if ($excludeId) {
                          $q2->where('id','<',$excludeId);
                      } else {
                          $q2->where('id','<', PHP_INT_MAX);
                      }
                  });
            })
            ->orderBy('fecha','desc')->orderBy('id','desc')
            ->first();
    }

    /** Carga inmediatamente posterior a (fecha,id) */
    protected function findNextCarga(int $vehiculoId, $fecha, int $id): ?CargaCombustible
    {
        return CargaCombustible::where('vehiculo_id', $vehiculoId)
            ->where(function ($q) use ($fecha, $id) {
                $q->where('fecha','>',$fecha)
                  ->orWhere(function ($q2) use ($fecha, $id) {
                      $q2->where('fecha',$fecha)->where('id','>',$id);
                  });
            })
            ->orderBy('fecha','asc')->orderBy('id','asc')
            ->first();
    }

    // ===================== API MÓVIL =====================

    /**
     * API móvil: crea carga y, si vienen imágenes temporales, las anexa (tabla carga_fotos).
     * Entrada opcional:
     *   - imagenes: [{tipo: 'ticket|voucher|odometro|extra', tmp_path: 'tmp/ocr/...'}]
     */
    public function storeApi(Request $request)
    {
        $data = $request->validate([
            'fecha'            => ['required', 'date'],
            'precio'           => ['required', 'numeric', 'min:0'],
            'tipo_combustible' => ['required', 'in:Magna,Diesel,Premium'],
            'litros'           => ['required', 'numeric', 'min:0.001'],
            'total'            => ['required', 'numeric', 'min:0.01'],
            'custodio'         => ['nullable', 'string', 'max:255'],
            'vehiculo_id'      => ['required', 'exists:vehiculos,id'],
            'km_final'         => ['required', 'integer', 'min:0'],
            'destino'          => ['nullable', 'string', 'max:255'],
            'observaciones'    => ['nullable', 'string', 'max:2000'],
            'imagenes'             => ['nullable', 'array'],
            'imagenes.*.tipo'      => ['nullable', 'in:ticket,voucher,odometro,extra'],
            'imagenes.*.tmp_path'  => ['required_with:imagenes', 'string'],
        ]);

        $imagenes = $data['imagenes'] ?? [];
        unset($data['imagenes']);

        $user = $request->user();
        $operador = Operador::where('user_id', $user->id)->first();

        if (!$operador) {
            return response()->json(['message' => 'El usuario autenticado no tiene un operador asociado.'], 422);
        }

        // Todo lo que llega por API inicia como Pendiente
        $data['estado'] = 'Pendiente';

        return DB::transaction(function () use ($data, $operador, $imagenes) {
            $vehiculo = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);

            // Base cronológica
            $previa = $this->findPrevCarga($vehiculo->id, $data['fecha'], null);
            $kmInicial = (int) ($previa?->km_final ?? 0);

            if ($data['km_final'] < $kmInicial) {
                return response()->json([
                    'errors' => ['km_final' => ["El KM final ({$data['km_final']}) no puede ser menor que el KM de la carga previa ({$kmInicial})."]]
                ], 422);
            }

            $payload = $data;
            $payload['operador_id'] = $operador->id;

            $this->applyDerived($payload, $kmInicial); // NO recalcula total

            $carga = new CargaCombustible();
            $carga->forceFill($payload)->save();

            if (!empty($imagenes)) {
                $this->attachTmpImagesToCarga($carga, $imagenes);
            }

            // Recalcular en cadena y sincronizar odómetro
            $this->reflowFromCarga($carga, true);

            $carga->loadMissing('vehiculo','operador','fotos');

            DB::afterCommit(function () use ($carga) {
                $destinatarios = User::role(['administrador','capturista'], 'web')->get();
                Notification::send($destinatarios, new NuevaCarga($carga));
            });

            return response()->json(
                $carga->load([
                    'vehiculo:id,unidad,placa',
                    'operador:id,nombre,apellido_paterno,apellido_materno',
                    'fotos:id,carga_id,tipo,path,mime,size,original_name'
                ]),
                201
            );
        });
    }

    /**
     * Mueve archivos temporales a PRIVADO.
     * Acepta ambos orígenes durante la transición:
     *   - PRIVADO: storage/app/tmp/ocr/...
     *   - LEGADO (público): storage/app/public/tmp/ocr/...
     * Los deja en: storage/app/cargas/{carga_id}/...
     */
    protected function attachTmpImagesToCarga(CargaCombustible $carga, array $imagenes): void
    {
        $private = Storage::disk('local');  // destino privado
        $public  = Storage::disk('public'); // legado (origen posible)

        $baseDir = "cargas/{$carga->id}";
        if (!$private->exists($baseDir)) {
            $private->makeDirectory($baseDir);
        }

        foreach ($imagenes as $img) {
            $tmp  = $img['tmp_path'] ?? null;
            $tipo = $img['tipo'] ?? CargaFoto::EXTRA;

            if (!$tmp || !is_string($tmp)) continue;
            if (!str_starts_with($tmp, 'tmp/ocr/')) continue;

            // Detecta en qué disco está la temporal
            $sourceDisk = null;
            if ($private->exists($tmp)) {
                $sourceDisk = 'local';
            } elseif ($public->exists($tmp)) {
                $sourceDisk = 'public';
            } else {
                continue; // no existe en ninguno
            }

            $ext  = pathinfo($tmp, PATHINFO_EXTENSION) ?: 'jpg';
            $name = ($tipo ?: 'extra') . '-' . now()->format('Ymd-His') . '-' . Str::random(6) . '.' . $ext;
            $dest = $baseDir . '/' . $name;

            // Mueve en-local o copia de public->local y borra origen
            if ($sourceDisk === 'local') {
                if (!$private->move($tmp, $dest)) {
                    throw ValidationException::withMessages(['imagenes' => "No se pudo mover la imagen temporal {$tmp}."]);
                }
            } else {
                try {
                    $bytes = $public->get($tmp);
                    $private->put($dest, $bytes);
                    $public->delete($tmp);
                } catch (\Throwable $e) {
                    throw ValidationException::withMessages(['imagenes' => "No se pudo transferir la imagen temporal {$tmp} al almacenamiento privado."]);
                }
            }

            $mime = $private->mimeType($dest);
            $size = $private->size($dest);

            CargaFoto::create([
                'carga_id'      => $carga->id,
                'tipo'          => $tipo,
                'path'          => $dest,
                'mime'          => $mime,
                'size'          => $size,
                'original_name' => null,
            ]);
        }
    }
}
