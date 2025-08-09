<?php

namespace App\Http\Controllers;

use App\Models\Verificacion;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VerificacionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    /**
     * Listar verificaciones
     */
    public function index()
    {
        $verificaciones = Verificacion::with('vehiculo')
            ->latest('fecha_verificacion')
            ->paginate(10);

        return view('verificaciones.index', compact('verificaciones'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $vehiculos = Vehiculo::orderBy('unidad')->get();
        return view('verificaciones.create', compact('vehiculos'));
    }

    /**
     * Guardar nueva verificación
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Verificacion::create($data);

        return redirect()
            ->route('verificaciones.index')
            ->with('success', 'Verificación registrada correctamente.');
    }

    /**
     * Mostrar detalles de una verificación
     */
    public function show(Verificacion $verificacion)
    {
        return view('verificaciones.show', compact('verificacion'));
    }

    /**
     * Formulario de edición
     */
    public function edit(Verificacion $verificacion)
    {
        $vehiculos = Vehiculo::orderBy('unidad')->get();
        return view('verificaciones.edit', compact('verificacion', 'vehiculos'));
    }

    /**
     * Actualizar verificación
     */
    public function update(Request $request, Verificacion $verificacion)
    {
        $data = $this->validateData($request);
        $verificacion->update($data);

        return redirect()
            ->route('verificaciones.index')
            ->with('success', 'Verificación actualizada correctamente.');
    }

    /**
     * Eliminar verificación
     */
    public function destroy(Verificacion $verificacion)
    {
        $verificacion->delete();

        return redirect()
            ->route('verificaciones.index')
            ->with('success', 'Verificación eliminada correctamente.');
    }

    /**
     * Validar datos
     */
    private function validateData(Request $request)
    {
        return $request->validate([
            'vehiculo_id'        => ['required', 'exists:vehiculos,id'],
            'estado'             => ['required', 'string', 'max:255'],
            'comentarios'        => ['nullable', 'string'],
            'fecha_verificacion' => ['required', 'date'],
        ]);
    }
}
