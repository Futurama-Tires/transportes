<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculo;
use App\Models\Operador;
use App\Services\PdfService;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\RendimientoReport;
use App\Services\Reports\CostoKmReport;
use App\Services\Reports\AuditoriaReport;
use App\Services\Reports\VerificacionReport;

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

    /** ------------------------ 1) Rendimiento vs Índice ------------------------ */
    public function rendimientoJson(Request $r, RendimientoReport $svc)
    {
        $fx  = ReportFilters::fromRequest($r);
        $res = $svc->run($fx);

        $page    = (int) $r->input('page', 1);
        $perPage = (int) $r->input('per_page', 25);
        $pag     = $fx->paginate($res['rows'], $perPage, $page);

        return response()->json([
            'kpis'       => $res['kpis'],
            'table'      => $pag['data'],
            'pagination' => $pag['meta'],
            'chart'      => $res['chart'] ?? ['categories' => [], 'series' => []],
            'params'     => $r->all(),
        ]);
    }

    public function exportRendimientoPdf(Request $r, PdfService $pdf, RendimientoReport $svc)
    {
        $fx   = ReportFilters::fromRequest($r);
        $res  = $svc->run($fx);
        $data = [
            'titulo'    => 'Rendimiento vs Índice Estándar (km/L)',
            'filtros'   => $fx->filtroResumen(),
            'kpis'      => $res['kpis'],
            'rows'      => $res['rows'],
            'chart_uri' => $fx->normalizeChartUri($r->input('chart_uri')),
        ];

        return $pdf->streamFromView('reportes.pdf.rendimiento', $data, 'rendimiento-vs-indice.pdf', 'A4', 'portrait');
    }

    /** ------------------------ 2) Costo por km ------------------------ */
    public function costoKmJson(Request $r, CostoKmReport $svc)
    {
        $fx  = ReportFilters::fromRequest($r);
        $res = $svc->run($fx);

        $page    = (int) $r->input('page', 1);
        $perPage = (int) $r->input('per_page', 25);
        $pag     = $fx->paginate($res['rows'], $perPage, $page);

        return response()->json([
            'kpis'       => $res['kpis'],
            'table'      => $pag['data'],
            'pagination' => $pag['meta'],
            'chart'      => $res['chart'] ?? ['categories' => [], 'series' => []],
            'params'     => $r->all(),
        ]);
    }

    public function exportCostoKmPdf(Request $r, PdfService $pdf, CostoKmReport $svc)
    {
        $fx   = ReportFilters::fromRequest($r);
        $res  = $svc->run($fx);
        $data = [
            'titulo'    => 'Costo por km & Gasto de combustible',
            'filtros'   => $fx->filtroResumen(),
            'kpis'      => $res['kpis'],
            'rows'      => $res['rows'],
            'chart_uri' => $fx->normalizeChartUri($r->input('chart_uri')),
        ];

        return $pdf->streamFromView('reportes.pdf.costo_km', $data, 'costo-por-km.pdf', 'A4', 'portrait');
    }

    /** ------------------------ 3) Auditoría ------------------------ */
    public function auditoriaJson(Request $r, AuditoriaReport $svc)
    {
        $fx  = ReportFilters::fromRequest($r);
        $res = $svc->run($fx);

        $page    = (int) $r->input('page', 1);
        $perPage = (int) $r->input('per_page', 25);
        $pag     = $fx->paginate($res['rows'], $perPage, $page);

        return response()->json([
            'kpis'       => ['litros' => null, 'gasto' => null, 'km' => null, 'costo_km' => null],
            'table'      => $pag['data'],
            'pagination' => $pag['meta'],
            'chart'      => ['categories' => [], 'series' => []],
            'params'     => $r->all(),
        ]);
    }

    public function exportAuditoriaPdf(Request $r, PdfService $pdf, AuditoriaReport $svc)
    {
        $fx   = ReportFilters::fromRequest($r);
        $res  = $svc->run($fx);
        $data = [
            'titulo'    => 'Auditoría de cargas y anomalías',
            'filtros'   => $fx->filtroResumen(),
            'rows'      => $res['rows'],
            'chart_uri' => $fx->normalizeChartUri($r->input('chart_uri')),
        ];

        return $pdf->streamFromView('reportes.pdf.auditoria', $data, 'auditoria-cargas.pdf', 'A4', 'portrait');
    }

    /** ------------------------ 4) Verificación ------------------------ */
    public function verificacionJson(Request $r, VerificacionReport $svc)
    {
        $fx  = ReportFilters::fromRequest($r);
        $res = $svc->run($fx);

        $page    = (int) $r->input('page', 1);
        $perPage = (int) $r->input('per_page', 25);
        $pag     = $fx->paginate($res['rows'], $perPage, $page);

        return response()->json([
            'kpis'       => $res['kpis'],
            'table'      => $pag['data'],
            'rows'       => $pag['data'], // compat front
            'pagination' => $pag['meta'],
            'chart'      => $res['chart'] ?? ['categories' => [], 'series' => []],
            'params'     => $r->all(),
        ]);
    }

    public function exportVerificacionPdf(Request $r, PdfService $pdf, VerificacionReport $svc)
    {
        $fx   = ReportFilters::fromRequest($r);
        $res  = $svc->run($fx);
        $data = [
            'titulo'    => 'Verificaciones por año (Verificado / Parcial / Sin verificar)',
            'filtros'   => $fx->filtroResumen(),
            'rows'      => $res['rows'],
            'kpis'      => $res['kpis'],
            'chart_uri' => $fx->normalizeChartUri($r->input('chart_uri')),
        ];

        return $pdf->streamFromView('reportes.pdf.verificacion', $data, 'verificacion-vencimientos.pdf', 'A4', 'portrait');
    }
}
