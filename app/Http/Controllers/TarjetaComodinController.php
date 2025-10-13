<?php

namespace App\Http\Controllers;

use App\Models\TarjetaComodin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TarjetaComodinController extends Controller
{
    /** Listado con búsqueda/filtros/orden usando scopes del Modelo */
    public function index(Request $request)
    {
        $tarjetas = TarjetaComodin::query()
            ->search($request->input('search'))
            ->ultimos4($request->input('ultimos4'))
            ->tieneNip($request->input('tiene_nip'))
            ->venceFrom($request->input('from'))
            ->venceTo($request->input('to'))
            ->estado($request->input('estado'))
            ->ordenar($request->input('sort_by'), $request->input('sort_dir'))
            ->paginate(12)
            ->withQueryString();

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
            // 4 a 16 dígitos (solo números), único
            'numero_tarjeta'    => [
                'required',
                'regex:/^\d{4,16}$/',
                Rule::unique('tarjetas_comodin', 'numero_tarjeta'),
            ],
            'nip'               => ['nullable', 'string', 'max:255'],
            // Dejamos el parse a los mutators del Modelo: acepta 'YYYY-MM' o 'YYYY-MM-DD'
            'fecha_vencimiento' => ['nullable', 'string', 'max:20'],
            'descripcion'       => ['nullable', 'string', 'max:1000'],
        ], [
            'numero_tarjeta.regex'  => 'El número de tarjeta debe tener entre 4 y 16 dígitos (solo números).',
        ]);

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
            'numero_tarjeta'    => [
                'required',
                'regex:/^\d{4,16}$/',
                Rule::unique('tarjetas_comodin', 'numero_tarjeta')->ignore($tarjeta->id),
            ],
            'nip'               => ['nullable', 'string', 'max:255'],
            'fecha_vencimiento' => ['nullable', 'string', 'max:20'],
            'descripcion'       => ['nullable', 'string', 'max:1000'],
        ], [
            'numero_tarjeta.regex'  => 'El número de tarjeta debe tener entre 4 y 16 dígitos (solo números).',
        ]);

        // Si el campo viene presente pero vacío, el mutator lo convertirá a null;
        // si NO viene en la request y quieres preservar el valor, no toques la clave.
        if (!$request->has('nip')) {
            unset($data['nip']);
        }

        $tarjeta->update($data);

        return redirect()
            ->route('tarjetas-comodin.index')
            ->with('status', 'Tarjeta Comodín actualizada.');
    }

    /** Eliminar tarjeta (considera FK/ON DELETE en la migración de gastos) */
    public function destroy(TarjetaComodin $tarjetas_comodin)
    {
        $tarjetas_comodin->delete();

        return redirect()
            ->route('tarjetas-comodin.index')
            ->with('status', 'Tarjeta Comodín eliminada.');
    }
}
