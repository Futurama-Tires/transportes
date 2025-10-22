<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

class CostoKmReport
{
    /**
     * @return array{rows: array<int,array>, kpis: array, chart: array}
     */
    public function run(ReportFilters $fx): array
    {
        $params = [];
        $where  = $fx->whereClauseCC($params);

        $sql = "
            SELECT
                cc.vehiculo_id,
                v.placa,
                v.unidad,
                cc.operador_id,
                TRIM(CONCAT(o.nombre, ' ', o.apellido_paterno, ' ', COALESCE(o.apellido_materno,''))) AS operador_nombre,
                SUM(COALESCE(cc.recorrido, GREATEST(0, cc.km_final - cc.km_inicial))) AS km_recorridos,
                SUM(cc.litros)  AS litros,
                SUM(cc.total)   AS gasto,
                COUNT(*)        AS num_cargas
            FROM cargas_combustible cc
            JOIN vehiculos v   ON v.id = cc.vehiculo_id
            LEFT JOIN operadores o ON o.id = cc.operador_id
            $where
            GROUP BY cc.vehiculo_id, v.placa, v.unidad, cc.operador_id, operador_nombre
            ORDER BY v.placa ASC, operador_nombre ASC
        ";

        $rows = collect(DB::select($sql, $params))->map(function ($r) use ($fx) {
            $km     = (float)$r->km_recorridos;
            $litros = (float)$r->litros;
            $gasto  = (float)$r->gasto;

            $costoKm    = $km > 0 ? $gasto / $km : 0;
            $precioProm = $litros > 0 ? $gasto / $litros : 0;

            return [
                'vehiculo_id'    => (int)$r->vehiculo_id,
                'placa'          => (string)$r->placa,
                'unidad'         => (string)$r->unidad,
                'vehiculo_label' => $fx->makeVehiculoLabel($r->unidad, $r->placa),
                'operador_id'    => $r->operador_id ? (int)$r->operador_id : null,
                'operador'       => (string)$r->operador_nombre,
                'litros'         => round($litros, 2),
                'gasto'          => round($gasto, 2),
                'km'             => round($km, 2),
                'costo_km'       => round($costoKm, 4),
                'precio_prom'    => round($precioProm, 3),
                'num_cargas'     => (int)$r->num_cargas,
            ];
        })->values()->all();

        $chart = [
            'categories' => collect($rows)->pluck('vehiculo_label')->all(),
            'series'     => [
                ['name' => '$ / km',     'data' => collect($rows)->pluck('costo_km')->all()],
                ['name' => '$ / L prom', 'data' => collect($rows)->pluck('precio_prom')->all()],
            ],
        ];

        return [
            'rows'  => $rows,
            'kpis'  => $fx->numbers($rows),
            'chart' => $chart,
        ];
    }
}
