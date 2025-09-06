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
        $query = CargaCombustible::with(['operador', 'vehiculo'])->orderByDesc('fecha');

        // Filtros opcionales
        if ($request->filled('ubicacion')) {
            $query->where('ubicacion', $request->ubicacion);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo_combustible', $request->tipo);
        }
        if ($request->filled('mes')) {
            $query->where('mes', $request->mes);
        }

        $cargas = $query->paginate(15)->withQueryString();

        return view('cargas.index', [
            'cargas'      => $cargas,
            'ubicaciones' => CargaCombustible::UBICACIONES,
        ]);
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
        $data = $this->validateData($request);

        // Calcular derivados
        $this->hydrateDerivedFields($data);

        CargaCombustible::create($data);

        return redirect()
            ->route('cargas.index')
            ->with('success', 'Carga registrada correctamente.');
    }

    public function edit(CargaCombustible $carga)
    {
        return view('cargas.edit', [
            'carga'       => $carga, // <-- ¡IMPORTANTE!
            'operadores'  => Operador::orderBy('nombre')->get(),
            'vehiculos'   => Vehiculo::orderBy('unidad')->get(),
            'ubicaciones' => CargaCombustible::UBICACIONES,
            'tipos'       => ['Magna','Diesel','Premium'],
        ]);
    }

    public function update(Request $request, CargaCombustible $carga)
    {
        $data = $this->validateData($request);
        $this->hydrateDerivedFields($data);
        $carga->update($data);

        return redirect()->route('cargas.index')
            ->with('success', 'Carga actualizada correctamente.');
    }

    public function destroy(\App\Models\CargaCombustible $carga)
{
    // Si usas SoftDeletes y quieres eliminar de verdad, usa forceDelete()
    // $carga->forceDelete();

    $deleted = $carga->delete();

    return redirect()
        ->route('cargas.index')
        ->with('success', $deleted ? 'Carga eliminada correctamente.' : 'No se pudo eliminar la carga.');
}

    // ===================== Helpers =====================

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'ubicacion'       => ['nullable', 'in:' . implode(',', CargaCombustible::UBICACIONES)],
            'fecha'           => ['required', 'date'],
            // 'mes' se llena automáticamente desde 'fecha'
            'precio'          => ['required', 'numeric', 'min:0'],
            'tipo_combustible'=> ['required', 'in:Magna,Diesel,Premium'],
            'litros'          => ['required', 'numeric', 'min:0.001'],
            'custodio'        => ['nullable', 'string', 'max:255'],
            'operador_id'     => ['required', 'exists:operadores,id'],
            'vehiculo_id'     => ['required', 'exists:vehiculos,id'],
            'km_inicial'      => ['nullable', 'integer', 'min:0'],
            'km_final'        => ['nullable', 'integer', 'gte:km_inicial'],
            // 'recorrido', 'rendimiento', 'diferencia', 'total' se calculan
            'destino'         => ['nullable', 'string', 'max:255'],
            'observaciones'   => ['nullable', 'string', 'max:2000'],
        ]);
    }

    protected function hydrateDerivedFields(array &$data): void
    {
        // Mes en español (Enero, Febrero, ...)
        $mes = Carbon::parse($data['fecha'])->locale('es')->translatedFormat('F');
        $data['mes'] = ucfirst($mes);

        // Total = Precio * Litros
        $data['total'] = round($data['precio'] * $data['litros'], 2);

        // Recorrido = km_final - km_inicial (si ambos están)
        $recorrido = (isset($data['km_inicial'], $data['km_final']))
            ? ($data['km_final'] - $data['km_inicial'])
            : null;

        $data['recorrido'] = is_null($recorrido) ? null : (int)$recorrido;

        // Rendimiento = Recorrido / Litros
        $data['rendimiento'] = (!is_null($recorrido) && (float)$data['litros'] > 0)
            ? round($recorrido / (float)$data['litros'], 2)
            : null;

        // Diferencia (Dif $S) = -((E - (M/14)) * C)
        // E = litros, M = recorrido, C = precio
        if (!is_null($recorrido) && isset($data['litros'], $data['precio'])) {
            $data['diferencia'] = round(-(((float)$data['litros'] - ($recorrido / 14)) * (float)$data['precio']), 2);
        } else {
            $data['diferencia'] = null;
        }
    }
}
