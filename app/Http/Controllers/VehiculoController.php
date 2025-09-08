<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\TarjetaSiVale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehiculoController extends Controller
{
    public function __construct()
    {
        // Listar/crear/editar para admin o capturista
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    /** Listado con filtros y paginación. */
    public function index(Request $request)
    {
        $vehiculos = Vehiculo::with('tarjetaSiVale')
            ->filter($request->all())
            ->sort($request->get('sort_by'), $request->get('sort_dir'))
            ->paginate(25)
            ->withQueryString();

        // Datos para filtros (opcionales)
        $ubicaciones = Vehiculo::select('ubicacion')->whereNotNull('ubicacion')->distinct()->orderBy('ubicacion')->pluck('ubicacion');
        $marcas      = Vehiculo::select('marca')->whereNotNull('marca')->distinct()->orderBy('marca')->pluck('marca');
        $estados     = Vehiculo::select('estado')->whereNotNull('estado')->distinct()->orderBy('estado')->pluck('estado');
        $anios       = Vehiculo::select('anio')->whereNotNull('anio')->distinct()->orderByDesc('anio')->pluck('anio');
        $tarjetas    = class_exists(TarjetaSiVale::class) ? TarjetaSiVale::select('id')->orderBy('id')->get() : collect();

        return view('vehiculos.index', compact('vehiculos', 'ubicaciones', 'marcas', 'estados', 'anios', 'tarjetas'));
    }

    public function create()
    {
        $tarjetas = class_exists(TarjetaSiVale::class) ? TarjetaSiVale::orderBy('id')->get() : collect();
        return view('vehiculos.create', compact('tarjetas'));
    }

    public function store(Request $request)
    {
        $data = $this->validateVehiculo($request);
        Vehiculo::create($data);

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo creado correctamente.');
    }

    public function show(Vehiculo $vehiculo)
    {
        $vehiculo->load('tarjetaSiVale');
        return view('vehiculos.show', compact('vehiculo'));
    }

    public function edit(Vehiculo $vehiculo)
    {
        $tarjetas = class_exists(TarjetaSiVale::class) ? TarjetaSiVale::orderBy('id')->get() : collect();
        return view('vehiculos.edit', compact('vehiculo', 'tarjetas'));
    }

    public function update(Request $request, Vehiculo $vehiculo)
    {
        $data = $this->validateVehiculo($request, $vehiculo->id);
        $vehiculo->update($data);

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $vehiculo->delete();

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo eliminado correctamente.');
    }

    private function validateVehiculo(Request $request, $vehiculoId = null): array
    {
        return $request->validate([
            'ubicacion'                 => ['required', Rule::in(['CVC', 'IXT', 'QRO', 'VALL', 'GDL'])],
            'propietario'               => ['required', 'string', 'max:255'],
            'unidad'                    => ['required', 'string', 'max:255'],
            'marca'                     => ['nullable', 'string', 'max:255'],
            'anio'                      => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'serie'                     => [
                'required', 'string', 'max:255',
                Rule::unique('vehiculos', 'serie')->ignore($vehiculoId),
            ],
            'motor'                     => ['nullable', 'string', 'max:255'],
            'placa'                     => [
                'nullable', 'string', 'max:255',
                Rule::unique('vehiculos', 'placa')->ignore($vehiculoId),
            ],
            'estado'                    => ['nullable', 'string', 'max:255'],
            // Usa el nombre de tabla que tengas realmente. Conservo el tuyo:
            'tarjeta_si_vale_id'        => ['nullable', 'exists:tarjetasSiVale,id'],
            'nip'                       => ['nullable', 'string', 'max:255'],
            'fec_vencimiento'           => ['nullable', 'string', 'max:255'],
            'vencimiento_t_circulacion' => ['nullable', 'string', 'max:255'],
            'cambio_placas'             => ['nullable', 'string', 'max:255'],
            'poliza_hdi'                => ['nullable', 'string', 'max:255'],
            'rend'                      => ['nullable', 'numeric'],
        ], [
            'serie.unique' => 'La serie ya está registrada.',
            'placa.unique' => 'La placa ya está registrada.',
            'tarjeta_si_vale_id.exists' => 'La tarjeta seleccionada no es válida.'
        ]);
    }
}
