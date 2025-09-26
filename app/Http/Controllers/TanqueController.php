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

    /**
     * Vista del tanque del vehículo (relación 1–1).
     * Si no existe, muestra CTA para crear.
     */
    public function index(Vehiculo $vehiculo)
    {
        // Carga la relación 1–1 y pásala a la vista
        $vehiculo->load('tanque');
        $tanque = $vehiculo->tanque; // null si aún no existe

        return view('tanques.index', compact('vehiculo', 'tanque'));
    }


    public function create(Vehiculo $vehiculo)
    {
        // Si ya existe tanque, redirige a editar (evita 2dos registros)
        if ($vehiculo->tanque) {
            return redirect()
                ->route('vehiculos.tanques.edit', [$vehiculo, $vehiculo->tanque])
                ->with('success', 'El vehículo ya tiene tanque, puedes editarlo aquí.');
        }
        return view('tanques.create', compact('vehiculo'));
    }

    public function store(Request $request, Vehiculo $vehiculo)
    {
        // Evita duplicados por app (en DB ya tienes UNIQUE(vehiculo_id))
        if ($vehiculo->tanque) {
            return redirect()
                ->route('vehiculos.tanques.edit', [$vehiculo, $vehiculo->tanque])
                ->withErrors(['vehiculo_id' => 'Este vehículo ya tiene un tanque registrado.'])
                ->withInput();
        }

        $data = $request->validate([
            'cantidad_tanques'     => ['required','integer','min:1','max:255'],
            'capacidad_litros'     => ['required','numeric','min:0'],
            'rendimiento_estimado' => ['nullable','numeric','min:0'],
            'costo_tanque_lleno'   => ['nullable','numeric','min:0'],
            'tipo_combustible'     => ['required','in:Magna,Diesel,Premium'],
        ]);

        $cap = (float)($data['capacidad_litros'] ?? 0);
        $ren = (float)($data['rendimiento_estimado'] ?? 0);
        $data['km_recorre'] = $cap * $ren;
        $data['vehiculo_id'] = $vehiculo->id;

        $tanque = Tanque::create($data);

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
            'cantidad_tanques'     => ['required','integer','min:1','max:255'],
            'capacidad_litros'     => ['required','numeric','min:0'],
            'rendimiento_estimado' => ['nullable','numeric','min:0'],
            'costo_tanque_lleno'   => ['nullable','numeric','min:0'],
            'tipo_combustible'     => ['required','in:Magna,Diesel,Premium'],
        ]);

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
