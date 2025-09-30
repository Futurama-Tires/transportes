<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vehiculo;
use App\Models\Operador;
use App\Models\Verificacion;
use App\Services\PdfService;

class ReporteController extends Controller
{
    /** Vista dashboard */
    public function index(Request $request)
    {
        $vehiculosOptions = Vehiculo::query()
            ->select('id', 'placa', 'unidad', 'marca', 'anio', 'estado')
            ->orderBy('placa')
            ->get();

        $operadoresOptions = Operador::query()
            ->select('id', 'nombre', 'apellido_paterno', 'apellido_materno')
            ->orderBy('nombre')
            ->get();

        return view('reportes.index', compact('vehiculosOptions', 'operadoresOptions'));
    }

    /** ------------------------ Helpers ------------------------ */
    private function buildWhere(Request $r, array &$params): string
    {
        $w = [];
        if ($r->filled('desde')) { $w[] = "cc.fecha >= ?"; $params[] = $r->input('desde'); }
        if ($r->filled('hasta')) { $w[] = "cc.fecha <= ?"; $params[] = $r->input('hasta'); }

        $vehiculos = (array) $r->input('vehiculos', []);
        if (count($vehiculos)) {
            $w[] = "cc.vehiculo_id IN (" . implode(',', array_fill(0, count($vehiculos), '?')) . ")";
            array_push($params, ...$vehiculos);
        }

        $operadores = (array) $r->input('operadores', []);
        if (count($operadores)) {
            $w[] = "cc.operador_id IN (" . implode(',', array_fill(0, count($operadores), '?')) . ")";
            array_push($params, ...$operadores);
        }

        if ($r->filled('destino')) {
            $w[] = "cc.destino LIKE ?";
            $params[] = '%' . $r->input('destino') . '%';
        }

        if ($r->filled('tipo_comb')) {
            $w[] = "LOWER(cc.tipo_combustible) = LOWER(?)";
            $params[] = $r->input('tipo_comb');
        }

        return count($w) ? ('WHERE ' . implode(' AND ', $w)) : '';
    }

