<?php

namespace App\Http\Controllers;

use App\Models\PrecioCombustible;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PrecioCombustibleController extends Controller
{
    /**
     * Lista los precios (para una vista sencilla o el modal).
     */
    public function index()
    {
        $precios = PrecioCombustible::query()
            ->orderBy('combustible')
            ->get();

        // Puedes crear luego la vista: resources/views/precios_combustible/index.blade.php
        return view('precios_combustible.index', compact('precios'));
    }

    /**
     * Crea un nuevo registro (por si quieres permitir agregar otro combustible).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'combustible'      => ['required','string','max:20','unique:precios_combustible,combustible'],
            'precio_por_litro' => ['required','numeric','min:0','max:999999.999'],
        ]);

        $row = PrecioCombustible::create($data);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'row' => $row], 201);
        }

        return back()->with('success', 'Precio creado correctamente.');
    }

    /**
     * Actualiza un registro específico por ID.
     */
    public function update(Request $request, PrecioCombustible $precioCombustible)
    {
        $data = $request->validate([
            'combustible'      => ['required','string','max:20', Rule::unique('precios_combustible','combustible')->ignore($precioCombustible->id)],
            'precio_por_litro' => ['required','numeric','min:0','max:999999.999'],
        ]);

        $precioCombustible->update($data);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'row' => $precioCombustible], 200);
        }

        return back()->with('success', 'Precio actualizado correctamente.');
    }

    /**
     * Actualización masiva (ideal para el modal): items = [{combustible, precio_por_litro}, ...]
     */
    public function upsertMany(Request $request)
    {
        $items = $request->validate([
            'items'                       => ['required','array','min:1'],
            'items.*.combustible'         => ['required','string','max:20'],
            'items.*.precio_por_litro'    => ['required','numeric','min:0','max:999999.999'],
            'recalcular_tanques'          => ['sometimes','boolean'], // opcional
        ])['items'];

        foreach ($items as $it) {
            PrecioCombustible::query()->updateOrCreate(
                ['combustible' => $it['combustible']],
                ['precio_por_litro' => $it['precio_por_litro']]
            );
        }

        $updatedTanques = null;
        if ($request->boolean('recalcular_tanques')) {
            $updatedTanques = $this->recalcularTanquesCosto();
        }

        return response()->json([
            'ok' => true,
            'updated_items' => count($items),
            'tanques_recalculados' => $updatedTanques,
        ], 200);
    }

    /**
     * Devuelve precios actuales en formato JSON (útil para poblar el modal).
     */
    public function current()
    {
        $map = PrecioCombustible::query()
            ->orderBy('combustible')
            ->get(['combustible','precio_por_litro'])
            ->map(fn($r) => [
                'combustible'      => $r->combustible,
                'precio_por_litro' => (float) $r->precio_por_litro,
            ])->values();

        return response()->json(['ok' => true, 'data' => $map], 200);
    }

    /**
     * (Opcional) Acción para recalcular costo_tanque_lleno en masa.
     * La puedes llamar manualmente tras cambiar precios.
     */
    public function recalc(Request $request)
    {
        $updated = $this->recalcularTanquesCosto();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'tanques_recalculados' => $updated]);
        }

        return back()->with('success', "Recalculados {$updated} tanques.");
    }

    /**
     * Lógica SQL para recalcular el costo del tanque lleno.
     * Fórmula: costo = (capacidad_litros * max(cantidad_tanques,1)) * precio_por_litro
     */
    protected function recalcularTanquesCosto(): int
    {
        $sql = "
            UPDATE tanques t
            JOIN precios_combustible p
              ON p.combustible = t.tipo_combustible
            SET t.costo_tanque_lleno = ROUND(
                  (COALESCE(t.capacidad_litros,0) * GREATEST(COALESCE(t.cantidad_tanques,1),1))
                  * p.precio_por_litro
                , 2)
        ";

        // affectingStatement devuelve filas afectadas
        return DB::affectingStatement($sql);
    }
}
