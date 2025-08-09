<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehiculoController extends Controller
{
    public function __construct()
    {
        // Listar/crear/editar para admin o capturista
        $this->middleware(['auth', 'role:administrador|capturista']);

        // Borrar solo admin
        $this->middleware(['role:administrador'])->only('destroy');
    }

    public function index()
    {
        $vehiculos = Vehiculo::latest('id')->paginate(10);
        return view('vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        return view('vehiculos.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Vehiculo::create($data);

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo creado correctamente.');
    }

    public function show(Vehiculo $vehiculo)
    {
        return view('vehiculos.show', compact('vehiculo'));
    }

    public function edit(Vehiculo $vehiculo)
    {
        return view('vehiculos.edit', compact('vehiculo'));
    }

    public function update(Request $request, Vehiculo $vehiculo)
    {
        $data = $this->validateData($request, $vehiculo->id);
        $vehiculo->update($data);

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $vehiculo->delete();

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo eliminado correctamente.');
    }

    private function validateData(Request $request, $vehiculoId = null): array
    {
        return $request->validate([
            'ubicacion'                 => ['required', 'string', 'max:255'],
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
            'tarjeta_siVale'            => ['nullable', 'string', 'max:255'],
            'nip'                       => ['nullable', 'string', 'max:255'],
            'fec_vencimiento'           => ['nullable', 'string', 'max:255'],
            'vencimiento_t_circulacion' => ['nullable', 'string', 'max:255'],
            'cambio_placas'             => ['nullable', 'string', 'max:255'],
            'poliza_hdi'                => ['nullable', 'string', 'max:255'],
            'rend'                      => ['nullable', 'numeric'],
        ], [
            'serie.unique' => 'La serie ya está registrada.',
            'placa.unique' => 'La placa ya está registrada.',
        ]);
    }
}
