<?php

namespace App\Http\Controllers;

use App\Models\CalendarioVerificacion;
use App\Models\Verificacion;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProgramaVerificacionController extends Controller
{
    public function index(Request $request)
    {
        $anio = intval($request->input('anio', now()->year));
        $semestre = $request->input('semestre');
        $semestre = in_array($semestre, ['1','2']) ? intval($semestre) : null;

        // Estados desde vehículos: sólo no-nulos y no-vacíos
        $estadosDisponibles = Vehiculo::query()
            ->whereNotNull('estado')
            ->where('estado', '<>', '')
            ->select('estado')->distinct()->orderBy('estado')->pluck('estado');

        if ($estadosDisponibles->isEmpty()) {
            return view('verificaciones.programa.index', [
                'anio'               => $anio,
                'estado'             => null,
                'estadoNorm'         => null,
                'estadosDisponibles' => $estadosDisponibles,
                'semestre'           => $semestre,
                'dataSemestres'      => [],
            ])->with('warning', 'No hay vehículos con estado asignado.');
        }

        $estado = $request->input('estado');
        if (!$estado || !$estadosDisponibles->contains($estado)) {
            $estado = $estadosDisponibles->first();
        }
        $estadoNorm = $this->normalizeEstado($estado);

        // Periodos del calendario (por año/estado y opcional semestre)
        $periodos = CalendarioVerificacion::query()
            ->where('anio', $anio)
            ->where('estado', $estadoNorm)
            ->when($semestre, fn($q) => $q->where('semestre', $semestre))
            ->orderBy('semestre')->orderBy('mes_inicio')->get();

        // Rangos de cada semestre (cabecera)
        $rangos = $this->rangosPorSemestre($periodos);

        // Índice de bimestres por semestre y terminación:
        // [1|2][0..9] => ['desde'=>Carbon, 'hasta'=>Carbon, 'mi'=>int, 'mf'=>int, 'cv_id'=>int]
        $bimestresPorSemestre = [1 => [], 2 => []];
        foreach ($periodos as $p) {
            $s   = (int) $p->semestre;
            $dig = (int) $p->terminacion;

            $desde = $p->vigente_desde
                ? Carbon::parse($p->vigente_desde)->startOfDay()
                : Carbon::create($anio, (int)$p->mes_inicio, 1)->startOfDay();

            $hasta = $p->vigente_hasta
                ? Carbon::parse($p->vigente_hasta)->endOfDay()
                : Carbon::create($anio, (int)$p->mes_fin, 1)->endOfMonth()->endOfDay();

            $bimestresPorSemestre[$s][$dig] = [
                'desde' => $desde,
                'hasta' => $hasta,
                'mi'    => (int) $p->mes_inicio,
                'mf'    => (int) $p->mes_fin,
                'cv_id' => (int) $p->id,
            ];
        }

        // Vehículos del estado (valor mostrado tal cual)
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

        // Verificaciones del año para esos vehículos (sin usar verificaciones.anio)
        $ids = $vehiculos->pluck('id')->all();
        $verifMap = collect();

        if (!empty($ids)) {
            // Traemos sólo del año solicitado:
            // - Si hay calendario: cv.anio = $anio
            // - Si no hay calendario: YEAR(fecha_verificacion) = $anio
            $verifs = Verificacion::query()
                ->whereIn('vehiculo_id', $ids)
                ->leftJoin('calendario_verificacion as cv', 'cv.id', '=', 'verificaciones.calendario_id')
                ->where(function ($q) use ($anio) {
                    $q->where('cv.anio', $anio)
                      ->orWhereYear('fecha_verificacion', $anio);
                })
                ->get([
                    'verificaciones.id',
                    'verificaciones.vehiculo_id',
                    'verificaciones.fecha_verificacion',
                    'verificaciones.comentarios',
                    'verificaciones.calendario_id',
                ]);

            // Agrupar por vehículo y ordenar por fecha (más recientes primero)
            $verifMap = $verifs
                ->sortByDesc('fecha_verificacion')
                ->groupBy('vehiculo_id');
        }

        // Preparar estructura para la vista
        $dataSemestres = [];

        foreach ([1,2] as $s) {
            if ($semestre && $semestre !== $s) continue;
            if (!isset($rangos[$s])) continue;

            // Terminaciones a mostrar: SOLO las definidas en calendario para ese semestre
            $terminaciones = $periodos->where('semestre', $s)
                ->pluck('terminacion')->map(fn($t)=>(int)$t)->unique()->sort()->values();

            if ($terminaciones->isEmpty()) {
                // Si no hay calendario para ese semestre, podríamos caer a vehículos (raro)
                $terminaciones = collect(array_keys($vehiculosPorTerminacion))->map(fn($t)=>(int)$t)->sort()->values();
                if ($terminaciones->isEmpty()) {
                    $terminaciones = collect(range(0,9));
                }
            }

            $grupo = [];

            foreach ($terminaciones as $dig) {
                $bimestre = $bimestresPorSemestre[$s][$dig] ?? null;
                if (!$bimestre) continue; // evita inconsistencias (p. ej., reglas anuales)

                $desdeBi = $bimestre['desde'];
                $hastaBi = $bimestre['hasta'];
                $cvId    = $bimestre['cv_id'];

                $lista = [
                    'pendientes'  => [],
                    'verificados' => [], // agrupados por fecha
                    'bimestre'    => ['desde' => $desdeBi, 'hasta' => $hastaBi],
                    'terminacion' => $dig,
                    'semestre'    => $s,
                ];

                foreach ($vehiculosPorTerminacion[$dig] ?? [] as $veh) {
                    $verList = $verifMap->get($veh->id, collect());

                    // ¿Tiene verificación válida para ESTE bimestre?
                    // a) enlazada al periodo (calendario_id)
                    // b) o cuya fecha cae dentro del rango [desdeBi, hastaBi]
                    $match = $verList->first(function ($ver) use ($cvId, $desdeBi, $hastaBi) {
                        if ($ver->calendario_id && intval($ver->calendario_id) === $cvId) {
                            return true;
                        }
                        if ($ver->fecha_verificacion) {
                            $f = Carbon::parse($ver->fecha_verificacion);
                            // usar "between" inclusivo para máxima compatibilidad
                            return $f->between($desdeBi, $hastaBi, true);
                        }
                        return false;
                    });

                    if ($match) {
                        $fechaKey = Carbon::parse($match->fecha_verificacion)->toDateString();
                        $lista['verificados'][$fechaKey] ??= [];
                        $lista['verificados'][$fechaKey][] = [
                            'vehiculo'     => $veh,
                            'verificacion' => $match,
                            'desde'        => $desdeBi,
                            'hasta'        => $hastaBi,
                            'semestre'     => $s,
                            'terminacion'  => $dig,
                        ];
                    } else {
                        $lista['pendientes'][] = [
                            'vehiculo'    => $veh,
                            'desde'       => $desdeBi,
                            'hasta'       => $hastaBi,
                            'semestre'    => $s,
                            'terminacion' => $dig,
                        ];
                    }
                }

                // Ordenar por placa
                usort($lista['pendientes'], fn($a,$b)=>strcmp($a['vehiculo']->placa, $b['vehiculo']->placa));
                foreach ($lista['verificados'] as $f=>&$arr) {
                    usort($arr, fn($a,$b)=>strcmp($a['vehiculo']->placa, $b['vehiculo']->placa));
                }

                $grupo[$dig] = $lista;
            }

            $dataSemestres[$s] = [
                'rango'        => $rangos[$s],       // cabecera del semestre
                'terminaciones'=> $grupo,            // tarjetas por terminación
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
            'desde'       => ['required','date'], // bimestre programado
            'hasta'       => ['required','date'],
        ]);

        $vehiculoId = (int) $data['vehiculo_id'];
        $vehiculo   = Vehiculo::findOrFail($vehiculoId);

        $estadoNorm = $this->normalizeEstado($data['estado']);
        $fecha      = Carbon::parse($data['fecha'])->startOfDay();
        $desde      = Carbon::parse($data['desde'])->startOfDay();
        $hasta      = Carbon::parse($data['hasta'])->endOfDay();

        $anioPeriodo = (int) $desde->format('Y');
        $mesIni      = (int) $desde->format('n');
        $mesFin      = (int) $hasta->format('n');

        // Localizar el periodo de calendario por estado+terminación+año+meses
        $terminacion = $this->ultimaCifraDePlaca($vehiculo->placa);
        $cv = CalendarioVerificacion::query()
            ->where('anio', $anioPeriodo)
            ->where('estado', $estadoNorm)
            ->when($terminacion !== null, fn($q) => $q->where('terminacion', $terminacion))
            ->where('mes_inicio', $mesIni)
            ->where('mes_fin', $mesFin)
            ->first();

        if (!$cv) {
            // Fallback por fechas (solapamiento)
            $cv = CalendarioVerificacion::query()
                ->where('anio', $anioPeriodo)
                ->where('estado', $estadoNorm)
                ->when($terminacion !== null, fn($q) => $q->where('terminacion', $terminacion))
                ->whereDate('vigente_desde', '<=', $hasta->toDateString())
                ->whereDate('vigente_hasta', '>=', $desde->toDateString())
                ->first();
        }

        // Buscar verificación existente para ese vehículo y periodo
        $match = Verificacion::query()->where('vehiculo_id', $vehiculoId);
        if ($cv) {
            $match->where('calendario_id', $cv->id);
        } else {
            // Sin periodo localizado: usa misma fecha exacta como criterio de idempotencia
            $match->whereDate('fecha_verificacion', $fecha->toDateString());
        }
        $ver = $match->orderByDesc('id')->first();

        // Payload minimal compatible con tu esquema (sin anio / sin mes_* / sin fecha_programada_*)
        $payload = [
            'vehiculo_id'        => $vehiculoId,
            'fecha_verificacion' => $fecha->toDateString(),
            'comentarios'        => $data['comentarios'] ?? null,
            'calendario_id'      => $cv?->id,
            // Si tu tabla tiene 'estado' o 'resultado', puedes descomentar:
            // 'estado'          => $estadoNorm,
            // 'resultado'       => 'APROBADO',
        ];

        if ($ver) {
            $ver->update($payload);
        } else {
            Verificacion::create($payload);
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
     * Último dígito válido de la placa (si termina en letra, toma el último dígito previo).
     */
    protected function ultimaCifraDePlaca(?string $placa): ?int
    {
        if (!$placa) return null;
        $str = strtoupper(trim($placa));
        $len = strlen($str);
        if ($len === 0) return null;

        $last = $str[$len - 1];
        if (ctype_digit($last)) return intval($last);

        for ($i = $len - 1; $i >= 0; $i--) {
            if (ctype_digit($str[$i])) return intval($str[$i]);
        }
        return null;
    }
}
