<?php

namespace App\Http\Controllers;

use App\Models\CargaCombustible;
use App\Models\Operador;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
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

        $vehiculos = Vehiculo::orderBy('unidad')->get(['id','unidad','placa']);
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
            'vehiculos'   => Vehiculo::orderBy('unidad')->get(),
            'ubicaciones' => CargaCombustible::UBICACIONES,
            'tipos'       => ['Magna', 'Diesel', 'Premium'],
        ]);
    }

    public function store(Request $request)
    {
        // === Flujo WEB: operador_id llega desde el formulario ===
        $data = $this->validateData($request);
        $this->hydrateDerivedFields($data);

        $carga = new CargaCombustible();
        $carga->forceFill($data)->save();

        return redirect()->route('cargas.index')
            ->with('success', 'Carga registrada correctamente.');
    }

    public function edit(CargaCombustible $carga)
    {
        return view('cargas.edit', [
            'carga'       => $carga,
            'operadores'  => Operador::orderBy('nombre')->get(),
            'vehiculos'   => Vehiculo::orderBy('unidad')->get(),
            'ubicaciones' => CargaCombustible::UBICACIONES,
            'tipos'       => ['Magna','Diesel','Premium'],
        ]);
    }

    public function update(Request $request, CargaCombustible $carga)
    {
        // === Flujo WEB: operador_id llega desde el formulario ===
        $data = $this->validateData($request);
        $this->hydrateDerivedFields($data);

        $carga->forceFill($data)->save();

        return redirect()->route('cargas.index')
            ->with('success', 'Carga actualizada correctamente.');
    }

    public function destroy(CargaCombustible $carga)
    {
        $deleted = $carga->delete();

        return redirect()->route('cargas.index')
            ->with('success', $deleted ? 'Carga eliminada correctamente.' : 'No se pudo eliminar la carga.');
    }

    // ===================== Helpers (WEB) =====================

    /** Validación para formularios WEB (exige operador_id) */
    protected function validateData(Request $request): array
    {
        return $request->validate([
            'ubicacion'        => ['nullable', 'in:' . implode(',', CargaCombustible::UBICACIONES)],
            'fecha'            => ['required', 'date'],
            'precio'           => ['required', 'numeric', 'min:0'],
            'tipo_combustible' => ['required', 'in:Magna,Diesel,Premium'],
            'litros'           => ['required', 'numeric', 'min:0.001'],
            'custodio'         => ['nullable', 'string', 'max:255'],
            'operador_id'      => ['required', 'exists:operadores,id'],
            'vehiculo_id'      => ['required', 'exists:vehiculos,id'],
            'km_inicial'       => ['nullable', 'integer', 'min:0'],
            'km_final'         => ['nullable', 'integer', 'gte:km_inicial'],
            'destino'          => ['nullable', 'string', 'max:255'],
            'observaciones'    => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /** Calcula mes, total, recorrido, rendimiento y diferencia */
    protected function hydrateDerivedFields(array &$data): void
    {
        $mes = Carbon::parse($data['fecha'])->locale('es')->translatedFormat('F');
        $data['mes'] = ucfirst($mes);

        $data['total'] = round(((float)$data['precio']) * ((float)$data['litros']), 2);

        $recorrido = (isset($data['km_inicial'], $data['km_final']))
            ? ((int)$data['km_final'] - (int)$data['km_inicial'])
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
     * API: crea una carga asociando el operador mediante el usuario autenticado.
     * La app NO debe enviar operador_id.
     */
    public function storeApi(Request $request)
    {
        // Validamos igual que en web pero SIN operador_id
        $data = $request->validate([
            'ubicacion'        => ['nullable', 'in:' . implode(',', CargaCombustible::UBICACIONES)],
            'fecha'            => ['required', 'date'],
            'precio'           => ['required', 'numeric', 'min:0'],
            'tipo_combustible' => ['required', 'in:Magna,Diesel,Premium'],
            'litros'           => ['required', 'numeric', 'min:0.001'],
            'custodio'         => ['nullable', 'string', 'max:255'],
            'vehiculo_id'      => ['required', 'exists:vehiculos,id'],
            'km_inicial'       => ['nullable', 'integer', 'min:0'],
            'km_final'         => ['nullable', 'integer', 'gte:km_inicial'],
            'destino'          => ['nullable', 'string', 'max:255'],
            'observaciones'    => ['nullable', 'string', 'max:2000'],
        ]);

        // Buscamos el operador ligado al usuario autenticado
        $user = $request->user();
        $operador = Operador::where('user_id', $user->id)->first();

        if (!$operador) {
            return response()->json([
                'message' => 'El usuario autenticado no tiene un operador asociado.'
            ], 422);
        }

        // Asignamos operador_id desde el token
        $data['operador_id'] = $operador->id;

        // Calculamos derivados
        $this->hydrateDerivedFields($data);

        // Guardamos
        $carga = new CargaCombustible();
        $carga->forceFill($data)->save();

        // Respondemos con relaciones útiles
        return response()->json(
            $carga->load([
                'vehiculo:id,unidad,placa',
                'operador:id,nombre,apellido_paterno,apellido_materno'
            ]),
            201
        );
    }
}
