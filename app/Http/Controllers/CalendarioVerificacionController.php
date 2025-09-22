<?php

namespace App\Http\Controllers;

use App\Models\CalendarioVerificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CalendarioVerificacionController extends Controller
{

    public function index(Request $r)
    {
        $q = CalendarioVerificacion::query()
            ->when($r->filled('estado'), fn($qq)=>$qq->where('estado', strtoupper($r->estado)))
            ->when($r->filled('terminacion'), fn($qq)=>$qq->where('terminacion', (int) $r->terminacion))
            ->orderBy('estado')->orderBy('terminacion')->orderBy('anio')->orderBy('mes_inicio');

        $items = $q->paginate(20)->withQueryString();
        return view('calendarios.index', compact('items'));
    }

    public function create()
    {
        return view('calendarios.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateCalendario($request);
        CalendarioVerificacion::create($data);
        return redirect()->route('calendarios.index')->with('ok','Regla creada');
    }

    public function edit(CalendarioVerificacion $calendario)
    {
        return view('calendarios.edit', compact('calendario'));
    }

    public function update(Request $request, CalendarioVerificacion $calendario)
    {
        $data = $this->validateCalendario($request, $calendario->id);
        $calendario->update($data);
        return redirect()->route('calendarios.index')->with('ok','Regla actualizada');
    }

    public function destroy(CalendarioVerificacion $calendario)
    {
        $calendario->delete();
        return back()->with('ok','Regla eliminada');
    }

    /**
     * Valida y normaliza la regla (sin FormRequest).
     * También evita solapamientos para (estado, terminacion, anio/null).
     */
    private function validateCalendario(Request $request, ?int $ignoreId = null): array
    {
        // Pre-normalización ligera
        $input = $request->all();
        if (isset($input['estado'])) {
            $input['estado'] = mb_strtoupper(trim($input['estado']));
        }

        $rules = [
            'estado'        => ['required','string','max:50'],
            'terminacion'   => ['required','integer','between:0,9'],
            'mes_inicio'    => ['required','integer','between:1,12'],
            'mes_fin'       => ['required','integer','between:1,12','gte:mes_inicio'],
            'semestre'      => ['nullable','integer','in:1,2'],
            'frecuencia'    => ['required','in:Semestral,Anual'],
            'anio'          => ['nullable','integer','min:2000','max:2100'],
            'vigente_desde' => ['nullable','date'],
            'vigente_hasta' => ['nullable','date','after_or_equal:vigente_desde'],
        ];

        $v = Validator::make($input, $rules);

        // Validación de solapamiento de rangos para mismo estado+terminación+(anio/null)
        $v->after(function ($val) use ($input, $ignoreId) {
            $estado      = $input['estado'] ?? null;
            $terminacion = isset($input['terminacion']) ? (int)$input['terminacion'] : null;
            $anio        = $input['anio'] ?? null;
            $ini         = isset($input['mes_inicio']) ? (int)$input['mes_inicio'] : null;
            $fin         = isset($input['mes_fin']) ? (int)$input['mes_fin'] : null;

            if (!$estado || $terminacion === null || !$ini || !$fin) {
                return;
            }

            $q = CalendarioVerificacion::query()
                ->whereRaw('UPPER(estado) = ?', [$estado])
                ->where('terminacion', $terminacion);

            if ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            }

            if ($anio) { $q->where('anio', $anio); } else { $q->whereNull('anio'); }

            // Overlap: A.start <= B.end && A.end >= B.start
            $q->where('mes_inicio', '<=', $fin)
              ->where('mes_fin', '>=', $ini);

            if ($q->exists()) {
                $val->errors()->add('mes_inicio', 'Ya existe una regla traslapada para ese estado/terminación/año.');
            }
        });

        $data = $v->validate();

        // Normaliza nullables (evita guardar cadenas vacías)
        foreach (['semestre','anio','vigente_desde','vigente_hasta'] as $k) {
            if (!isset($data[$k]) || $data[$k] === '') $data[$k] = null;
        }

        return $data;
    }
}
