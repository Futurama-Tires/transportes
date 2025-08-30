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
    public function index(Request $request)
{
    $query = Verificacion::with(['vehiculo' => function ($q) {
        // Trae solo lo necesario a la tabla
        $q->select('id', 'placa', 'unidad', 'marca', 'propietario');
    }]);

    // --- Búsqueda libre: estado, placa, unidad, marca, propietario
    if ($request->filled('search')) {
        $s = trim($request->string('search'));
        $query->where(function ($q) use ($s) {
            $q->where('estado', 'like', "%{$s}%")
              ->orWhereHas('vehiculo', function ($v) use ($s) {
                  $v->where('placa', 'like', "%{$s}%")
                    ->orWhere('unidad', 'like', "%{$s}%")
                    ->orWhere('marca', 'like', "%{$s}%")
                    ->orWhere('propietario', 'like', "%{$s}%");
              });
        });
    }

    // --- Filtro por rango de fechas (si tu columna es DATE/DATETIME)
    // Acepta ?from=YYYY-MM-DD&to=YYYY-MM-DD
    $from = $request->date('from'); // null si no viene / formato inválido
    $to   = $request->date('to');

    if ($from) {
        $query->whereDate('fecha_verificacion', '>=', $from);
    }
    if ($to) {
        $query->whereDate('fecha_verificacion', '<=', $to);
    }

    // Orden y paginación (server-side)
    $verificaciones = $query
        ->orderByDesc('fecha_verificacion')
        ->paginate(25)
        ->withQueryString(); // conserva search/from/to

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
