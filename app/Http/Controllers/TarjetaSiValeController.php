<?php

namespace App\Http\Controllers;

use App\Models\TarjetaSiVale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TarjetaSiValeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    public function index()
    {
        $tarjetas = TarjetaSiVale::latest()->paginate(10);
        return view('tarjetas.index', compact('tarjetas'));
    }

    public function create()
    {
        return view('tarjetas.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        TarjetaSiVale::create($data);

        return redirect()
            ->route('tarjetas.index')
            ->with('success', 'Tarjeta SiVale creada correctamente.');
    }

    /**
     * Muestra la tarjeta + sus vehículos y sus cargas (paginadas) con totales.
     */
    public function show(TarjetaSiVale $tarjeta, Request $request)
    {
        // Cargamos vehículos asociados (por si quieres mostrarlos)
        $tarjeta->load('vehiculos');

        // Query base de cargas (relación hasManyThrough definida en el modelo TarjetaSiVale)
        $cargasQuery = $tarjeta->cargas()
            ->with(['vehiculo:id,unidad,placa,serie'])
            // ajusta el orden según tus columnas (fecha o created_at)
            ->orderByDesc('fecha');

        // Totales (se hacen sobre la misma query)
        $stats = [
            'total_cargas'  => (clone $cargasQuery)->count(),
            'total_litros'  => (clone $cargasQuery)->sum('litros'),
            'total_gastado' => (clone $cargasQuery)->sum('total'),
        ];

        // Paginación
        $perPage = (int) $request->get('per_page', 15);
        $cargas  = $cargasQuery->paginate($perPage)->withQueryString();

        return view('tarjetas.show', compact('tarjeta', 'cargas', 'stats'));
    }

    public function edit(TarjetaSiVale $tarjeta)
    {
        return view('tarjetas.edit', compact('tarjeta'));
    }

    public function update(Request $request, TarjetaSiVale $tarjeta)
    {
        $data = $this->validateData($request, $tarjeta->id);
        $tarjeta->update($data);

        return redirect()
            ->route('tarjetas.index')
            ->with('success', 'Tarjeta SiVale actualizada correctamente.');
    }

    public function destroy(TarjetaSiVale $tarjeta)
    {
        $tarjeta->delete();

        return redirect()
            ->route('tarjetas.index')
            ->with('success', 'Tarjeta SiVale eliminada correctamente.');
    }

    /**
     * Valida y normaliza datos. Usa dinámicamente el nombre real de la tabla del modelo
     * para evitar desajustes si cambias $table en el modelo.
     */
    private function validateData(Request $request, $id = null)
    {
        $table = (new TarjetaSiVale())->getTable();

        $data = $request->validate([
            'numero_tarjeta'    => ['required', 'digits:16', Rule::unique($table)->ignore($id)],
            'nip'               => ['nullable', 'digits:4'],
            'fecha_vencimiento' => ['required', 'date_format:Y-m'],
        ], [
            'numero_tarjeta.digits'         => 'El número de tarjeta debe tener exactamente 16 dígitos.',
            'nip.digits'                    => 'El NIP debe tener exactamente 4 dígitos.',
            'fecha_vencimiento.date_format' => 'El formato de fecha debe ser Mes/Año (YYYY-MM).',
        ]);

        // Convertimos YYYY-MM → YYYY-MM-01 para guardarlo en DATE
        if (!empty($data['fecha_vencimiento'])) {
            $data['fecha_vencimiento'] = $data['fecha_vencimiento'] . '-01';
        }

        return $data;
    }
}
