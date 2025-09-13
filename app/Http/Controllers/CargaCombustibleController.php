<?php

namespace App\Http\Controllers;

use App\Models\CargaCombustible;
use App\Models\Operador;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

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

        // Incluye odómetro en el listado para filtros (si quisieras usarlos en offcanvas)
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
            // Traemos kilometros para autorrellenar KM Inicial por JS
            'vehiculos'   => Vehiculo::orderBy('unidad')->get(['id','unidad','placa','kilometros']),
            'ubicaciones' => CargaCombustible::UBICACIONES,
            'tipos'       => CargaCombustible::TIPOS_COMBUSTIBLE,
        ]);
    }

    public function store(Request $request)
    {
        // Validación base (NO aceptamos km_inicial del request: es derivado del odómetro)
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
            // Bloqueamos el vehículo para lectura consistente del odómetro
            $vehiculo = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);
            $kmInicial = $vehiculo->kilometros; // puede ser null si no hay historial

            // Regla: si hay odómetro previo, km_final debe ser >= km_inicial
            if (!is_null($kmInicial) && $data['km_final'] < $kmInicial) {
                throw ValidationException::withMessages([
                    'km_final' => "El KM final ({$data['km_final']}) no puede ser menor que el odómetro actual del vehículo ({$kmInicial}).",
                ]);
            }

            // Derivados
            $this->applyDerived($data, $kmInicial);

            // Guardamos la carga
            $carga = new CargaCombustible();
            $carga->forceFill($data)->save();

            // Actualizamos odómetro del vehículo con el KM final de esta carga
            $vehiculo->update(['kilometros' => $data['km_final']]);

            return redirect()->route('cargas.index')
                ->with('success', 'Carga registrada y odómetro del vehículo actualizado.');
        });
    }

    public function edit(CargaCombustible $carga)
    {
        return view('cargas.edit', [
            'carga'       => $carga,
            'operadores'  => Operador::orderBy('nombre')->get(),
            // Incluye odómetro por si deseas usarlo en la UI
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
            // Si cambia el vehículo, el cálculo de km_inicial se basa en la carga previa del NUEVO vehículo
            $vehiculo = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);

            // Carga previa (por fecha e id) del mismo vehículo para definir km_inicial lógico
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

            // Si hay odómetro lógico previo, validar km_final
            if (!is_null($kmInicial) && $data['km_final'] < $kmInicial) {
                throw ValidationException::withMessages([
                    'km_final' => "El KM final ({$data['km_final']}) no puede ser menor que el KM final de la carga previa ({$kmInicial}).",
                ]);
            }

            // Derivados
            $this->applyDerived($data, $kmInicial);

            // Guardamos cambios de la carga
            $carga->forceFill($data)->save();

            // Si esta carga quedó como la más reciente del vehículo, sincroniza odómetro
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

            // ¿Era la más reciente?
            $esUltima = CargaCombustible::where('vehiculo_id', $vehiculoId)
                ->orderBy('fecha','desc')->orderBy('id','desc')->value('id') === $carga->id;

            $deleted = $carga->delete();

            // Re‐sincroniza odómetro si borraste la última
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

    /**
     * Aplica campos derivados a $data y fija km_inicial desde $kmInicial
     */
    protected function applyDerived(array &$data, ?int $kmInicial): void
    {
        // Mes en español capitalizado (Enero, Febrero, …)
        $data['mes'] = ucfirst(Carbon::parse($data['fecha'])->locale('es')->translatedFormat('F'));

        // Total = precio * litros
        $data['total'] = round(((float)$data['precio']) * ((float)$data['litros']), 2);

        // Fijamos km_inicial desde odómetro/carga previa
        $data['km_inicial'] = $kmInicial;

        // Recorrido / Rendimiento
        $recorrido = (!is_null($kmInicial) && isset($data['km_final']))
            ? max(0, (int)$data['km_final'] - (int)$kmInicial)
            : null;

        $data['recorrido'] = is_null($recorrido) ? null : (int)$recorrido;

        $data['rendimiento'] = (!is_null($recorrido) && (float)$data['litros'] > 0)
            ? round($recorrido / (float)$data['litros'], 2)
            : null;

        // Diferencia (tu fórmula base de referencia con factor 14 km/L)
        if (!is_null($recorrido) && isset($data['litros'], $data['precio'])) {
            $data['diferencia'] = round(-(((float)$data['litros'] - ($recorrido / 14)) * (float)$data['precio']), 2);
        } else {
            $data['diferencia'] = null;
        }
    }

    // ===================== API MÓVIL =====================

    /**
     * API: crea una carga asociando el operador mediante el usuario autenticado.
     * La app NO debe enviar operador_id.
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
        ]);

        $user = $request->user();
        $operador = Operador::where('user_id', $user->id)->first();

        if (!$operador) {
            return response()->json([
                'message' => 'El usuario autenticado no tiene un operador asociado.'
            ], 422);
        }

        return DB::transaction(function () use ($data, $operador) {
            $vehiculo = Vehiculo::lockForUpdate()->findOrFail($data['vehiculo_id']);
            $kmInicial = $vehiculo->kilometros;

            if (!is_null($kmInicial) && $data['km_final'] < $kmInicial) {
                return response()->json([
                    'errors' => ['km_final' => ["El KM final ({$data['km_final']}) no puede ser menor que el odómetro actual del vehículo ({$kmInicial})."]]
                ], 422);
            }

            $data['operador_id'] = $operador->id;

            $this->applyDerived($data, $kmInicial);

            $carga = new CargaCombustible();
            $carga->forceFill($data)->save();

            // Actualizamos odómetro del vehículo
            $vehiculo->update(['kilometros' => $data['km_final']]);

            return response()->json(
                $carga->load([
                    'vehiculo:id,unidad,placa',
                    'operador:id,nombre,apellido_paterno,apellido_materno'
                ]),
                201
            );
        });
    }
}
