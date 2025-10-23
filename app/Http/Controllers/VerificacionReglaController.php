<?php

namespace App\Http\Controllers;

use App\Models\VerificacionRegla;
use App\Models\CalendarioVerificacion;
use App\Models\VerificacionReglaEstado;
use App\Models\VerificacionReglaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VerificacionReglaController extends Controller
{
    /* ===== Catálogos ===== */
    protected function catalogoEstados(): array
    {
        return [
            'Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas','Chihuahua',
            'Ciudad de México','Coahuila','Colima','Durango','Guanajuato','Guerrero','Hidalgo','Jalisco',
            'México','Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla','Querétaro','Quintana Roo',
            'San Luis Potosí','Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas'
        ];
    }

    protected function mesesES(): array
    {
        return [
            1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
            7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
        ];
    }

    protected function normalizeEstado(?string $s): string
    {
        $norm = $s ? Str::of($s)->ascii()->upper()->replaceMatches('/\s+/', ' ')->trim() : '';
        $val  = (string) $norm;
        if (in_array($val, ['ESTADO DE MEXICO','MEXICO','EDO MEXICO','EDO. MEX','E DOMEX'], true)) {
            return 'EDO MEX';
        }
        return $val;
    }

    /* ===== UI CRUD ===== */
    public function index()
    {
        $reglas = VerificacionRegla::withCount('periodos')->orderByDesc('created_at')->paginate(15);
        return view('verificacion_reglas.index', ['reglas' => $reglas]);
    }

    public function create()
    {
        // Defaults para precargar la tabla de terminaciones:
        $defaultsSemestral = $this->defaultSemestralDetalles(); // tipo CAMe por bimestres
        $defaultsAnual     = $this->defaultAnualDetalles();     // estilo Jalisco (1→Ene–Feb, 2→Feb–Mar, ...)

        return view('verificacion_reglas.create', [
            'catalogoEstados'  => $this->catalogoEstados(),
            'meses'            => $this->mesesES(),
            'defaultsSemestral'=> $defaultsSemestral,
            'defaultsAnual'    => $defaultsAnual,
            'anioDefault'      => now()->year,
        ]);
    }

    public function edit(VerificacionRegla $verificacion_regla)
    {
        return view('verificacion_reglas.edit', [
            'regla'           => $verificacion_regla,
            'catalogoEstados' => $this->catalogoEstados(),
            'meses'           => $this->mesesES(),
        ]);
    }

    public function generarForm(VerificacionRegla $verificacion_regla)
    {
        // (Opcional/legacy) ya no se requiere con reconciliación automática
        return view('verificacion_reglas.generar', ['regla' => $verificacion_regla]);
    }

    /* ===== Store (crea regla y SINCRONIZA automáticamente el año capturado) ===== */
    public function store(Request $request)
    {
        // Validación base de la regla + menú flexible
        $data = $request->validate([
            'nombre'          => ['required', 'string', 'max:255'],
            'version'         => ['nullable', 'string', 'max:50'],
            'frecuencia'      => ['required', Rule::in(['Semestral','Anual'])],
            'notas'           => ['nullable','string'],

            // Menú:
            'anio'            => ['required','integer','min:2000','max:2999'],
            'estados'         => ['required','array','min:1'],
            'estados.*'       => ['string','max:100'],

            // Detalles (tabla de terminaciones → meses)
            'detalles'        => ['required','array'],
        ]);

        $anio = (int) $data['anio'];
        $regla = null;

        DB::transaction(function () use ($data, $anio, &$regla) {
            // Normaliza estados seleccionados
            $estadosOriginales = $data['estados'];
            $estadosNorm = array_values(array_unique(array_map(
                fn($e) => $this->normalizeEstado($e),
                $estadosOriginales
            )));

            // 0) Validación extra de disponibilidad (evita choques por año+estado)
            $choquesPivot = VerificacionReglaEstado::query()
                ->where('anio', $anio)
                ->whereIn('estado', $estadosNorm)
                ->exists();

            $choquesCal = DB::table('calendario_verificacion')
                ->where('anio', $anio)
                ->whereIn('estado', $estadosNorm)
                ->exists();

            if ($choquesPivot || $choquesCal) {
                throw ValidationException::withMessages([
                    'estados' => 'Uno o más estados ya están asignados en '.$anio.'. Elige otros estados o cambia el año.',
                ]);
            }

            // 1) Crear la regla
            $regla = VerificacionRegla::create([
                'nombre'          => $data['nombre'],
                'version'         => $data['version'] ?? null,
                'status'          => 'published',
                'vigencia_inicio' => Carbon::create($anio, 1, 1)->toDateString(),
                'vigencia_fin'    => Carbon::create($anio, 12, 31)->toDateString(),
                'frecuencia'      => $data['frecuencia'],
                'estados'         => [], // dejamos vacío (usaremos tabla por año)
                'notas'           => $data['notas'] ?? null,
            ]);

            // 2) Guardar estados por AÑO
            foreach ($estadosOriginales as $label) {
                VerificacionReglaEstado::create([
                    'regla_id' => $regla->id,
                    'anio'     => $anio,
                    'estado'   => $label, // normaliza en el mutator del modelo
                ]);
            }

            // 3) Guardar DETALLES (validando rango 1..12, sin cruce de año y filtrando semestres válidos por frecuencia)
            $allowedSemestres = ($data['frecuencia'] === 'Anual') ? [0] : [1,2];

            $seen = [];
            foreach ($data['detalles'] as $terminacionStr => $porSemestre) {
                $terminacion = (int) $terminacionStr;
                if ($terminacion < 0 || $terminacion > 9) {
                    throw ValidationException::withMessages([
                        'detalles' => 'Terminación inválida. Debe ser 0–9.',
                    ]);
                }

                foreach ($porSemestre as $sem => $mm) {
                    $semestre = (int) $sem; // 0 (anual), 1 o 2 (semestral)

                    if (!in_array($semestre, $allowedSemestres, true)) {
                        continue;
                    }

                    $mi = (int) ($mm['mes_inicio'] ?? 0);
                    $mf = (int) ($mm['mes_fin'] ?? 0);

                    if ($mi < 1 || $mi > 12 || $mf < 1 || $mf > 12) {
                        continue; // ignora filas malformadas
                    }
                    if ($mi > $mf) {
                        throw ValidationException::withMessages([
                            "detalles.$terminacion.$sem.mes_inicio" => 'mes_inicio no puede ser mayor a mes_fin (no se admite cruce de año).',
                        ]);
                    }

                    $k = $terminacion.'|'.$semestre.'|'.$mi.'|'.$mf;
                    if (isset($seen[$k])) continue;
                    $seen[$k] = true;

                    VerificacionReglaDetalle::create([
                        'regla_id'   => $regla->id,
                        'frecuencia' => $data['frecuencia'],
                        'terminacion'=> $terminacion,
                        'semestre'   => $semestre,
                        'mes_inicio' => $mi,
                        'mes_fin'    => $mf,
                    ]);
                }
            }
        });

        // 4) ⚡️ Reconciliación automática del año capturado
        $stats = $this->reconciliarPeriodos($regla, $anio);

        return redirect()->route('verificacion-reglas.index')
            ->with('success', "Regla creada. Calendario {$anio} sincronizado (eliminados: {$stats['deleted']}, upsert: {$stats['upserted']}).");
    }

    public function update(Request $request, VerificacionRegla $verificacion_regla)
    {
        // Validación base de la regla
        $data = $request->validate([
            'nombre'     => ['required', 'string', 'max:255'],
            'version'    => ['nullable', 'string', 'max:50'],
            'status'     => ['required', Rule::in(['draft','published','archived'])],
            'frecuencia' => ['required', Rule::in(['Semestral','Anual'])],
            'notas'      => ['nullable','string'],

            // Edición opcional de estados (por AÑO)
            'anio'       => ['nullable','integer','min:2000','max:2999'],
            'estados'    => ['nullable','array'],
            'estados.*'  => ['string','max:100'],
        ]);

        // 1) Actualiza metadatos de la regla
        $verificacion_regla->update([
            'nombre'     => $data['nombre'],
            'version'    => $data['version'] ?? null,
            'status'     => $data['status'],
            'frecuencia' => $data['frecuencia'],
            'notas'      => $data['notas'] ?? null,
        ]);

        $mensajeEstados = '';
        // 2) (Opcional) Sincroniza estados del año indicado
        if ($request->filled('anio')) {
            $anio = (int) $request->integer('anio');
            $estadosLabels = (array) ($request->input('estados', [])); // permite vacío para limpiar

            // Normalizados (para validar choques con OTRAS reglas)
            $estadosNorm = array_values(array_unique(array_map(
                fn($e) => $this->normalizeEstado($e), $estadosLabels
            )));

            // Valida choques con OTRAS reglas para ese año
            if (!empty($estadosNorm)) {
                $confPivot = VerificacionReglaEstado::query()
                    ->where('anio', $anio)
                    ->whereIn('estado', $estadosNorm)
                    ->where('regla_id', '!=', $verificacion_regla->id)
                    ->exists();

                $confCal = DB::table('calendario_verificacion')
                    ->where('anio', $anio)
                    ->whereIn('estado', $estadosNorm)
                    ->where('regla_id', '!=', $verificacion_regla->id)
                    ->exists();

                if ($confPivot || $confCal) {
                    throw ValidationException::withMessages([
                        'estados' => "Uno o más estados ya están asignados en {$anio} por otra regla.",
                    ]);
                }
            }

            // Sincroniza (reemplaza el set de ese año)
            $this->syncEstadosParaAnio($verificacion_regla, $anio, $estadosLabels);

            // Reconciliación del calendario del año afectado
            $statsY = $this->reconciliarPeriodos($verificacion_regla, $anio);
            $mensajeEstados = " | Estados {$anio} actualizados (eliminados: {$statsY['deleted']}, upsert: {$statsY['upserted']}).";
        }

        // 3) ⚡️ Reconciliación de TODOS los años asignados (por si cambió la frecuencia)
        $anios = $verificacion_regla->estadosAsignados()->distinct()->pluck('anio');
        $tot = ['deleted'=>0,'upserted'=>0];
        foreach ($anios as $a) {
            $s = $this->reconciliarPeriodos($verificacion_regla, (int)$a);
            $tot['deleted']  += $s['deleted'];
            $tot['upserted'] += $s['upserted'];
        }

        return redirect()->route('verificacion-reglas.index')
            ->with('success', "Regla actualizada. Calendarios sincronizados (eliminados: {$tot['deleted']}, upsert: {$tot['upserted']}).{$mensajeEstados}");
    }

    public function destroy(VerificacionRegla $verificacion_regla)
    {
        // Si ya aplicaste CASCADE en la BD, con esto basta:
        $verificacion_regla->delete();

        return redirect()
            ->route('verificacion-reglas.index')
            ->with('success', 'Regla eliminada.');
    }

    /* ===== (Legacy/compat) Generación manual, ahora redirige a reconciliación ===== */
    public function generar(Request $request, VerificacionRegla $verificacion_regla)
    {
        $request->validate([
            'anio' => ['required', 'integer', 'min:2000', 'max:2999'],
        ]);

        $anio = (int) $request->integer('anio');

        $stats = $this->reconciliarPeriodos($verificacion_regla, $anio);

        return redirect()->route('verificacion-reglas.index')
            ->with('success', "Calendario {$anio} sincronizado (eliminados: {$stats['deleted']}, upsert: {$stats['upserted']}).");
    }

    /* ===== Reconciliación automática (borra obsoletos y upserta esperados) ===== */

    /** Clave lógica única consistente con el índice único de BD. */
    private function keyFor(string $estado, int $terminacion, int $mi, int $mf, int $anio): string
    {
        $E = VerificacionRegla::normalizeEstado($estado);
        return "{$E}|{$terminacion}|{$mi}|{$mf}|{$anio}";
    }

    /** Construye el conjunto esperado de filas para regla+año (indexado por key). */
    private function buildExpectedRows(VerificacionRegla $regla, int $anio): array
    {
        // 1) Estados (normalizados) para ese año
        $estados = $regla->estadosParaAnio($anio)
            ->map(fn($e) => VerificacionRegla::normalizeEstado($e))
            ->unique()
            ->values()
            ->all();

        if (empty($estados)) return [];

        // 2) Detalles (deduplicados) — << clave: filtrar por frecuencia >>
        $q = $regla->detalles()->orderBy('terminacion');
        if ($regla->frecuencia === 'Anual') {
            $q->where('semestre', 0);
        } else { // Semestral
            $q->whereIn('semestre', [1,2]);
        }

        $detalles = $q->get(['terminacion','semestre','mes_inicio','mes_fin'])
            ->unique(fn($d) => $d->terminacion.'|'.$d->semestre.'|'.$d->mes_inicio.'|'.$d->mes_fin)
            ->values();

        if ($detalles->isEmpty()) return [];

        $rows = [];
        foreach ($estados as $estado) {
            foreach ($detalles as $d) {
                $mi  = (int) $d->mes_inicio;
                $mf  = (int) $d->mes_fin;
                if ($mi < 1 || $mi > 12 || $mf < 1 || $mf > 12 || $mi > $mf) {
                    continue; // no admitimos cruce de año
                }
                $desde = Carbon::create($anio, $mi, 1)->startOfMonth();
                $hasta = Carbon::create($anio, $mf, 1)->endOfMonth();

                $sem   = (int) $d->semestre; // 0 (anual) ó 1/2
                $semE  = ($sem === 0) ? ($mf <= 6 ? 1 : 2) : $sem;

                $row = [
                    'estado'         => VerificacionRegla::normalizeEstado($estado),
                    'terminacion'    => (int) $d->terminacion,
                    'mes_inicio'     => $mi,
                    'mes_fin'        => $mf,
                    'semestre'       => $semE,
                    'frecuencia'     => $regla->frecuencia, // "Semestral" | "Anual"
                    'anio'           => $anio,
                    'vigente_desde'  => $desde->toDateString(),
                    'vigente_hasta'  => $hasta->toDateString(),
                    'regla_id'       => $regla->id,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
                $rows[$this->keyFor($row['estado'],$row['terminacion'],$mi,$mf,$anio)] = $row;
            }
        }
        return $rows; // indexados por key
    }

    /**
     * Reconciliación total:
     * - Borra periodos obsoletos de ESA regla+año
     * - Upsert del conjunto esperado (sin duplicar)
     * Retorna ['deleted' => n, 'upserted' => n]
     */
    private function reconciliarPeriodos(VerificacionRegla $regla, int $anio): array
    {
        $expected = $this->buildExpectedRows($regla, $anio);

        return DB::transaction(function () use ($regla, $anio, $expected) {
            // Trae existentes (id + columnas de clave) para esta regla+año
            $existing = DB::table('calendario_verificacion')
                ->where('regla_id', $regla->id)
                ->where('anio', $anio)
                ->get(['id','estado','terminacion','mes_inicio','mes_fin','anio'])
                ->mapWithKeys(function ($r) {
                    $k = $this->keyFor(
                        (string)$r->estado,
                        (int)$r->terminacion,
                        (int)$r->mes_inicio,
                        (int)$r->mes_fin,
                        (int)$r->anio
                    );
                    return [$k => (int)$r->id];
                })
                ->all();

            // 1) Borrados: existentes que ya no están en el esperado
            $idsToDelete = [];
            foreach ($existing as $k => $id) {
                if (!isset($expected[$k])) {
                    $idsToDelete[] = $id;
                }
            }
            if (!empty($idsToDelete)) {
                DB::table('calendario_verificacion')->whereIn('id', $idsToDelete)->delete();
            }

            // 2) Upsert de todo lo esperado (agrega/actualiza)
            $rows = array_values($expected);
            if (!empty($rows)) {
                DB::table('calendario_verificacion')->upsert(
                    $rows,
                    ['estado','terminacion','mes_inicio','mes_fin','anio'], // coincide con índice único
                    ['regla_id','semestre','frecuencia','vigente_desde','vigente_hasta','updated_at']
                );
            }

            return ['deleted' => count($idsToDelete), 'upserted' => count($rows)];
        });
    }

    /* ===== Helper: sincroniza los estados de una REGLA para un AÑO ===== */
    private function syncEstadosParaAnio(VerificacionRegla $regla, int $anio, array $labels): void
    {
        // Set destino (labels tal cual; el modelo/mutator normaliza al guardar)
        $destLabels = array_values(array_unique(array_filter($labels, fn($x) => is_string($x) && $x !== '')));

        DB::transaction(function () use ($regla, $anio, $destLabels) {
            // Estados actuales del año
            $actual = VerificacionReglaEstado::query()
                ->where('regla_id', $regla->id)
                ->where('anio', $anio)
                ->pluck('estado')
                ->all();

            // Diffs
            $toDelete = array_values(array_diff($actual, $destLabels));
            $toInsert = array_values(array_diff($destLabels, $actual));

            if (!empty($toDelete)) {
                VerificacionReglaEstado::query()
                    ->where('regla_id', $regla->id)
                    ->where('anio', $anio)
                    ->whereIn('estado', $toDelete)
                    ->delete();
            }

            foreach ($toInsert as $label) {
                VerificacionReglaEstado::create([
                    'regla_id' => $regla->id,
                    'anio'     => $anio,
                    'estado'   => $label, // normaliza el mutator
                ]);
            }
        });
    }

    /* ===== Defaults para precarga en UI ===== */
    protected function defaultSemestralDetalles(): array
    {
        // Mapeo
        // S1: 5-6 Ene-Feb | 7-8 Feb-Mar | 3-4 Mar-Abr | 1-2 Abr-May | 9-0 May-Jun
        // S2: 5-6 Jul-Ago | 7-8 Ago-Sep | 3-4 Sep-Oct | 1-2 Oct-Nov | 9-0 Nov-Dic
        $mapS1 = [
            5 => [1,2], 6 => [1,2],
            7 => [2,3], 8 => [2,3],
            3 => [3,4], 4 => [3,4],
            1 => [4,5], 2 => [4,5],
            9 => [5,6], 0 => [5,6],
        ];

        $out = [];
        foreach (range(0,9) as $d) {
            [$mi1, $mf1] = $mapS1[$d] ?? [1,2];
            $mi2 = $mi1 + 6; if ($mi2 > 12) $mi2 -= 12;
            $mf2 = $mf1 + 6; if ($mf2 > 12) $mf2 -= 12;

            $out[$d][1]['mes_inicio'] = $mi1;
            $out[$d][1]['mes_fin']    = $mf1;
            $out[$d][2]['mes_inicio'] = $mi2;
            $out[$d][2]['mes_fin']    = $mf2;
        }
        return $out;
    }

    protected function defaultAnualDetalles(): array
    {
        // Estilo Jalisco: 1→Ene–Feb, 2→Feb–Mar, ... 0→Nov–Dic
        $map = [
            1 => [1,2], 2 => [2,3], 3 => [3,4], 4 => [4,5], 5 => [5,6],
            6 => [6,7], 7 => [7,8], 8 => [8,9], 9 => [9,10], 0 => [11,12],
        ];
        $out = [];
        foreach (range(0,9) as $d) {
            $mi = $map[$d][0] ?? 1;
            $mf = $map[$d][1] ?? 2;
            $out[$d][0]['mes_inicio'] = $mi;
            $out[$d][0]['mes_fin']    = $mf;
        }
        return $out;
    }

    /* ===== Estados disponibles por AÑO (para el menú) ===== */
    public function estadosDisponibles(Request $request)
    {
        $anio = (int) $request->query('anio', now()->year);
        $reglaId = (int) $request->query('regla_id', 0); // opcional: al editar, excluye a la propia regla

        if ($anio < 2000 || $anio > 2999) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Año inválido',
                'anio'        => $anio,
                'disponibles' => [],
                'ocupados'    => [],
                'seleccionados'=> [],
            ], 400);
        }

        // Estados ya ocupados (normalizados) por OTRAS reglas en ese año
        $ocupadosQ = VerificacionReglaEstado::where('anio', $anio);
        if ($reglaId > 0) {
            $ocupadosQ->where('regla_id', '!=', $reglaId);
        }
        $ocupados = $ocupadosQ->pluck('estado')
            ->map(fn($e) => $this->normalizeEstado($e))
            ->unique()
            ->values()
            ->all();

        // Estados seleccionados por ESTA regla (si aplica)
        $seleccionados = [];
        if ($reglaId > 0) {
            $seleccionados = VerificacionReglaEstado::where('regla_id', $reglaId)
                ->where('anio', $anio)
                ->pluck('estado')
                ->values()
                ->all();
        }

        $catalogo = $this->catalogoEstados();

        $disponibles = [];
        foreach ($catalogo as $label) {
            $norm = $this->normalizeEstado($label);
            if (!in_array($norm, $ocupados, true)) {
                $disponibles[] = [
                    'value' => $label, // el modelo normaliza al guardar
                    'label' => $label,
                ];
            }
        }

        return response()->json([
            'ok'            => true,
            'anio'          => $anio,
            'disponibles'   => $disponibles,
            'ocupados'      => $ocupados,
            'seleccionados' => $seleccionados,
        ]);
    }
}
