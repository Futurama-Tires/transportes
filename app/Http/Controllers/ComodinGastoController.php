<?php

namespace App\Http\Controllers;

use App\Models\ComodinGasto;
use App\Models\TarjetaComodin;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComodinGastosExport;

class ComodinGastoController extends Controller
{
    /**
     * Listado global de gastos (no anidado).
     * Filtros: ?tarjeta=ID, ?search=texto, ?desde=YYYY-MM-DD, ?hasta=YYYY-MM-DD
     * Orden:   ?sort_by=fecha|monto|concepto|id  ?sort_dir=asc|desc
     */
    public function index(Request $request)
    {
        // Exportar a Excel con filtros/orden actuales (sin paginar)
        if ($request->get('export') === 'xlsx') {
            $filename = 'comodin_gastos_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new ComodinGastosExport($request), $filename);
        }

        $tarjetaId = $request->integer('tarjeta');
        $sortBy  = $request->get('sort_by', 'fecha');
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (!in_array($sortBy, ['fecha','monto','concepto','id'], true)) {
            $sortBy = 'fecha';
        }

        $q = ComodinGasto::with('tarjeta')
            ->when($tarjetaId, fn($qq) => $qq->where('tarjeta_comodin_id', $tarjetaId))
            ->when($request->filled('search'), function ($qq) use ($request) {
                $s = $request->string('search');
                $qq->where('concepto', 'like', "%{$s}%");
            })
            ->when($request->filled('desde'), fn($qq) => $qq->whereDate('fecha', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn($qq) => $qq->whereDate('fecha', '<=', $request->date('hasta')))
            ->when($request->filled('monto_min'), fn($qq) => $qq->where('monto', '>=', (float)$request->get('monto_min')))
            ->when($request->filled('monto_max'), fn($qq) => $qq->where('monto', '<=', (float)$request->get('monto_max')));

        // Orden dinámico
        if ($sortBy === 'fecha') {
            $q->orderBy('fecha', $sortDir)->orderBy('id', $sortDir);
        } else {
            $q->orderBy($sortBy, $sortDir)->orderBy('id', 'desc');
        }

        $gastos   = $q->paginate(15)->withQueryString();
        $tarjetas = TarjetaComodin::orderByDesc('id')->get();

        return view('comodin_gastos.index', compact('gastos', 'tarjetas', 'tarjetaId'));
    }

    /** Form para crear gasto de una tarjeta específica (recurso anidado). */
    public function create(TarjetaComodin $tarjetas_comodin)
    {
        $tarjeta = $tarjetas_comodin;
        return view('comodin_gastos.create', compact('tarjeta'));
    }

    /** Guardar gasto (recurso anidado). */
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

    /** Editar gasto (ruta shallow) */
    public function edit(ComodinGasto $gasto)
    {
        $gasto->load('tarjeta');
        return view('comodin_gastos.edit', compact('gasto'));
    }

    /** Actualizar gasto (ruta shallow) */
    public function update(Request $request, ComodinGasto $gasto)
    {
        $data = $request->validate([
            'fecha'    => ['required', 'date'],
            'concepto' => ['required', 'string', 'max:255'],
            'monto'    => ['required', 'numeric', 'min:0'],
        ]);

        $gasto->update($data);

        return redirect()
            ->route('comodin-gastos.index', ['tarjeta' => $gasto->tarjeta_comodin_id])
            ->with('status', 'Gasto actualizado correctamente.');
    }

    /** Eliminar gasto (ruta shallow) */
    public function destroy(ComodinGasto $gasto)
    {
        $tarjetaId = $gasto->tarjeta_comodin_id;
        $gasto->delete();

        return redirect()
            ->route('comodin-gastos.index', ['tarjeta' => $tarjetaId])
            ->with('status', 'Gasto eliminado.');
    }
}
