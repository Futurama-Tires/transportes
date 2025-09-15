<?php

namespace App\Http\Controllers;

use App\Models\TarjetaComodin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'numero_tarjeta'    => ['required', 'string', 'max:255', 'unique:tarjetas_comodin,numero_tarjeta'],
            // Si quieres forzar 16 dígitos numéricos, cambia por: ['required','digits:16','unique:...']
            'nip'               => ['nullable', 'string', 'max:255'],
            // Si quieres forzar 4 dígitos, usa: ['nullable','digits:4']
            // Aceptamos "YYYY-MM" del <input type="month"> y lo normalizamos a YYYY-MM-01
            'fecha_vencimiento' => ['nullable', 'string', 'max:10'],
        ]);

        if (!empty($data['fecha_vencimiento'])) {
            // Si viene "YYYY-MM" lo convertimos a primer día del mes
            if (preg_match('/^\d{4}-\d{2}$/', $data['fecha_vencimiento'])) {
                $data['fecha_vencimiento'] = $data['fecha_vencimiento'] . '-01';
            }
            // Validar que sea una fecha válida
            $request->merge(['_fv' => $data['fecha_vencimiento']]);
            $request->validate(['_fv' => ['date']]);
            unset($data['_fv']);
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
            'numero_tarjeta'    => [
                'required', 'string', 'max:255',
                Rule::unique('tarjetas_comodin', 'numero_tarjeta')->ignore($tarjeta->id),
                // Si quieres forzar 16 dígitos: 'digits:16',
            ],
            'nip'               => ['nullable', 'string', 'max:255'],
            // Si quieres forzar 4 dígitos: 'digits:4'
            'fecha_vencimiento' => ['nullable', 'string', 'max:10'],
        ]);

        if (!empty($data['fecha_vencimiento'])) {
            if (preg_match('/^\d{4}-\d{2}$/', $data['fecha_vencimiento'])) {
                $data['fecha_vencimiento'] = $data['fecha_vencimiento'] . '-01';
            }
            $request->merge(['_fv' => $data['fecha_vencimiento']]);
            $request->validate(['_fv' => ['date']]);
            unset($data['_fv']);
        }

        // ⛔️ Evitar que un NIP vacío sobreescriba el existente:
        // Si el input 'nip' NO viene lleno, removemos la clave para que no se actualice.
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
