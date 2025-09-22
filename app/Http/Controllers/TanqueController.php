<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Tanque;
use Illuminate\Http\Request;

class TanqueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:administrador|capturista']);
    }

    // Lista de tanques del vehículo
    public function index(Vehiculo $vehiculo)
    {
        $tanques = $vehiculo->tanques()
            ->orderByRaw('COALESCE(numero_tanque, 999)')
            ->orderBy('id')
            ->paginate(25)->withQueryString();

        return view('tanques.index', compact('vehiculo','tanques'));
    }

    public function create(Vehiculo $vehiculo)
    {
        return view('tanques.create', compact('vehiculo'));
    }

    public function store(Request $request, Vehiculo $vehiculo)
    {
        $data = $request->validate([
            'numero_tanque'        => ['nullable','integer','min:1','max:255'],
            'capacidad_litros'     => ['required','numeric','min:0'],
            'rendimiento_estimado' => ['nullable','numeric','min:0'],
            'costo_tanque_lleno'   => ['nullable','numeric','min:0'],
            'tipo_combustible'     => ['required','in:Magna,Diesel,Premium'],
        ]);

        $cap = (float)($data['capacidad_litros'] ?? 0);
        $ren = (float)($data['rendimiento_estimado'] ?? 0);
        $data['km_recorre'] = $cap * $ren;
        $data['vehiculo_id'] = $vehiculo->id;

        Tanque::create($data);

        return redirect()->route('vehiculos.tanques.index', $vehiculo)
            ->with('success', 'Tanque creado correctamente.');
    }

    public function edit(Vehiculo $vehiculo, Tanque $tanque)
    {
        if ($tanque->vehiculo_id !== $vehiculo->id) {
            abort(404);
        }
        return view('tanques.edit', compact('vehiculo','tanque'));
    }

    public function update(Request $request, Vehiculo $vehiculo, Tanque $tanque)
    {
        if ($tanque->vehiculo_id !== $vehiculo->id) {
            abort(404);
        }

        $data = $request->validate([
            'numero_tanque'        => ['nullable','integer','min:1','max:255'],
            'capacidad_litros'     => ['required','numeric','min:0'],
            'rendimiento_estimado' => ['nullable','numeric','min:0'],
            'costo_tanque_lleno'   => ['nullable','numeric','min:0'],
            'tipo_combustible'     => ['required','in:Magna,Diesel,Premium'],
        ]);

        if (!empty($data['numero_tanque'])) {
            $exists = Tanque::where('vehiculo_id', $vehiculo->id)
                ->where('numero_tanque', $data['numero_tanque'])
                ->where('id', '!=', $tanque->id)
                ->exists();
            if ($exists) {
                return back()->withErrors(['numero_tanque' => 'El número de tanque ya existe para este vehículo.'])
                             ->withInput();
            }
        }

        $cap = (float)($data['capacidad_litros'] ?? $tanque->capacidad_litros ?? 0);
        $ren = (float)($data['rendimiento_estimado'] ?? $tanque->rendimiento_estimado ?? 0);
        $data['km_recorre'] = $cap * $ren;

        $tanque->update($data);

        return redirect()->route('vehiculos.tanques.index', $vehiculo)
            ->with('success', 'Tanque actualizado correctamente.');
    }

    public function destroy(Vehiculo $vehiculo, Tanque $tanque)
    {
        if ($tanque->vehiculo_id !== $vehiculo->id) {
            abort(404);
        }

        $tanque->delete();

        return redirect()->route('vehiculos.tanques.index', $vehiculo)
            ->with('success', 'Tanque eliminado correctamente.');
    }
}
