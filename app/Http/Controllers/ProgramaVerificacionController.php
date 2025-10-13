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

        // Estados desde vehiculos 
        $estadosDisponibles = Vehiculo::query()
            ->select('estado')->distinct()->orderBy('estado')->pluck('estado');

        $estado = $request->input('estado');
        if (!$estado || !$estadosDisponibles->contains($estado)) {
            $estado = $estadosDisponibles->first();
        }

        $estadoNorm = $this->normalizeEstado($estado);

        // Periodos del calendario (por año/estado y opcional semestre)
        $periodos = CalendarioVerificacion::where('anio', $anio)
            ->where('estado', $estadoNorm)
            ->when($semestre, fn($q) => $q->where('semestre', $semestre))
            ->orderBy('semestre')->orderBy('mes_inicio')->get();

        // Rangos de cada semestre (solo para cabecera de tarjeta)
        $rangos = $this->rangosPorSemestre($periodos);

        // Índice de bimestres por semestre y terminación (para mostrar SIEMPRE aun sin vehículos)
        // Estructura: $bimestresPorSemestre[1|2][0..9] = ['desde'=>Carbon, 'hasta'=>Carbon, 'mi'=>int, 'mf'=>int]
        $bimestresPorSemestre = [1 => [], 2 => []];
        foreach ($periodos as $p) {
            $s   = (int) $p->semestre;
            $dig = (int) $p->terminacion;

            // Preferir columnas de vigencia si existen; si no, construir con mes_inicio/mes_fin
            $desde = $p->vigente_desde ? Carbon::parse($p->vigente_desde)->startOfDay()
                                       : Carbon::create($anio, (int)$p->mes_inicio, 1)->startOfDay();
            $hasta = $p->vigente_hasta ? Carbon::parse($p->vigente_hasta)->endOfDay()
                                       : Carbon::create($anio, (int)$p->mes_fin, 1)->endOfMonth()->endOfDay();

            $bimestresPorSemestre[$s][$dig] = [
                'desde' => $desde,
                'hasta' => $hasta,
                'mi'    => (int) $p->mes_inicio,
                'mf'    => (int) $p->mes_fin,
            ];
        }

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

        // Verificaciones ya registradas por semestre (rango de meses, no par fijo)
        $ids = $vehiculos->pluck('id')->all();

        $verifS1 = collect();
        $verifS2 = collect();

        if (!empty($ids)) {
            $verifS1 = Verificacion::whereIn('vehiculo_id', $ids)
                ->where('anio', $anio)
                ->whereBetween('mes_inicio', [1, 6])   // incluye registros antiguos (1-6) y los nuevos por bimestre
                ->get(['id','vehiculo_id','fecha_verificacion','comentarios','mes_inicio','mes_fin']);

            $verifS2 = Verificacion::whereIn('vehiculo_id', $ids)
                ->where('anio', $anio)
                ->whereBetween('mes_inicio', [7, 12])
                ->get(['id','vehiculo_id','fecha_verificacion','comentarios','mes_inicio','mes_fin']);
        }

        // Preparar estructura para la vista
        $dataSemestres = [];

        foreach ([1,2] as $s) {
            if ($semestre && $semestre !== $s) continue;
            if (!isset($rangos[$s])) continue;

            // Terminaciones: unión de las definidas en calendario y las que tienen vehículos
            $terminacionesCal = $periodos->where('semestre', $s)->pluck('terminacion')->map(fn($t)=>(int)$t);
            $terminacionesVeh = collect(array_keys($vehiculosPorTerminacion))->map(fn($t)=>(int)$t);
            $terminaciones = $terminacionesCal->merge($terminacionesVeh)->unique()->sort()->values();

            // Si no hay nada, mostramos 0..9 (opcional)
            if ($terminaciones->isEmpty()) {
                $terminaciones = collect(range(0,9));
            }

            $grupo = [];
            $verifMap = ($s === 1 ? $verifS1 : $verifS2)->groupBy('vehiculo_id');

            foreach ($terminaciones as $dig) {
                $bimestre = $bimestresPorSemestre[$s][$dig] ?? null;

                $desdeBi = $bimestre['desde'] ?? $rangos[$s]['desde']; // para el modal siempre debemos mandar algo
                $hastaBi = $bimestre['hasta'] ?? $rangos[$s]['hasta'];

                $lista = [
                    'pendientes'  => [],
                    'verificados' => [], // agrupados por fecha
                    // << clave para la vista: permite mostrar el bimestre aun con 0 vehículos >>
                    'bimestre'    => $bimestre ? [
                        'desde' => $bimestre['desde'],
                        'hasta' => $bimestre['hasta'],
                    ] : null,
                    'terminacion' => $dig,
                    'semestre'    => $s,
                ];

                foreach ($vehiculosPorTerminacion[$dig] ?? [] as $veh) {
                    // Tomar la verificación más reciente del semestre
                    $ver = $verifMap->get($veh->id, collect())->sortByDesc('fecha_verificacion')->first();

                    if ($ver) {
                        $fechaKey = Carbon::parse($ver->fecha_verificacion)->toDateString();
                        $lista['verificados'][$fechaKey] ??= [];
                        $lista['verificados'][$fechaKey][] = [
                            'vehiculo'     => $veh,
                            'verificacion' => $ver,
                            // para edición en modal, usamos SIEMPRE el bimestre de esta terminación
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
                'terminaciones'=> $grupo,            // tarjetas por terminación (con bimestre propio)
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

            // Definen el periodo programado (ahora bimestre de la terminación)
            'desde'       => ['required','date'],
            'hasta'       => ['required','date'],
        ]);

        $vehiculoId = (int) $data['vehiculo_id'];
        $estadoNorm = $this->normalizeEstado($data['estado']);
        $fecha      = Carbon::parse($data['fecha'])->startOfDay();
        $desde      = Carbon::parse($data['desde'])->startOfDay();
        $hasta      = Carbon::parse($data['hasta'])->endOfDay();

        // Upsert POR PERIODO programado (anio/mes_inicio/mes_fin) — ahora corresponde al BIMESTRE
        $anioPeriodo = (int) $desde->format('Y');
        $mesIni      = (int) $desde->format('n');
        $mesFin      = (int) $hasta->format('n');

        $ver = Verificacion::where('vehiculo_id', $vehiculoId)
            ->where('anio', $anioPeriodo)
            ->where('mes_inicio', $mesIni)
            ->where('mes_fin', $mesFin)
            ->first();

        $payload = [
            'vehiculo_id'             => $vehiculoId,
            'estado'                  => $estadoNorm,
            'fecha_verificacion'      => $fecha->toDateString(),  // puede ser fuera del periodo
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
     * Obtiene el último dígito válido de la placa:
     * - Si termina en número: ese mismo.
     * - Si termina en letra o guion (u otro no-numérico): busca el último número ANTES, recorriendo hacia atrás.
     * - Si no hay ningún número en toda la placa: devuelve null.
     */
    protected function ultimaCifraDePlaca(?string $placa): ?int
    {
        if (!$placa) {
            return null;
        }
        $str = strtoupper(trim($placa));
        $len = strlen($str);
        if ($len === 0) return null;

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
        return null; // No hay dígitos en toda la placa
    }
}
