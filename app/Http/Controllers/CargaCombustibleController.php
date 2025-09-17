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
use Illuminate\Support\Arr;
use App\Notifications\NuevaCarga;
use Illuminate\Support\Facades\Notification;

class CargaCombustibleController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'vehiculo_id', 'operador_id',
            'ubicacion', 'tipo_combustible',
            'from', 'to',
            'litros_min','litros_max',
            'precio_min','precio_max',
            'total_min','total_max',
            'rend_min','rend_max',
            'km_ini_min','km_ini_max',
            'km_fin_min','km_fin_max',
            'destino','custodio',
            'sort_by','sort_dir',
        ]);

        $vehiculos = Vehiculo::orderBy('unidad')->get(['id','unidad','placa','kilometros']);
        $operadores = Operador::select('id','nombre','apellido_paterno','apellido_materno')
            ->orderBy('nombre')->orderBy('apellido_paterno')->get();

        $ubicaciones = CargaCombustible::UBICACIONES;
        $tipos       = CargaCombustible::TIPOS_COMBUSTIBLE;

        $cargas = CargaCombustible::query()
            ->with(['vehiculo','operador'])
            ->filter($filters)
            ->paginate(25)
            ->withQueryString();

        return view('cargas.index', compact('cargas','vehiculos','operadores','ubicaciones','tipos'));
    }

    public function create()
    {
        return view('cargas.create', [
            'carga'       => new CargaCombustible(),
            'operadores'  => Operador::orderBy('nombre')->get(),
            'vehiculos'   => Vehiculo::orderBy('unidad')->get(['id','unidad','placa','kilometros']),
            'ubicaciones' => CargaCombustible::UBICACIONES,
            'tipos'       => CargaCombustible::TIPOS_COMBUSTIBLE,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ubicacion'        => ['nullable', 'in:' . implode(',', CargaCombustible::UBICACIONES)],
            'fecha'            => ['required', 'date'],
            'precio'           => ['required', 'numeric', 'min:0'],
            'tipo_combustible' => ['required', 'in:Magna,Diesel,Premium'],
            'litros'           => ['required', 'numeric', 'min:0.001'],
            'custodio'         => ['nullable', 'string', 'max:255'],
            'operador_id'      => ['required', 'exists:operadores,id'],
            'vehiculo_id'      => ['required', 'exists:vehiculos,id'],
            'km_final'         => ['required', 'integer', 'min:0'],
            'destino'          => ['nullable', 'string', 'max:255'],
            'observaciones'    => ['nullable', 'string', 'max:2000'],
        ]);

        return DB::transaction(function () use ($data) {
            $vehiculo = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);
            $kmInicial = $vehiculo->kilometros;

            if (!is_null($kmInicial) && $data['km_final'] < $kmInicial) {
                throw ValidationException::withMessages([
                    'km_final' => "El KM final ({$data['km_final']}) no puede ser menor que el odómetro actual del vehículo ({$kmInicial}).",
                ]);
            }

            $this->applyDerived($data, $kmInicial);

            $carga = new CargaCombustible();
            $carga->forceFill($data)->save();

            $vehiculo->update(['kilometros' => $data['km_final']]);

            // 🔔 Notificar después del commit
            $carga->loadMissing('vehiculo','operador'); // opcional
            DB::afterCommit(function () use ($carga) {
                $destinatarios = User::role(['administrador','capturista'], 'web')
                    // ->whereKeyNot(auth()->id()) // descomenta si no quieres notificar al que registró
                    ->get();

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
            'carga'       => $carga,
            'operadores'  => Operador::orderBy('nombre')->get(),
            'vehiculos'   => Vehiculo::orderBy('unidad')->get(['id','unidad','placa','kilometros']),
            'ubicaciones' => CargaCombustible::UBICACIONES,
            'tipos'       => CargaCombustible::TIPOS_COMBUSTIBLE,
        ]);
    }

    public function update(Request $request, CargaCombustible $carga)
    {
        $data = $request->validate([
            'ubicacion'        => ['nullable', 'in:' . implode(',', CargaCombustible::UBICACIONES)],
            'fecha'            => ['required', 'date'],
            'precio'           => ['required', 'numeric', 'min:0'],
            'tipo_combustible' => ['required', 'in:Magna,Diesel,Premium'],
            'litros'           => ['required', 'numeric', 'min:0.001'],
            'custodio'         => ['nullable', 'string', 'max:255'],
            'operador_id'      => ['required', 'exists:operadores,id'],
            'vehiculo_id'      => ['required', 'exists:vehiculos,id'],
            'km_final'         => ['required', 'integer', 'min:0'],
            'destino'          => ['nullable', 'string', 'max:255'],
            'observaciones'    => ['nullable', 'string', 'max:2000'],
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
                ->orderBy('fecha','desc')->orderBy('id','desc')->first();

            $kmInicial = $previa?->km_final;

            if (!is_null($kmInicial) && $data['km_final'] < $kmInicial) {
                throw ValidationException::withMessages([
                    'km_final' => "El KM final ({$data['km_final']}) no puede ser menor que el KM final de la carga previa ({$kmInicial}).",
                ]);
            }

            $this->applyDerived($data, $kmInicial);

            $carga->forceFill($data)->save();

            $ultima = CargaCombustible::where('vehiculo_id', $vehiculo->id)
                ->orderBy('fecha','desc')->orderBy('id','desc')->first();

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

    // ===================== Helpers =====================

    protected function applyDerived(array &$data, ?int $kmInicial): void
    {
        $data['mes'] = ucfirst(Carbon::parse($data['fecha'])->locale('es')->translatedFormat('F'));
        $data['total'] = round(((float)$data['precio']) * ((float)$data['litros']), 2);

        $data['km_inicial'] = $kmInicial;

        $recorrido = (!is_null($kmInicial) && isset($data['km_final']))
            ? max(0, (int)$data['km_final'] - (int)$kmInicial)
            : null;

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

    // ===================== API MÓVIL =====================

    /**
     * API móvil: crea carga y, si vienen imágenes temporales, las anexa (tabla carga_fotos).
     * Entrada opcional:
     *   - imagenes: [{tipo: 'ticket|voucher|odometro|extra', tmp_path: 'tmp/ocr/...'}]
     */
    public function storeApi(Request $request)
    {
        $data = $request->validate([
            'ubicacion'        => ['nullable', 'in:' . implode(',', CargaCombustible::UBICACIONES)],
            'fecha'            => ['required', 'date'],
            'precio'           => ['required', 'numeric', 'min:0'],
            'tipo_combustible' => ['required', 'in:Magna,Diesel,Premium'],
            'litros'           => ['required', 'numeric', 'min:0.001'],
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
            return response()->json([
                'message' => 'El usuario autenticado no tiene un operador asociado.'
            ], 422);
        }

        return DB::transaction(function () use ($data, $operador, $imagenes) {
            $vehiculo = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);
            $kmInicial = $vehiculo->kilometros;

            if (!is_null($kmInicial) && $data['km_final'] < $kmInicial) {
                return response()->json([
                    'errors' => ['km_final' => ["El KM final ({$data['km_final']}) no puede ser menor que el odómetro actual del vehículo ({$kmInicial})."]]
                ], 422);
            }

            $payload = $data;
            $payload['operador_id'] = $operador->id;

            $this->applyDerived($payload, $kmInicial);

            $carga = new CargaCombustible();
            $carga->forceFill($payload)->save();

            $vehiculo->update(['kilometros' => $payload['km_final']]);

            if (!empty($imagenes)) {
                $this->attachTmpImagesToCarga($carga, $imagenes);
            }

            // 🔔 Notificar después del commit
            $carga->loadMissing('vehiculo','operador'); // opcional
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
     * Mueve archivos desde /public/tmp/ocr/... a /public/cargas/{carga_id}/ y crea registros en carga_fotos.
     * $imagenes = [['tipo'=>'ticket','tmp_path'=>'tmp/ocr/2025-09/xxx.jpg'], ...]
     */
    protected function attachTmpImagesToCarga(CargaCombustible $carga, array $imagenes): void
    {
        $disk = Storage::disk('public');
        $baseDir = "cargas/{$carga->id}";
        if (!$disk->exists($baseDir)) {
            $disk->makeDirectory($baseDir);
        }

        foreach ($imagenes as $img) {
            $tmp = $img['tmp_path'] ?? null;
            $tipo = $img['tipo'] ?? CargaFoto::EXTRA;

            if (!$tmp || !is_string($tmp)) {
                continue;
            }
            if (!str_starts_with($tmp, 'tmp/ocr/')) {
                continue;
            }
            if (!$disk->exists($tmp)) {
                continue;
            }

            $ext = pathinfo($tmp, PATHINFO_EXTENSION) ?: 'jpg';
            $name = ($tipo ?: 'extra') . '-' . now()->format('Ymd-His') . '-' . Str::random(6) . '.' . $ext;
            $dest = $baseDir . '/' . $name;

            if (!$disk->move($tmp, $dest)) {
                throw ValidationException::withMessages(['imagenes' => "No se pudo mover la imagen temporal {$tmp}."]);
            }

            $mime = $disk->mimeType($dest);
            $size = $disk->size($dest);

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
