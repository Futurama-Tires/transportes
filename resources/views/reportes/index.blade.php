{{-- resources/views/reportes/index.blade.php --}}
<x-app-layout>
  
    {{-- ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @php
        $vehiculosOptions  = $vehiculosOptions  ?? collect();
        $operadoresOptions = $operadoresOptions ?? collect();

        $q          = request()->all();
        $fechaDesde = $q['desde'] ?? '';
        $fechaHasta = $q['hasta'] ?? '';
        $vehiculosQ = collect($q['vehiculos'] ?? []);
        $operadoresQ = collect($q['operadores'] ?? []);
        $destino    = $q['destino'] ?? '';
        $tipoComb   = strtolower($q['tipo_comb'] ?? '');
        $anio       = (int)($q['anio'] ?? date('Y'));
        $anioMin    = $anio - 2; $anioMax = $anio + 2;

        // Mapas para JS
        $vehById = $vehiculosOptions->mapWithKeys(fn($v)=>[
            $v->id => ['unidad' => $v->unidad, 'placa' => $v->placa]
        ]);
        $vehByPlaca = $vehiculosOptions->filter(fn($v)=>!empty($v->placa))
            ->mapWithKeys(fn($v)=>[
                $v->placa => ['unidad' => $v->unidad, 'placa' => $v->placa]
            ]);
    @endphp

    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a>Inicio</a></li>
                        <li class="breadcrumb-item"><a>Panel</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Reportes</li>
                    </ol>
                    <div class="col">
                        <h2 class="page-title mb-0">Reportes — Dashboard</h2>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="#filtrosForm" class="btn btn-danger">
                            <i class="ti ti-adjustments me-1"></i><span>Filtros</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- FILTROS --}}
            @include('reportes.partials._filters', [
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
                'vehiculosOptions' => $vehiculosOptions,
                'vehiculosQ' => $vehiculosQ,
                'operadoresOptions' => $operadoresOptions,
                'operadoresQ' => $operadoresQ,
                'destino' => $destino,
                'tipoComb' => $tipoComb,
                'anio' => $anio,
                'anioMin' => $anioMin,
                'anioMax' => $anioMax,
            ])

            {{-- KPIs --}}
            @include('reportes.partials._kpis')

            {{-- TABS --}}
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="tab-rendimiento" data-tab="rendimiento" href="javascript:void(0)">1) Rendimiento vs Índice</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-costokm" data-tab="costokm" href="javascript:void(0)">2) Costo por km & Gasto</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-auditoria" data-tab="auditoria" href="javascript:void(0)">3) Auditoría de cargas</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-verificacion" data-tab="verificacion" href="javascript:void(0)">4) Verificación (anual)</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    @include('reportes.tabs._rendimiento')
                    @include('reportes.tabs._costokm')
                    @include('reportes.tabs._auditoria')
                    @include('reportes.tabs._verificacion')
                </div>
            </div>
        </div>
    </div>

    {{-- Boot: datos mínimos para el JS externo --}}
    <script>
      window.REPORTES_BOOT = {
        csrf: @json(csrf_token()),
        indexUrl: @json(route('reportes.index')),
        endpoints: {
          rendimiento:  '/api/reportes/rendimiento',
          costokm:      '/api/reportes/costo-km',
          auditoria:    '/api/reportes/auditoria',
          verificacion: '/api/reportes/verificacion',
          exportBase:   '/reportes' // + '/{key}/export.pdf'
        },
        vehById:    @json($vehById, JSON_UNESCAPED_UNICODE),
        vehByPlaca: @json($vehByPlaca, JSON_UNESCAPED_UNICODE),
      };
    </script>



</x-app-layout>