    /** Paginación en memoria para arreglos resultantes (por defecto 25) */
    private function paginate(array $rows, Request $r, int $defaultPerPage = 25): array
    {
        $page    = max(1, (int)$r->input('page', 1));
        $perPage = min(200, max(1, (int)$r->input('per_page', $defaultPerPage)));

        $total    = count($rows);
        $lastPage = (int) max(1, ceil($total / $perPage));
        $page     = min($page, $lastPage);
        $offset   = ($page - 1) * $perPage;

        $data = array_slice($rows, $offset, $perPage);

        return [
            'data' => array_values($data),
            'meta' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $lastPage,
                'from'         => $total ? ($offset + 1) : 0,
                'to'           => $total ? min($offset + $perPage, $total) : 0,
            ],
        ];
    }

    private function numbers(array $rows): array
    {
        $litros = 0; $gasto = 0; $km = 0;
        foreach ($rows as $r) {
            $litros += (float)($r['litros'] ?? 0);
            $gasto  += (float)($r['gasto'] ?? 0);
            $km     += (float)($r['km_recorridos'] ?? $r['km'] ?? 0);
        }
        $costoKm = $km > 0 ? $gasto / $km : 0;
        return [
            'litros'   => round($litros, 2),
            'gasto'    => round($gasto, 2),
            'km'       => round($km, 2),
            'costo_km' => round($costoKm, 4),
        ];
    }

    private function filtroResumen(Request $r): array
    {
        return [
            'desde'       => $r->input('desde'),
            'hasta'       => $r->input('hasta'),
            'vehiculos'   => (array)$r->input('vehiculos', []),
            'operadores'  => (array)$r->input('operadores', []),
            'destino'     => $r->input('destino'),
            'tipo_comb'   => $r->input('tipo_comb'),
            'anio'        => $r->input('anio'),
        ];
    }

    /** Helper: label "unidad - placa" con fallback decente */
    private function makeVehiculoLabel(?string $unidad, ?string $placa): string
    {
        $u = trim((string)($unidad ?? ''));
        $p = trim((string)($placa ?? ''));
        if ($u !== '' && $p !== '') return $u.' - '.$p;
        return $u !== '' ? $u : $p;
    }

    /** Normaliza chart_uri: acepta data URLs o base64 "pelón" y las convierte a data:image/png;base64, */
    private function normalizeChartUri(?string $raw): ?string
    {
        if (!$raw) return null;
        $raw = trim($raw);
        if (str_starts_with($raw, 'data:image/')) return $raw; // OK
        if (preg_match('#^https?://#i', $raw)) return $raw;    // Requiere isRemoteEnabled=true
        if (preg_match('/^[A-Za-z0-9+\/\=\r\n]+$/', $raw) && strlen($raw) > 200) {
            return 'data:image/png;base64,' . preg_replace('/\s+/', '', $raw);
        }
        return null;
    }

    /** ------------------------ 1) Rendimiento vs Índice ------------------------ */
    private function queryRendimiento(Request $r): array
    {
        $params = [];
        $where  = $this->buildWhere($r, $params);

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
                COUNT(*)        AS num_cargas,
                t.indice_estandar AS indice_estandar
            FROM cargas_combustible cc
            JOIN vehiculos v   ON v.id = cc.vehiculo_id
            LEFT JOIN operadores o ON o.id = cc.operador_id
            LEFT JOIN (
                SELECT vehiculo_id, MAX(rendimiento_estimado) AS indice_estandar
                FROM tanques
                GROUP BY vehiculo_id
            ) t ON t.vehiculo_id = v.id
            $where
            GROUP BY cc.vehiculo_id, v.placa, v.unidad, cc.operador_id, operador_nombre, t.indice_estandar
            ORDER BY v.placa ASC, operador_nombre ASC
        ";

        $rows = collect(DB::select($sql, $params))->map(function ($r) {
            $rendReal = ((float)$r->litros > 0) ? ((float)$r->km_recorridos / (float)$r->litros) : 0.0;
            $indice   = $r->indice_estandar !== null ? (float)$r->indice_estandar : null;
            $desvPct  = ($indice ?? 0) > 0 ? (($rendReal / $indice) - 1.0) * 100.0 : null;

            $vehiculo_label = $this->makeVehiculoLabel($r->unidad, $r->placa);

            return [
                'vehiculo_id'    => (int)$r->vehiculo_id,
                'placa'          => $r->placa,
                'unidad'         => $r->unidad,
                'vehiculo_label' => $vehiculo_label,
                'operador_id'    => $r->operador_id ? (int)$r->operador_id : null,
                'operador'       => $r->operador_nombre,
                'km_recorridos'  => round((float)$r->km_recorridos, 2),
                'litros'         => round((float)$r->litros, 2),
                'gasto'          => round((float)$r->gasto, 2),
                'num_cargas'     => (int)$r->num_cargas,
                'indice'         => $indice !== null ? round($indice, 2) : null,
                'rend_real'      => round($rendReal, 3),
                'desviacion_pct' => $desvPct !== null ? round($desvPct, 2) : null,
            ];
        })->values()->all();

        // Gráfica por vehículo (usar misma etiqueta)
        $byVehiculo = collect($rows)->groupBy('vehiculo_id')->map(function ($grp) {
            $km     = collect($grp)->sum('km_recorridos');
            $litros = collect($grp)->sum('litros');
            $rend   = $litros > 0 ? $km / $litros : 0;
            $indice = $grp->first()['indice'];
            return [
                'vehiculo_id'    => $grp->first()['vehiculo_id'],
                'placa'          => $grp->first()['placa'],
                'unidad'         => $grp->first()['unidad'],
                'vehiculo_label' => $grp->first()['vehiculo_label'],
                'rend_real'      => round($rend, 3),
                'indice'         => $indice !== null ? round($indice, 3) : null,
            ];
        })->values();

        $chart = [
            'categories' => $byVehiculo->pluck('vehiculo_label')->all(),
            'series'     => [
                ['name' => 'Rendimiento real (km/L)', 'data' => $byVehiculo->pluck('rend_real')->all()],
            ],
        ];
        if ($byVehiculo->pluck('indice')->filter(fn($x)=>$x !== null)->count()) {
            $chart['series'][] = ['name' => 'Índice estándar (km/L)', 'data' => $byVehiculo->pluck('indice')->map(fn($x)=>$x ?? 0)->all()];
        }

        return ['rows'=>$rows, 'kpis'=>$this->numbers($rows), 'chart'=>$chart];
    }

    public function rendimientoJson(Request $r)
    {
        $res = $this->queryRendimiento($r);
        $pag = $this->paginate($res['rows'], $r, 25);

        return response()->json([
            'kpis'       => $res['kpis'],
            'table'      => $pag['data'],        // <-- sólo esta página
            'pagination' => $pag['meta'],        // <-- metadatos de paginación
            'chart'      => $res['chart'],       // <-- gráfica con dataset completo (filtrado)
            'params'     => $r->all(),
        ]);
    }

    public function exportRendimientoPdf(Request $r, PdfService $pdf)
    {
        $res = $this->queryRendimiento($r);
        $data = [
            'titulo'    => 'Rendimiento vs Índice Estándar (km/L)',
            'filtros'   => $this->filtroResumen($r),
            'kpis'      => $res['kpis'],
            'rows'      => $res['rows'], // exporta todo (sin paginar)
            'chart_uri' => $this->normalizeChartUri($r->input('chart_uri')),
        ];
        return $pdf->streamFromView('reportes.pdf.rendimiento', $data, 'rendimiento-vs-indice.pdf', 'A4', 'portrait');
    }

    /** ------------------------ 2) Costo por km ------------------------ */
    private function queryCostoKm(Request $r): array
    {
        $params = [];
        $where  = $this->buildWhere($r, $params);

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

        $rows = collect(DB::select($sql, $params))->map(function ($r) {
            $km = (float)$r->km_recorridos;
            $litros = (float)$r->litros;
            $gasto = (float)$r->gasto;
            $costoKm = $km > 0 ? $gasto / $km : 0;
            $precioProm = $litros > 0 ? $gasto / $litros : 0;

            $vehiculo_label = $this->makeVehiculoLabel($r->unidad, $r->placa);

            return [
                'vehiculo_id'    => (int)$r->vehiculo_id,
                'placa'          => $r->placa,
                'unidad'         => $r->unidad,
                'vehiculo_label' => $vehiculo_label,
                'operador_id'    => $r->operador_id ? (int)$r->operador_id : null,
                'operador'       => $r->operador_nombre,
                'litros'         => round($litros, 2),
                'gasto'          => round($gasto, 2),
                'km'             => round($km, 2),
                'costo_km'       => round($costoKm, 4),
                'precio_prom'    => round($precioProm, 3),
                'num_cargas'     => (int)$r->num_cargas,
            ];
        })->values()->all();

        return ['rows'=>$rows, 'kpis'=>$this->numbers($rows)];
    }

    public function costoKmJson(Request $r)
    {
        $res = $this->queryCostoKm($r);
        $pag = $this->paginate($res['rows'], $r, 25);

        return response()->json([
            'kpis'       => $res['kpis'],
            'table'      => $pag['data'],
            'pagination' => $pag['meta'],
            'chart'      => [
                'categories' => collect($res['rows'])->pluck('vehiculo_label')->all(),
                'series'     => [
                    ['name' => '$ / km',     'data' => collect($res['rows'])->pluck('costo_km')->all()],
                    ['name' => '$ / L prom', 'data' => collect($res['rows'])->pluck('precio_prom')->all()],
                ],
            ],
            'params' => $r->all(),
        ]);
    }

    public function exportCostoKmPdf(Request $r, PdfService $pdf)
    {
        $res = $this->queryCostoKm($r);
        $data = [
            'titulo'    => 'Costo por km & Gasto de combustible',
            'filtros'   => $this->filtroResumen($r),
            'kpis'      => $res['kpis'],
            'rows'      => $res['rows'], // exporta todo (sin paginar)
            'chart_uri' => $this->normalizeChartUri($r->input('chart_uri')),
        ];
        return $pdf->streamFromView('reportes.pdf.costo_km', $data, 'costo-por-km.pdf', 'A4', 'portrait');
    }

    /** ------------------------ 3) Auditoría ------------------------ */
    private function queryAuditoria(Request $r): array
    {
        $params = [];
        $where  = $this->buildWhere($r, $params);

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

        $rows = collect(DB::select($sql, $params))->map(function ($r) {
            $flags = [];
            if ($r->flag_excede_capacidad) $flags[] = 'Excede capacidad';
            if ($r->flag_km_invertido)     $flags[] = 'KM invertido';
            if ($r->flag_precio_outlier)   $flags[] = 'Precio atípico';
            if ($r->flag_posible_duplicado)$flags[] = 'Posible duplicado';

            $vehiculo_label = $this->makeVehiculoLabel($r->unidad, $r->placa);

            $opName = trim((string)($r->operador_nombre ?? ''));
            $opName = $opName !== '' ? $opName : null;

            return [
                'id'            => (int)$r->id,
                'fecha'         => (string)$r->fecha,
                'vehiculo_id'   => (int)$r->vehiculo_id,
                'placa'         => $r->placa,
                'unidad'        => $r->unidad,
                'vehiculo_label'=> $vehiculo_label,
                'operador_id'   => $r->operador_id ? (int)$r->operador_id : null,
                'operador'      => $opName,
                'litros'        => round((float)$r->litros, 2),
                'precio'        => $r->precio !== null ? round((float)$r->precio, 3) : null,
                'total'         => round((float)$r->total, 2),
                'cap_litros'    => $r->capacidad_litros !== null ? round((float)$r->capacidad_litros, 1) : null,
                'km_inicial'    => $r->km_inicial,
                'km_final'      => $r->km_final,
                'flags'         => $flags,
            ];
        })->values()->all();

        return ['rows' => $rows];
    }

    public function auditoriaJson(Request $r)
    {
        $res = $this->queryAuditoria($r);
        $pag = $this->paginate($res['rows'], $r, 25);

        return response()->json([
            'kpis'       => ['litros' => null, 'gasto' => null, 'km' => null, 'costo_km' => null],
            'table'      => $pag['data'],
            'pagination' => $pag['meta'],
            'chart'      => ['categories' => [], 'series' => []],
            'params'     => $r->all(),
        ]);
    }

    public function exportAuditoriaPdf(Request $r, PdfService $pdf)
    {
        $res = $this->queryAuditoria($r);
        $data = [
            'titulo'    => 'Auditoría de cargas y anomalías',
            'filtros'   => $this->filtroResumen($r),
            'rows'      => $res['rows'], // exporta todo (hasta 500 por límite de consulta)
            'chart_uri' => $this->normalizeChartUri($r->input('chart_uri')),
        ];
        return $pdf->streamFromView('reportes.pdf.auditoria', $data, 'auditoria-cargas.pdf', 'A4', 'portrait');
    }

    /** ------------------------ 4) Verificación (simple por año) ------------------------ */
    private function queryVerificacion(Request $r): array
    {
        $anio = (int)($r->input('anio') ?: date('Y'));

        $vehiculoIds = (array)$r->input('vehiculos', []);
        $vehiculos = Vehiculo::query()
            ->select('id','placa','unidad','estado')
            ->when(count($vehiculoIds) > 0, fn($q)=>$q->whereIn('id', $vehiculoIds))
            ->orderBy('placa')
            ->get();

        $verifAprobadas = Verificacion::query()
            ->anio($anio)
            ->aprobada()
            ->select('vehiculo_id', DB::raw('MAX(fecha_verificacion) AS fecha_verificacion'))
            ->groupBy('vehiculo_id')
            ->pluck('fecha_verificacion', 'vehiculo_id');

        $rows = $vehiculos->map(function($v) use ($verifAprobadas, $anio) {
            $fecha = $verifAprobadas->get($v->id);
            $ok    = !empty($fecha);
            return [
                'vehiculo_id'        => (int)$v->id,
                'placa'              => $v->placa,
                'unidad'             => $v->unidad,
                'vehiculo_label'     => $this->makeVehiculoLabel($v->unidad, $v->placa),
                'estado'             => $v->estado,
                'anio'               => $anio,
                'estatus'            => $ok ? 'Verificado' : 'Sin verificar',
                'fecha_verificacion' => $ok ? (string)$fecha : null,
            ];
        })->values()->all();

        return [
            'rows' => $rows,
            'kpis' => [
                'total'         => count($rows),
                'verificados'   => collect($rows)->where('estatus','Verificado')->count(),
                'sin_verificar' => collect($rows)->where('estatus','Sin verificar')->count(),
            ],
        ];
    }

    public function verificacionJson(Request $r)
    {
        $res = $this->queryVerificacion($r);
        $pag = $this->paginate($res['rows'], $r, 25);

        return response()->json([
            'kpis'       => $res['kpis'],
            'table'      => $pag['data'],
            'rows'       => $pag['data'],   // si el front usa 'rows', lo mantenemos paginado también
            'pagination' => $pag['meta'],
            'chart'      => [
                'categories' => ['Verificado', 'Sin verificar'],
                'series'     => [[
                    'name' => 'Estatus',
                    'data' => [ $res['kpis']['verificados'], $res['kpis']['sin_verificar'] ]
                ]],
            ],
            'params' => $r->all(),
        ]);
    }

    public function exportVerificacionPdf(Request $r, PdfService $pdf)
    {
        $res = $this->queryVerificacion($r);
        $data = [
            'titulo'    => 'Verificaciones por año (Verificado / Sin verificar)',
            'filtros'   => $this->filtroResumen($r),
            'rows'      => $res['rows'], // exporta todo (sin paginar)
            'kpis'      => $res['kpis'],
            'chart_uri' => $this->normalizeChartUri($r->input('chart_uri')),
        ];
        return $pdf->streamFromView('reportes.pdf.verificacion', $data, 'verificacion-vencimientos.pdf', 'A4', 'portrait');
    }
}
