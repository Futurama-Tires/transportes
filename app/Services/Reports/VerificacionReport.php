<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;
use App\Models\Vehiculo;

class VerificacionReport
{
    /**
     * @return array{rows: array<int,array>, kpis: array, chart: array}
     */
    public function run(ReportFilters $fx): array
    {
        $anio = (int)($fx->anio ?: date('Y'));

        // Vehículos filtrados (si aplica)
        $vehiculoIds = $fx->vehiculos;
        $vehiculos = Vehiculo::query()
            ->select('id','placa','unidad','estado')
            ->when(count($vehiculoIds) > 0, fn($q)=>$q->whereIn('id', $vehiculoIds))
            ->orderBy('placa')
            ->get();

        if ($vehiculos->isEmpty()) {
            $kpis = ['total'=>0,'verificados'=>0,'parciales'=>0,'sin_verificar'=>0];
            return ['rows' => [], 'kpis' => $kpis, 'chart' => [
                'categories' => ['Verificado', 'Parcial', 'Sin verificar'],
                'series'     => [['name' => 'Estatus', 'data' => [0,0,0]]],
            ]];
        }

        // ===== Calendario del año (por estado/terminación) =====
        $estadosNorm = $vehiculos->pluck('estado')->filter()->map(fn($e)=>$fx->normalizeEstado($e))->unique()->values();

        $cvRows = DB::table('calendario_verificacion as cv')
            ->select('cv.id','cv.estado','cv.terminacion','cv.semestre','cv.anio',
                     'cv.vigente_desde','cv.vigente_hasta','cv.mes_inicio','cv.mes_fin')
            ->where('cv.anio', $anio)
            ->when($estadosNorm->isNotEmpty(), fn($q)=>$q->whereIn('cv.estado', $estadosNorm))
            ->get();

        $cvByEstadoTerm = [];   // "ESTADO|dig" => [rows...]
        $requiredByEstado = []; // "ESTADO" => #semestres distintos
        foreach ($cvRows as $cv) {
            $key = $cv->estado.'|'.(int)$cv->terminacion;
            $cvByEstadoTerm[$key] ??= [];
            $cvByEstadoTerm[$key][] = $cv;

            $requiredByEstado[$cv->estado] ??= [];
            $requiredByEstado[$cv->estado][$cv->semestre] = true;
        }
        foreach ($requiredByEstado as $est => $semSet) {
            $requiredByEstado[$est] = count($semSet);
        }

        // ===== Verificaciones del año (por vehículo) =====
        $vehIds = $vehiculos->pluck('id')->all();
        $verifs = DB::table('verificaciones as v')
            ->select('v.vehiculo_id','v.fecha_verificacion','v.calendario_id',
                     'cv.semestre as cv_semestre','cv.estado as cv_estado','cv.anio as cv_anio',
                     'cv.vigente_desde','cv.vigente_hasta','cv.mes_inicio','cv.mes_fin')
            ->leftJoin('calendario_verificacion as cv','cv.id','=','v.calendario_id')
            ->whereIn('v.vehiculo_id', $vehIds)
            ->where(function($q) use ($anio) {
                $q->whereYear('v.fecha_verificacion', $anio)
                  ->orWhere('cv.anio', $anio);
            })
            ->orderBy('v.fecha_verificacion','desc')
            ->get()
            ->groupBy('vehiculo_id');

        // ===== Clasificación =====
        $rows = $vehiculos->map(function($v) use ($anio, $verifs, $cvByEstadoTerm, $requiredByEstado, $fx) {
            $estadoNorm   = $fx->normalizeEstado($v->estado);
            $terminacion  = $fx->ultimaCifraDePlaca($v->placa);
            $keyET        = $terminacion !== null ? ($estadoNorm.'|'.$terminacion) : null;

            // ¿Cuántos semestres exige este estado/terminación en el año?
            $req = 2; // por defecto
            if ($keyET && isset($cvByEstadoTerm[$keyET])) {
                $req = collect($cvByEstadoTerm[$keyET])->pluck('semestre')->unique()->count();
                $req = max(1, (int)$req);
            } elseif (isset($requiredByEstado[$estadoNorm])) {
                $req = max(1, (int)$requiredByEstado[$estadoNorm]);
            }

            $list = $verifs->get($v->id, collect());
            $semCubiertos = collect();
            $fechaUlt = $list->first()?->fecha_verificacion;

            foreach ($list as $row) {
                // 1) Si viene semestre desde el calendario, úsalo.
                if (!is_null($row->cv_semestre)) {
                    $semCubiertos->push((int)$row->cv_semestre);
                    continue;
                }

                // 2) Mapear por rango de calendario (estado+terminación) si existe
                if ($keyET && isset($cvByEstadoTerm[$keyET]) && $row->fecha_verificacion) {
                    $f = \Carbon\Carbon::parse($row->fecha_verificacion)->startOfDay();
                    $hit = collect($cvByEstadoTerm[$keyET])->first(function($cv) use ($f) {
                        $d = $cv->vigente_desde ? \Carbon\Carbon::parse($cv->vigente_desde)->startOfDay() : null;
                        $h = $cv->vigente_hasta  ? \Carbon\Carbon::parse($cv->vigente_hasta)->endOfDay()   : null;
                        if ($d && $h) return $f->between($d, $h, true);
                        if ($cv->mes_inicio && $cv->mes_fin) {
                            $fd = \Carbon\Carbon::create((int)$cv->anio, (int)$cv->mes_inicio, 1)->startOfDay();
                            $fh = \Carbon\Carbon::create((int)$cv->anio, (int)$cv->mes_fin, 1)->endOfMonth()->endOfDay();
                            return $f->between($fd, $fh, true);
                        }
                        return false;
                    });
                    if ($hit && $hit->semestre) {
                        $semCubiertos->push((int)$hit->semestre);
                        continue;
                    }
                }

                // 3) Último recurso: por mes (S1=ene-jun, S2=jul-dic)
                if ($row->fecha_verificacion) {
                    $mes = (int)\Carbon\Carbon::parse($row->fecha_verificacion)->format('n');
                    $sem = $mes <= 6 ? 1 : 2;
                    $semCubiertos->push($sem);
                }
            }

            $semCount = $semCubiertos->unique()->count();

            $estatus = 'Sin verificar';
            if ($semCount >= $req) {
                $estatus = 'Verificado';
            } elseif ($semCount >= 1) {
                $estatus = 'Parcial';
            }

            return [
                'vehiculo_id'        => (int)$v->id,
                'placa'              => $v->placa,
                'unidad'             => $v->unidad,
                'vehiculo_label'     => $fx->makeVehiculoLabel($v->unidad, $v->placa),
                'estado'             => $v->estado,
                'anio'               => $anio,
                'estatus'            => $estatus,
                'fecha_verificacion' => $fechaUlt ? (string)$fechaUlt : null,
            ];
        })->values()->all();

        $kpis = [
            'total'         => count($rows),
            'verificados'   => collect($rows)->where('estatus','Verificado')->count(),
            'parciales'     => collect($rows)->where('estatus','Parcial')->count(),
            'sin_verificar' => collect($rows)->where('estatus','Sin verificar')->count(),
        ];

        $chart = [
            'categories' => ['Verificado', 'Parcial', 'Sin verificar'],
            'series'     => [[
                'name' => 'Estatus',
                'data' => [$kpis['verificados'], $kpis['parciales'], $kpis['sin_verificar']],
            ]],
        ];

        return ['rows' => $rows, 'kpis' => $kpis, 'chart' => $chart];
    }
}
