<?php

namespace App\Http\Controllers;

use App\Models\ComodinGasto;
use App\Models\TarjetaComodin;
use Illuminate\Http\Request;

class ComodinGastoController extends Controller
{
    /**
     * Listado global de gastos (no anidado).
     * Filtros: ?tarjeta=ID, ?search=texto, ?desde=YYYY-MM-DD, ?hasta=YYYY-MM-DD
     */
    public function index(Request $request)
    {
        $tarjetaId = $request->integer('tarjeta');

        $q = ComodinGasto::with(['tarjeta'])
            ->when($tarjetaId, fn($qq) => $qq->where('tarjeta_comodin_id', $tarjetaId))
            ->when($request->filled('search'), function ($qq) use ($request) {
                $s = $request->string('search');
                $qq->where('concepto', 'like', "%{$s}%");
            })
            ->when($request->filled('desde'), fn($qq) => $qq->whereDate('fecha', '>=', request()->date('desde')))
            ->when($request->filled('hasta'), fn($qq) => $qq->whereDate('fecha', '<=', request()->date('hasta')))
            ->orderByDesc('fecha')->orderByDesc('id');

        $gastos = $q->paginate(15)->withQueryString();
        $tarjetas = TarjetaComodin::orderByDesc('id')->get();

        return view('comodin_gastos.index', compact('gastos', 'tarjetas', 'tarjetaId'));
    }

    /**
     * Form para crear gasto de una tarjeta específica (recurso anidado).
     * GET /tarjetas-comodin/{tarjetas_comodin}/gastos/create
     */
    public function create(TarjetaComodin $tarjetas_comodin)
    {
        $tarjeta = $tarjetas_comodin;
        return view('comodin_gastos.create', compact('tarjeta'));
    }

    /**
     * Guardar gasto (recurso anidado).
     * POST /tarjetas-comodin/{tarjetas_comodin}/gastos
     */
    public function store(Request $request, TarjetaComodin $tarjetas_comodin)
    {
        $tarjeta = $tarjetas_comodin;

        $data = $request->validate([
            'fecha'    => ['required', 'date'],
            'concepto' => ['required', 'string', 'max:255'],
            'monto'    => ['required', 'numeric', 'min:0'],
        ]);

        $gasto = new ComodinGasto($data);
        $gasto->tarjeta_comodin_id = $tarjeta->id;
        $gasto->save();

        return redirect()
            ->route('comodin-gastos.index', ['tarjeta' => $tarjeta->id])
            ->with('status', 'Gasto registrado correctamente.');
    }

    /**
     * Editar gasto (ruta shallow):
     * GET /gastos/{gasto}/edit   -> name: gastos.edit
     */
    public function edit(ComodinGasto $gasto)
    {
        // Cargamos la relación para mostrar last4 en el encabezado si lo necesitas
        $gasto->load('tarjeta');
        return view('comodin_gastos.edit', compact('gasto'));
    }

    /**
     * Actualizar gasto (ruta shallow):
     * PUT /gastos/{gasto}        -> name: gastos.update
     */
    public function update(Request $request, ComodinGasto $gasto)
    {
        $data = $request->validate([
            'fecha'    => ['required', 'date'],
            'concepto' => ['required', 'string', 'max:255'],
            'monto'    => ['required', 'numeric', 'min:0'],
        ]);

        $gasto->update($data);

        // Tras actualizar, regresamos al listado global filtrado por su tarjeta
        return redirect()
            ->route('comodin-gastos.index', ['tarjeta' => $gasto->tarjeta_comodin_id])
            ->with('status', 'Gasto actualizado correctamente.');
    }

    /**
     * Eliminar gasto (ruta shallow):
     * DELETE /gastos/{gasto}     -> name: gastos.destroy
     */
    public function destroy(ComodinGasto $gasto)
    {
        $tarjetaId = $gasto->tarjeta_comodin_id;
        $gasto->delete();

        return redirect()
            ->route('comodin-gastos.index', ['tarjeta' => $tarjetaId])
            ->with('status', 'Gasto eliminado.');
    }
}
