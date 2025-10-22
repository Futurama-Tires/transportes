<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

class AuditoriaReport
{
    /**
     * @return array{rows: array<int,array>}
     */
    public function run(ReportFilters $fx): array
    {
        $params = [];
        $where  = $fx->whereClauseCC($params);

        $sql = "
            WITH base AS (
                SELECT
                    cc.id, cc.fecha, cc.vehiculo_id, cc.operador_id,
                    cc.litros, cc.precio, cc.total,
                    cc.km_inicial, cc.km_final,
                    v.placa,
                    v.unidad,
                    t.capacidad_litros,
                    TRIM(CONCAT(o.nombre, ' ', o.apellido_paterno, ' ', COALESCE(o.apellido_materno,''))) AS operador_nombre
                FROM cargas_combustible cc
                JOIN vehiculos v ON v.id = cc.vehiculo_id
                LEFT JOIN operadores o ON o.id = cc.operador_id
                LEFT JOIN tanques t ON t.vehiculo_id = cc.vehiculo_id
                $where
            ),
            ord AS (
                SELECT
                    base.*,
                    (base.km_final IS NOT NULL AND base.km_inicial IS NOT NULL AND base.km_final < base.km_inicial) AS flag_km_invertido,
                    (base.capacidad_litros IS NOT NULL AND base.litros > base.capacidad_litros) AS flag_excede_capacidad,
                    COUNT(*) OVER (PARTITION BY base.vehiculo_id, base.fecha, base.litros, base.total) AS dup_count
                FROM base
            ),
            stats AS (
                SELECT AVG(precio) AS avg_p, STDDEV_SAMP(precio) AS sd_p FROM base WHERE precio IS NOT NULL
            )
            SELECT
                ord.id, ord.fecha, ord.placa, ord.unidad, ord.vehiculo_id, ord.operador_id,
                ord.operador_nombre,
                ord.litros, ord.precio, ord.total, ord.capacidad_litros,
                ord.km_inicial, ord.km_final, ord.dup_count,
                ord.flag_km_invertido,
                ord.flag_excede_capacidad,
                (CASE
                    WHEN ord.precio IS NULL THEN 0
                    WHEN (SELECT sd_p FROM stats) IS NULL THEN 0
                    WHEN ord.precio > (SELECT avg_p + 2*sd_p FROM stats) THEN 1
                    WHEN ord.precio < (SELECT avg_p - 2*sd_p FROM stats) THEN 1
                    ELSE 0
                END) AS flag_precio_outlier,
                (ord.dup_count > 1) AS flag_posible_duplicado
            FROM ord
            ORDER BY flag_excede_capacidad DESC, flag_km_invertido DESC, flag_precio_outlier DESC, ord.fecha DESC
            LIMIT 500
        ";

        $rows = collect(DB::select($sql, $params))->map(function ($r) use ($fx) {
            $flags = [];
            if ($r->flag_excede_capacidad) $flags[] = 'Excede capacidad';
            if ($r->flag_km_invertido)     $flags[] = 'KM invertido';
            if ($r->flag_precio_outlier)   $flags[] = 'Precio atípico';
            if ($r->flag_posible_duplicado)$flags[] = 'Posible duplicado';

            $opName = trim((string)($r->operador_nombre ?? ''));
            $opName = $opName !== '' ? $opName : null;

            return [
                'id'             => (int)$r->id,
                'fecha'          => (string)$r->fecha,
                'vehiculo_id'    => (int)$r->vehiculo_id,
                'placa'          => (string)$r->placa,
                'unidad'         => (string)$r->unidad,
                'vehiculo_label' => $fx->makeVehiculoLabel($r->unidad, $r->placa),
                'operador_id'    => $r->operador_id ? (int)$r->operador_id : null,
                'operador'       => $opName,
                'litros'         => $r->litros !== null ? round((float)$r->litros, 2) : null,
                'precio'         => $r->precio !== null ? round((float)$r->precio, 3) : null,
                'total'          => $r->total !== null ? round((float)$r->total, 2) : null,
                'cap_litros'     => $r->capacidad_litros !== null ? round((float)$r->capacidad_litros, 1) : null,
                'km_inicial'     => $r->km_inicial,
                'km_final'       => $r->km_final,
                'flags'          => $flags,
            ];
        })->values()->all();

        return ['rows' => $rows];
    }
}
