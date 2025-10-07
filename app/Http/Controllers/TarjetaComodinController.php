<?php

namespace App\Http\Controllers;

use App\Models\TarjetaComodin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TarjetaComodinController extends Controller
{
    /** Listado con búsqueda por número de tarjeta */
    public function index(Request $request)
    {
        $q = TarjetaComodin::query()
            ->when($request->filled('search'), function ($qq) use ($request) {
                $s = $request->string('search');
                $qq->where('numero_tarjeta', 'like', "%{$s}%");
            })
            ->orderByDesc('id');

        $tarjetas = $q->paginate(12)->withQueryString();

        return view('tarjetas_comodin.index', compact('tarjetas'));
    }

    /** Form de creación */
    public function create()
    {
        return view('tarjetas_comodin.create');
    }

    /** Guardar tarjeta */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Cambiado: 4 a 16 dígitos (solo números)
            'numero_tarjeta'    => [
                'required',
                'regex:/^\d{4,16}$/',
                Rule::unique('tarjetas_comodin', 'numero_tarjeta'),
            ],
            'nip'               => ['nullable', 'string', 'max:255'],
            // Cambiado: aceptar 'YYYY-MM' y guardar último día del mes
            'fecha_vencimiento' => ['nullable', 'date_format:Y-m'],
            // Nuevo campo
            'descripcion'       => ['nullable', 'string', 'max:1000'],
        ], [
            'numero_tarjeta.regex'         => 'El número de tarjeta debe tener entre 4 y 16 dígitos (solo números).',
            'fecha_vencimiento.date_format'=> 'El formato de fecha debe ser Mes/Año (YYYY-MM).',
        ]);

        if (!empty($data['fecha_vencimiento'])) {
            $data['fecha_vencimiento'] = Carbon::createFromFormat('Y-m', $data['fecha_vencimiento'])
                ->endOfMonth()
                ->format('Y-m-d');
        }

        TarjetaComodin::create($data);

        return redirect()
            ->route('tarjetas-comodin.index')
            ->with('status', 'Tarjeta Comodín creada correctamente.');
    }

    /** Form de edición */
    public function edit(TarjetaComodin $tarjetas_comodin)
    {
        $tarjeta = $tarjetas_comodin;
        return view('tarjetas_comodin.edit', compact('tarjeta'));
    }

    /** Actualizar tarjeta */
    public function update(Request $request, TarjetaComodin $tarjetas_comodin)
    {
        $tarjeta = $tarjetas_comodin;

        $data = $request->validate([
            // Cambiado: 4 a 16 dígitos (solo números)
            'numero_tarjeta'    => [
                'required',
                'regex:/^\d{4,16}$/',
                Rule::unique('tarjetas_comodin', 'numero_tarjeta')->ignore($tarjeta->id),
            ],
            'nip'               => ['nullable', 'string', 'max:255'],
            // Cambiado: aceptar 'YYYY-MM' y guardar último día del mes
            'fecha_vencimiento' => ['nullable', 'date_format:Y-m'],
            // Nuevo campo
            'descripcion'       => ['nullable', 'string', 'max:1000'],
        ], [
            'numero_tarjeta.regex'         => 'El número de tarjeta debe tener entre 4 y 16 dígitos (solo números).',
            'fecha_vencimiento.date_format'=> 'El formato de fecha debe ser Mes/Año (YYYY-MM).',
        ]);

        if (!empty($data['fecha_vencimiento'])) {
            $data['fecha_vencimiento'] = Carbon::createFromFormat('Y-m', $data['fecha_vencimiento'])
                ->endOfMonth()
                ->format('Y-m-d');
        }

        // Evitar sobreescribir NIP con vacío
        if (!$request->filled('nip')) {
            unset($data['nip']);
        }

        $tarjeta->update($data);

        return redirect()
            ->route('tarjetas-comodin.index')
            ->with('status', 'Tarjeta Comodín actualizada.');
    }

    /** Eliminar tarjeta (cascade borra sus gastos) */
    public function destroy(TarjetaComodin $tarjetas_comodin)
    {
        $tarjetas_comodin->delete();

        return redirect()
            ->route('tarjetas-comodin.index')
            ->with('status', 'Tarjeta Comodín eliminada.');
    }
}
