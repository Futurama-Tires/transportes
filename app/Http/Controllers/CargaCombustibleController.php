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
        // 👉 Exportar a Excel con filtros/orden actuales (sin paginar)
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
            'total'            => ['required', 'numeric', 'min:0.01'], // ⬅ CAMBIO: total es obligatorio (manual)
            'custodio'         => ['nullable', 'string', 'max:255'],
            'operador_id'      => ['required', 'exists:operadores,id'],
            'vehiculo_id'      => ['required', 'exists:vehiculos,id'],
            'km_final'         => ['required', 'integer', 'min:0'],
            'destino'          => ['nullable', 'string', 'max:255'],
            'observaciones'    => ['nullable', 'string', 'max:2000'],
            // (no viene estado desde create web)
        ]);

        // ✅ Todo lo creado en web sale Aprobada
        $data['estado'] = 'Aprobada';

        return DB::transaction(function () use ($data) {
            $vehiculo  = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);
            $kmInicial = $vehiculo->kilometros;

            if (!is_null($kmInicial) && $data['km_final'] < $kmInicial) {
                throw ValidationException::withMessages([
                    'km_final' => "El KM final ({$data['km_final']}) no puede ser menor que el odómetro actual del vehículo ({$kmInicial}).",
                ]);
            }

            $this->applyDerived($data, $kmInicial); // ⬅ NO recalcula total

            $carga = new CargaCombustible();
            $carga->forceFill($data)->save();

            $vehiculo->update(['kilometros' => $data['km_final']]);

            $carga->loadMissing('vehiculo','operador');
            DB::afterCommit(function () use ($carga) {
                $destinatarios = User::role(['administrador','capturista'], 'web')->get();
                Notification::send($destinatarios, new NuevaCarga($carga));
            });

            return redirect()->route('cargas.index')
                ->with('success', 'Carga registrada y odómetro del vehículo actualizado.');
        });
    }

    public function edit(CargaCombustible $carga)
    {
        $carga->load(['fotos', 'vehiculo', 'operador']);

        return view('cargas.edit', [
            'carga'      => $carga,
            'operadores' => Operador::orderBy('nombre')->get(),
            'vehiculos'  => Vehiculo::orderBy('unidad')->get(['id','unidad','placa','kilometros']),
            'tipos'      => CargaCombustible::TIPOS_COMBUSTIBLE,
        ]);
    }

    public function update(Request $request, CargaCombustible $carga)
    {
        $data = $request->validate([
            'fecha'            => ['required', 'date'],
            'precio'           => ['required', 'numeric', 'min:0'],
            'tipo_combustible' => ['required', 'in:Magna,Diesel,Premium'],
            'litros'           => ['required', 'numeric', 'min:0.001'],
            'total'            => ['required', 'numeric', 'min:0.01'], // ⬅ CAMBIO: total obligatorio (manual)
            'custodio'         => ['nullable', 'string', 'max:255'],
            'operador_id'      => ['required', 'exists:operadores,id'],
            'vehiculo_id'      => ['required', 'exists:vehiculos,id'],
            'km_final'         => ['required', 'integer', 'min:0'],
            'destino'          => ['nullable', 'string', 'max:255'],
            'observaciones'    => ['nullable', 'string', 'max:2000'],
            // ✅ AQUÍ ESTÁ LA CLAVE:
            'estado'           => ['required', 'in:Pendiente,Aprobada'],
        ]);

        return DB::transaction(function () use ($carga, $data) {
            $vehiculo = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);

            $previa = CargaCombustible::where('vehiculo_id', $vehiculo->id)
                ->where(function($q) use ($carga, $data){
                    $fechaNueva = $data['fecha'];
                    $q->where('fecha','<', $fechaNueva)
                    ->orWhere(function($q2) use ($fechaNueva, $carga){
                        $q2->where('fecha', $fechaNueva)->where('id','<', $carga->id);
                    });
                })
                ->orderBy('fecha','desc')->orderBy('id','desc')
                ->first();

            $kmInicial = $previa?->km_final ?? $vehiculo->kilometros;

            if (!is_null($kmInicial) && $data['km_final'] < $kmInicial) {
                throw ValidationException::withMessages([
                    'km_final' => "El KM final ({$data['km_final']}) no puede ser menor que el KM inicial calculado ({$kmInicial}).",
                ]);
            }

            $this->applyDerived($data, $kmInicial); // ⬅ NO recalcula total

            // $data incluye 'estado', por lo que se guardará
            $carga->forceFill($data)->save();

            $ultima = CargaCombustible::where('vehiculo_id', $vehiculo->id)
                ->orderBy('fecha','desc')->orderBy('id','desc')
                ->first();

            if ($ultima && $ultima->id === $carga->id) {
                $vehiculo->update(['kilometros' => $data['km_final']]);
            }

            return redirect()->route('cargas.index')
                ->with('success', 'Carga actualizada correctamente.');
        });
    }


    public function destroy(CargaCombustible $carga)
    {
        return DB::transaction(function () use ($carga) {
            $vehiculoId = $carga->vehiculo_id;

            $esUltima = CargaCombustible::where('vehiculo_id', $vehiculoId)
                ->orderBy('fecha','desc')->orderBy('id','desc')->value('id') === $carga->id;

            $deleted = $carga->delete();

            if ($deleted && $esUltima) {
                $vehiculo = Vehiculo::lockForUpdate()->find($vehiculoId);
                if ($vehiculo) {
                    $nuevaUltima = CargaCombustible::where('vehiculo_id', $vehiculoId)
                        ->orderBy('fecha','desc')->orderBy('id','desc')->first();
                    $vehiculo->update(['kilometros' => $nuevaUltima?->km_final]);
                }
            }

            return redirect()->route('cargas.index')
                ->with('success', $deleted ? 'Carga eliminada correctamente.' : 'No se pudo eliminar la carga.');
        });
    }

    /** ===================== Revisión: Aprobar ===================== */

    public function approve(Request $request, CargaCombustible $carga)
    {
        // Permitir solo a admin/capturista (Spatie)
        if (!$request->user()->hasAnyRole(['administrador','capturista'])) {
            abort(403, 'No autorizado.');
        }

        DB::transaction(function () use ($request, $carga) {
            // Concurrencia: solo si sigue en Pendiente
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

    // ===================== Helpers =====================

    protected function applyDerived(array &$data, ?int $kmInicial): void
    {
        $data['mes'] = ucfirst(Carbon::parse($data['fecha'])->locale('es')->translatedFormat('F'));

        // ⬅ CAMBIO: NO calcular total. Solo normalizar si viene.
        if (isset($data['total'])) {
            $data['total'] = round((float) $data['total'], 2);
        }

        $data['km_inicial'] = $kmInicial;

        $recorrido = (!is_null($kmInicial) && isset($data['km_final']))
            ? max(0, (int)$data['km_final'] - (int)$kmInicial)
            : null;

        $data['recorrido'] = is_null($recorrido) ? null : (int)$recorrido;

        $data['rendimiento'] = (!is_null($recorrido) && (float)$data['litros'] > 0)
            ? round($recorrido / (float)$data['litros'], 2)
            : null;

        // Mantén tu lógica de diferencia (usa precio si lo tienes)
        if (!is_null($recorrido) && isset($data['litros'], $data['precio'])) {
            $data['diferencia'] = round(-(((float)$data['litros'] - ($recorrido / 14)) * (float)$data['precio']), 2);
        } else {
            $data['diferencia'] = null;
        }
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
            'total'            => ['required', 'numeric', 'min:0.01'], // ⬅ CAMBIO: total obligatorio en API
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

        // ✅ Todo lo que llega por API inicia como Pendiente
        $data['estado'] = 'Pendiente';

        return DB::transaction(function () use ($data, $operador, $imagenes) {
            $vehiculo  = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);
            $kmInicial = $vehiculo->kilometros;

            if (!is_null($kmInicial) && $data['km_final'] < $kmInicial) {
                return response()->json([
                    'errors' => ['km_final' => ["El KM final ({$data['km_final']}) no puede ser menor que el odómetro actual del vehículo ({$kmInicial})."]]
                ], 422);
            }

            $payload = $data;
            $payload['operador_id'] = $operador->id;

            $this->applyDerived($payload, $kmInicial); // ⬅ NO recalcula total

            $carga = new CargaCombustible();
            $carga->forceFill($payload)->save();

            $vehiculo->update(['kilometros' => $payload['km_final']]);

            if (!empty($imagenes)) {
                $this->attachTmpImagesToCarga($carga, $imagenes);
            }

            $carga->loadMissing('vehiculo','operador');
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
