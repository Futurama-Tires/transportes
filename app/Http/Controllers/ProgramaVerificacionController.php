<?php

namespace App\Http\Controllers;

use App\Models\CalendarioVerificacion;
use App\Models\Verificacion;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProgramaVerificacionController extends Controller
{
    public function index(Request $request)
    {
        $anio = intval($request->input('anio', now()->year));
        $semestre = $request->input('semestre');
        $semestre = in_array($semestre, ['1','2']) ? intval($semestre) : null;

        // Estados desde vehiculos (tu fuente de verdad)
        $estadosDisponibles = Vehiculo::query()
            ->select('estado')->distinct()->orderBy('estado')->pluck('estado');

        $estado = $request->input('estado');
        if (!$estado || !$estadosDisponibles->contains($estado)) {
            $estado = $estadosDisponibles->first();
        }

        $estadoNorm = $this->normalizeEstado($estado);

        // Periodos del calendario
        $periodos = CalendarioVerificacion::where('anio', $anio)
            ->where('estado', $estadoNorm)
            ->when($semestre, fn($q) => $q->where('semestre', $semestre))
            ->orderBy('semestre')->orderBy('mes_inicio')->get();

        $rangos = $this->rangosPorSemestre($periodos);

        // Vehículos del estado (traemos 'unidad' para mostrarla)
        $vehiculos = Vehiculo::where('estado', $estado)
            ->orderBy('placa')
            ->get(['id','placa','estado','unidad']);

        // Mapa terminación -> vehículos
        $vehiculosPorTerminacion = [];
        foreach ($vehiculos as $v) {
            $dig = $this->ultimaCifraDePlaca($v->placa);
            if ($dig === null) continue;
            $vehiculosPorTerminacion[$dig] ??= [];
            $vehiculosPorTerminacion[$dig][] = $v;
        }

        // Verificaciones ya registradas en cada semestre
        $ids = $vehiculos->pluck('id')->all();

        $verifS1 = collect(); // <-- inicializado para evitar undefined variable
        $verifS2 = collect(); // <-- inicializado para evitar undefined variable

        if (isset($rangos[1]) && !empty($ids)) {
            $anioS1 = (int) $rangos[1]['desde']->format('Y');
            $miS1   = (int) $rangos[1]['desde']->format('n'); // suele ser 1
            $mfS1   = (int) $rangos[1]['hasta']->format('n'); // suele ser 6
            $verifS1 = \App\Models\Verificacion::whereIn('vehiculo_id', $ids)
                ->where('anio', $anioS1)
                ->where('mes_inicio', $miS1)
                ->where('mes_fin', $mfS1)
                ->get(['id','vehiculo_id','fecha_verificacion','comentarios']);
        }
        if (isset($rangos[2]) && !empty($ids)) {
            $anioS2 = (int) $rangos[2]['desde']->format('Y');
            $miS2   = (int) $rangos[2]['desde']->format('n'); // suele ser 7
            $mfS2   = (int) $rangos[2]['hasta']->format('n'); // suele ser 12
            $verifS2 = \App\Models\Verificacion::whereIn('vehiculo_id', $ids)
                ->where('anio', $anioS2)
                ->where('mes_inicio', $miS2)
                ->where('mes_fin', $mfS2)
                ->get(['id','vehiculo_id','fecha_verificacion','comentarios']);
        }

        // Preparar estructura para la vista
        $dataSemestres = [];

        foreach ([1,2] as $s) {
            if ($semestre && $semestre !== $s) continue;
            if (!isset($rangos[$s])) continue;

            $terminaciones = $periodos->where('semestre', $s)->pluck('terminacion')->unique()->sort()->values();
            if ($terminaciones->isEmpty()) $terminaciones = collect(range(0,9));

            $grupo = [];
            foreach ($terminaciones as $dig) {
                $lista = [
                    'pendientes'  => [],
                    'verificados' => [], // agrupados por fecha
                ];
                $rang = $rangos[$s];
                $verifMap = ($s === 1 ? $verifS1 : $verifS2)->groupBy('vehiculo_id');

                foreach ($vehiculosPorTerminacion[$dig] ?? [] as $veh) {
                    $ver = $verifMap->get($veh->id, collect())->sortByDesc('fecha_verificacion')->first();
                    if ($ver) {
                        $fechaKey = Carbon::parse($ver->fecha_verificacion)->toDateString();
                        $lista['verificados'][$fechaKey] ??= [];
                        $lista['verificados'][$fechaKey][] = [
                            'vehiculo'     => $veh,
                            'verificacion' => $ver,
                            'desde'        => $rang['desde'],
                            'hasta'        => $rang['hasta'],
                            'semestre'     => $s,
                            'terminacion'  => $dig,
                        ];
                    } else {
                        $lista['pendientes'][] = [
                            'vehiculo'    => $veh,
                            'desde'       => $rang['desde'],
                            'hasta'       => $rang['hasta'],
                            'semestre'    => $s,
                            'terminacion' => $dig,
                        ];
                    }
                }

                // Ordenar por placa (ajusta si prefieres por unidad)
                usort($lista['pendientes'], fn($a,$b)=>strcmp($a['vehiculo']->placa, $b['vehiculo']->placa));
                foreach ($lista['verificados'] as $f=>&$arr) {
                    usort($arr, fn($a,$b)=>strcmp($a['vehiculo']->placa, $b['vehiculo']->placa));
                }

                $grupo[$dig] = $lista;
            }

            $dataSemestres[$s] = [
                'rango'        => $rangos[$s],
                'terminaciones'=> $grupo,
            ];
        }

        return view('verificaciones.programa.index', [
            'anio'               => $anio,
            'estado'             => $estado,
            'estadoNorm'         => $estadoNorm,
            'estadosDisponibles' => $estadosDisponibles,
            'semestre'           => $semestre,
            'dataSemestres'      => $dataSemestres,
        ]);
    }

    public function marcar(Request $request)
    {
        $data = $request->validate([
            'vehiculo_id' => ['required','integer','min:1'],
            'estado'      => ['required','string','max:255'],
            'fecha'       => ['required','date'],
            'comentarios' => ['nullable','string','max:500'],

            // Los seguimos recibiendo porque definen el periodo programado,
            // pero ya NO obligamos a que la fecha esté dentro de ese rango:
            'desde'       => ['required','date'],
            'hasta'       => ['required','date'], // (quitamos 'after_or_equal:desde')
        ]);

        $vehiculoId = (int) $data['vehiculo_id'];
        $estadoNorm = $this->normalizeEstado($data['estado']);
        $fecha      = \Carbon\Carbon::parse($data['fecha'])->startOfDay();
        $desde      = \Carbon\Carbon::parse($data['desde'])->startOfDay();
        $hasta      = \Carbon\Carbon::parse($data['hasta'])->endOfDay();

        // 🔁 Upsert POR PERIODO programado (anio/mes_inicio/mes_fin)
        $anioPeriodo = (int) $desde->format('Y');
        $mesIni      = (int) $desde->format('n');
        $mesFin      = (int) $hasta->format('n');

        $ver = \App\Models\Verificacion::where('vehiculo_id', $vehiculoId)
            ->where('anio', $anioPeriodo)
            ->where('mes_inicio', $mesIni)
            ->where('mes_fin', $mesFin)
            ->first();

        $payload = [
            'vehiculo_id'             => $vehiculoId,
            'estado'                  => $estadoNorm,
            'fecha_verificacion'      => $fecha->toDateString(),  // ✅ puede ser cualquier fecha
            'comentarios'             => $data['comentarios'] ?? null,
            'resultado'               => 'APROBADO',
            'fecha_programada_inicio' => $desde->toDateString(),
            'fecha_programada_fin'    => $hasta->toDateString(),
            'anio'                    => $anioPeriodo,
            'mes_inicio'              => $mesIni,
            'mes_fin'                 => $mesFin,
        ];

        if ($ver) {
            $ver->update($payload);
        } else {
            \App\Models\Verificacion::create($payload);
        }

        return back()->with('success', 'Verificación registrada.');
    }


    /* ================== Helpers ================== */

    protected function normalizeEstado(?string $s): string
    {
        $norm = $s ? Str::of($s)->ascii()->upper()->replaceMatches('/\s+/', ' ')->trim() : '';
        $val = (string) $norm;

        if (in_array($val, ['ESTADO DE MEXICO','MEXICO','EDO MEXICO','EDO. MEX','E DOMEX'], true)) {
            return 'EDO MEX';
        }
        return $val;
    }

    protected function rangosPorSemestre(Collection $periodos): array
    {
        $out = [];
        foreach ([1,2] as $s) {
            $p = $periodos->where('semestre', $s);
            if ($p->isEmpty()) continue;
            $desde = $p->min('vigente_desde') ?: $this->fechaInicioSemestre($p, $s);
            $hasta = $p->max('vigente_hasta') ?: $this->fechaFinSemestre($p, $s);
            $out[$s] = [
                'desde' => Carbon::parse($desde)->startOfDay(),
                'hasta' => Carbon::parse($hasta)->endOfDay(),
            ];
        }
        return $out;
    }

    protected function fechaInicioSemestre(Collection $p, int $s): Carbon
    {
        $mi = $p->min('mes_inicio') ?? ($s===1 ? 1 : 7);
        $anio = $p->first()->anio ?? now()->year;
        return Carbon::create($anio, $mi, 1)->startOfDay();
    }

    protected function fechaFinSemestre(Collection $p, int $s): Carbon
    {
        $mf = $p->max('mes_fin') ?? ($s===1 ? 6 : 12);
        $anio = $p->first()->anio ?? now()->year;
        return Carbon::create($anio, $mf, 1)->endOfMonth()->endOfDay();
    }

    /**
     * Obtiene el último dígito válido de la placa:
     * - Si termina en número: ese mismo.
     * - Si termina en letra o guion (u otro no-numérico): busca el último número ANTES, recorriendo hacia atrás.
     * - Si no hay ningún número en toda la placa: devuelve null.
     * Ejemplos:
     *  - "ABC123"   -> 3
     *  - "ABC123C"  -> 3
     *  - "XYZ89-"   -> 9
     *  - "JKL000A"  -> 0
     *  - "PLACA"    -> null
     *  - "ABC-456D" -> 6
     */
    protected function ultimaCifraDePlaca(?string $placa): ?int
    {
        if (!$placa) {
            return null;
        }

        $str = strtoupper(trim($placa));
        $len = strlen($str);

        if ($len === 0) {
            return null;
        }

        // Si el último caracter es un dígito, úsalo
        $last = $str[$len - 1];
        if (ctype_digit($last)) {
            return intval($last);
        }

        // Si termina en letra/guion/u otro, recorre hacia atrás hasta hallar un dígito
        for ($i = $len - 1; $i >= 0; $i--) {
            if (ctype_digit($str[$i])) {
                return intval($str[$i]);
            }
        }

        // No hay dígitos en toda la placa
        return null;
    }
}
