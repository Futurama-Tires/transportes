{{-- resources/views/reportes/index.blade.php --}}
<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $vehiculosOptions  = $vehiculosOptions  ?? collect();
        $operadoresOptions = $operadoresOptions ?? collect();

        $q = request()->all();
        $fechaDesde = $q['desde'] ?? '';
        $fechaHasta = $q['hasta'] ?? '';
        $vehiculosQ = collect($q['vehiculos'] ?? []);
        $operadoresQ = collect($q['operadores'] ?? []);
        $destino = $q['destino'] ?? '';
        $tipoComb = strtolower($q['tipo_comb'] ?? '');
        $anio = (int)($q['anio'] ?? date('Y'));
        $anioMin = $anio - 2; $anioMax = $anio + 2;

        // Mapas para usar del lado del cliente (por id y por placa)
        $vehById = $vehiculosOptions->mapWithKeys(function($v){
            return [$v->id => ['unidad' => $v->unidad, 'placa' => $v->placa]];
        });
        $vehByPlaca = $vehiculosOptions
            ->filter(fn($v) => !empty($v->placa))
            ->mapWithKeys(function($v){
                return [$v->placa => ['unidad' => $v->unidad, 'placa' => $v->placa]];
            });
    @endphp

    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <p class="text-secondary text-uppercase small mb-1">Reportes</p>
                    <h2 class="page-title mb-0">Dashboard de Reportes</h2>
                    <div class="text-secondary small mt-1">Filtros globales + 4 pestañas (carga bajo demanda).</div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            {{-- FILTROS (no sticky) --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form id="filtrosForm" class="row g-3" method="GET" action="{{ route('reportes.index') }}">
                        <div class="col-12 col-md-3">
                            <label class="form-label">Desde</label>
                            <input type="date" name="desde" value="{{ $fechaDesde }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Hasta</label>
                            <input type="date" name="hasta" value="{{ $fechaHasta }}" class="form-control">
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Vehículos</label>
                            <select name="vehiculos[]" class="form-select" multiple size="4">
                                @foreach($vehiculosOptions as $v)
                                    <option value="{{ $v->id }}" @selected($vehiculosQ->contains($v->id))>
                                        {{-- "unidad - placa" con fallback decente --}}
                                        {{ ($v->unidad && $v->placa) ? ($v->unidad.' - '.$v->placa) : ($v->unidad ?? ($v->placa ?? '#'.$v->id)) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Ctrl/Cmd + click para selección múltiple.</div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Operadores</label>
                            <select name="operadores[]" class="form-select" multiple size="4">
                                @foreach($operadoresOptions as $o)
                                    <option value="{{ $o->id }}" @selected($operadoresQ->contains($o->id))>
                                        {{ trim(($o->nombre ?? '').' '.($o->apellido_paterno ?? '')) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Destino</label>
                            <input type="text" name="destino" value="{{ $destino }}" class="form-control" placeholder="Texto libre">
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Tipo de combustible</label>
                            <select name="tipo_comb" class="form-select">
                                <option value="">— Todos —</option>
                                <option value="magna"   @selected($tipoComb==='magna')>Magna</option>
                                <option value="diesel"  @selected($tipoComb==='diesel')>Diésel</option>
                                <option value="premium" @selected($tipoComb==='premium')>Premium</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Año (verificación)</label>
                            <input type="number" name="anio" class="form-control" value="{{ $anio }}" min="{{ $anioMin }}" max="{{ $anioMax }}">
                            <div class="form-text">Aplica al reporte de Verificación.</div>
                        </div>

                        <div class="col-12 col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- KPIs (para tabs 1 y 2) --}}
            <div class="row g-3 mb-3" id="kpisRow">
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm"><div class="card-body">
                        <div class="subheader">Litros</div>
                        <div class="h2 mb-0" id="kpiLitros">—</div>
                        <div class="text-secondary">con filtros aplicados</div>
                    </div></div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm"><div class="card-body">
                        <div class="subheader">Gasto $</div>
                        <div class="h2 mb-0" id="kpiGasto">—</div>
                        <div class="text-secondary">total del periodo</div>
                    </div></div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm"><div class="card-body">
                        <div class="subheader">Km recorridos</div>
                        <div class="h2 mb-0" id="kpiKm">—</div>
                        <div class="text-secondary">estimado</div>
                    </div></div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card card-sm"><div class="card-body">
                        <div class="subheader">Costo por km</div>
                        <div class="h2 mb-0" id="kpiCostoKm">—</div>
                        <div class="text-secondary">($ / km)</div>
                    </div></div>
                </div>
            </div>

            {{-- TABS --}}
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="tab-rendimiento" data-tab="rendimiento" href="#">1) Rendimiento vs Índice</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-costokm" data-tab="costokm" href="#">2) Costo por km & Gasto</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-auditoria" data-tab="auditoria" href="#">3) Auditoría de cargas</a></li>
                        <li class="nav-item"><a class="nav-link" id="tab-verificacion" data-tab="verificacion" href="#">4) Verificación (anual)</a></li>
                    </ul>
                </div>

                <div class="card-body">
                    {{-- Tab 1: Rendimiento --}}
                    <div data-panel="rendimiento" class="report-panel">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="card-title mb-0">Rendimiento vs Índice Estándar (km/L)</h3>
                            <div class="btn-group">
                                <a id="exp-rendimiento-pdf" href="#" class="btn btn-outline-secondary" aria-disabled="true">Exportar PDF</a>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div id="chart-rendimiento" style="height: 320px;">
                                    <div class="text-secondary">[Gráfica aquí]</div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="tbl-rendimiento">
                                <thead>
                                <tr>
                                    <th>Vehículo</th>
                                    <th>Operador</th>
                                    <th>Kilómetros</th>
                                    <th>Litros</th>
                                    <th>Rend. real</th>
                                    <th>Índice</th>
                                    <th>Desviación %</th>
                                    <th># Cargas</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td colspan="8" class="text-secondary">[Datos se cargan al activar la pestaña]</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tab 2: Costo por km --}}
                    <div data-panel="costokm" class="report-panel d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="card-title mb-0">Costo por km & Gasto</h3>
                            <div class="btn-group">
                                <a id="exp-costokm-pdf" href="#" class="btn btn-outline-secondary" aria-disabled="true">Exportar PDF</a>
                            </div>
                        </div>
                        <div class="card mb-3"><div class="card-body">
                            <div id="chart-costokm" style="height: 320px;">
                                <div class="text-secondary">[Gráfica aquí]</div>
                            </div>
                        </div></div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="tbl-costokm">
                                <thead>
                                <tr>
                                    <th>Vehículo</th>
                                    <th>Operador</th>
                                    <th>Litros</th>
                                    <th>Gasto $</th>
                                    <th>Kilómetros</th>
                                    <th>$ / km</th>
                                    <th>$ / L prom.</th>
                                    <th># Cargas</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td colspan="8" class="text-secondary">[Datos se cargan al activar la pestaña]</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tab 3: Auditoría --}}
                    <div data-panel="auditoria" class="report-panel d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="card-title mb-0">Auditoría de cargas y anomalías</h3>
                            <div class="btn-group">
                                <a id="exp-auditoria-pdf" href="#" class="btn btn-outline-secondary" aria-disabled="true">Exportar PDF</a>
                            </div>
                        </div>

                        <div class="card mb-3"><div class="card-body">
                            <div id="chart-auditoria" style="height: 320px;">
                                <div class="text-secondary">[Gráfica aquí]</div>
                            </div>
                        </div></div>

                        {{-- Estado del filtro por click en la gráfica --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-secondary small">
                                Mostrando: <span id="auditoria-filter-badge" class="badge bg-secondary">Todas</span>
                            </div>
                            <div class="btn-group">
                                <button id="auditoria-clear-filter" class="btn btn-sm btn-outline-secondary" disabled>Quitar filtro</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped" id="tbl-auditoria">
                                <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Vehículo</th>
                                    <th>Operador</th>
                                    <th>Litros</th>
                                    <th>$ / L</th>
                                    <th>Total $</th>
                                    <th>Capacidad</th>
                                    <th>Flags</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td colspan="8" class="text-secondary">[Datos se cargan al activar la pestaña]</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tab 4: Verificación (simple) --}}
                    <div data-panel="verificacion" class="report-panel d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="card-title mb-0">Verificación anual: Verificados vs Sin verificar</h3>
                            <div class="btn-group">
                                <a id="exp-verificacion-pdf" href="#" class="btn btn-outline-secondary" aria-disabled="true">Exportar PDF</a>
                            </div>
                        </div>
                        <div class="card mb-3"><div class="card-body">
                            <div id="chart-verificacion" style="height: 320px;">
                                <div class="text-secondary">[Gráfica aquí]</div>
                            </div>
                        </div></div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="tbl-verificacion">
                                <thead>
                                <tr>
                                    <th>Vehículo</th>
                                    <th>Año</th>
                                    <th>Estatus</th>
                                    <th>Fecha verif.</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td colspan="4" class="text-secondary">[Datos se cargan al activar la pestaña]</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div> {{-- /card-body --}}
            </div> {{-- /card --}}

        </div>
    </div>

    <script>
    (function() {
      const tabs   = document.querySelectorAll('#reportTabs a.nav-link');
      const panels = document.querySelectorAll('.report-panel');

      const endpoints = {
        rendimiento:  '/api/reportes/rendimiento',
        costokm:      '/api/reportes/costo-km',
        auditoria:    '/api/reportes/auditoria',
        verificacion: '/api/reportes/verificacion',
      };

      // ====== Mapas de vehículos inyectados desde PHP ======
      const VEH_BY_ID    = @json($vehById, JSON_UNESCAPED_UNICODE);
      const VEH_BY_PLACA = @json($vehByPlaca, JSON_UNESCAPED_UNICODE);

      // ===== util: etiqueta "unidad - placa" (con fallback) =====
      function vehLabelFrom(unidad, placa) {
        const u = (unidad ?? '').toString().trim();
        const p = (placa ?? '').toString().trim();
        return (u && p) ? `${u} - ${p}` : (u || p || '');
      }
      function vehLabel(row) {
        // 1) Si la API ya manda unidad y placa
        if (row && (row.unidad || row.placa)) {
          const lbl = vehLabelFrom(row.unidad, row.placa);
          if (lbl) return lbl;
        }
        // 2) Buscar por vehiculo_id
        const vid = row?.vehiculo_id;
        if (vid && VEH_BY_ID[vid]) {
          return vehLabelFrom(VEH_BY_ID[vid].unidad, VEH_BY_ID[vid].placa);
        }
        // 3) Buscar por placa
        const p = row?.placa;
        if (p && VEH_BY_PLACA[p]) {
          return vehLabelFrom(VEH_BY_PLACA[p].unidad, VEH_BY_PLACA[p].placa);
        }
        // 4) Fallback
        return row?.placa ?? '';
      }

      function qsParams() {
        const form = document.getElementById('filtrosForm');
        const fd = new FormData(form);
        const params = new URLSearchParams();
        for (const [k, v] of fd.entries()) { if (v !== '') params.append(k, v); }
        form.querySelectorAll('select[multiple]').forEach(sel=>{
          const base = sel.name.replace('[]','');
          const vals = Array.from(sel.selectedOptions).map(o=>o.value);
          if (vals.length) vals.forEach(val=>params.append(base+'[]', val));
        });
        return params;
      }
      function filtrosObj() { return Object.fromEntries(qsParams().entries()); }
      function showPanel(key) { panels.forEach(p => p.classList.toggle('d-none', p.dataset.panel !== key)); }
      function postWithData(url, data) {
        const form = document.createElement('form');
        form.method = 'POST'; form.action = url;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const inpCsrf = document.createElement('input');
        inpCsrf.type = 'hidden'; inpCsrf.name = '_token'; inpCsrf.value = token; form.appendChild(inpCsrf);
        Object.entries(data || {}).forEach(([k,v])=>{
          if (Array.isArray(v)) v.forEach(val=>{ const i=document.createElement('input'); i.type='hidden'; i.name=k+'[]'; i.value=val; form.appendChild(i); });
          else if (v !== undefined && v !== null) { const i=document.createElement('input'); i.type='hidden'; i.name=k; i.value=v; form.appendChild(i); }
        });
        document.body.appendChild(form); form.submit();
      }

      // ========= CHART: Rendimiento =========
      window.chartRend = window.chartRend || null;
      function renderRendChart(payload) {
        const el = document.querySelector('#chart-rendimiento'); if (!el) return;
        const series = payload?.chart?.series || [];
        const cats   = payload?.chart?.categories || [];
        const opts = {
          chart: { type: 'line', height: 320, toolbar: { show: false } },
          series,
          xaxis: { categories: cats },
          stroke: { width: [0, 3] },
          plotOptions: { bar: { columnWidth: '55%' } },
          dataLabels: { enabled: false },
          yaxis: { title: { text: 'km/L' } },
          legend: { position: 'top' }
        };
        if (series.length >= 2) { opts.series[0].type='column'; opts.series[1].type='line'; }
        if (window.chartRend) window.chartRend.destroy();
        window.chartRend = new ApexCharts(el, opts); window.chartRend.render();
      }

      // ========= CHART: Costo por km =========
      window.chartCkm = window.chartCkm || null;
      function renderCostokmChart(payload) {
        const el = document.querySelector('#chart-costokm'); if (!el) return;
        const series = payload?.chart?.series || [];
        const cats   = payload?.chart?.categories || [];
        const opts = {
          chart: { type: 'line', height: 320, toolbar: { show: false } },
          series,
          xaxis: { categories: cats },
          dataLabels: { enabled: false },
          stroke: { width: [0,3] },
          plotOptions: { bar: { columnWidth: '55%' } },
          yaxis: [
            { title: { text: '$ / km' } },
            { opposite: true, title: { text: '$ / L prom' } }
          ],
          legend: { position: 'top' }
        };
        if (series.length >= 2) { opts.series[0].type='column'; opts.series[1].type='line'; opts.series[1].yAxisIndex = 1; }
        if (window.chartCkm) window.chartCkm.destroy();
        window.chartCkm = new ApexCharts(el, opts); window.chartCkm.render();
      }

      // ======== AUDITORÍA: estado y helpers de filtrado por click ========
      const AUD_FLAGS = ['Excede capacidad','KM invertido','Precio atípico','Posible duplicado'];
      let auditoriaRowsAll = [];
      let auditoriaActiveFlag = null;

      function updateAuditoriaFilterUI() {
        const badge = document.getElementById('auditoria-filter-badge');
        const btn   = document.getElementById('auditoria-clear-filter');
        if (!badge || !btn) return;
        if (auditoriaActiveFlag) {
          badge.textContent = auditoriaActiveFlag;
          badge.className = 'badge bg-primary';
          btn.disabled = false;
        } else {
          badge.textContent = 'Todas';
          badge.className = 'badge bg-secondary';
          btn.disabled = true;
        }
      }

      function renderAuditoriaTable(rows) {
        const tbody = document.querySelector('[data-panel="auditoria"] tbody');
        if (!tbody) return;
        if (!rows || !rows.length) {
          tbody.innerHTML = `<tr><td colspan="8" class="text-secondary">Sin datos.</td></tr>`;
          return;
        }
        tbody.innerHTML = rows.map(r => `
          <tr>
            <td>${r.fecha}</td>
            <td>${vehLabel(r)}</td>
            <td>${r.operador ?? r.operador_id ?? ''}</td>
            <td>${r.litros}</td>
            <td>${r.precio ?? ''}</td>
            <td>${r.total}</td>
            <td>${r.cap_litros ?? ''}</td>
            <td>${(r.flags || []).join(', ')}</td>
          </tr>`).join('');
      }

      function setAuditoriaFilter(flag) {
        auditoriaActiveFlag = flag || null;
        const filtered = auditoriaActiveFlag
          ? (auditoriaRowsAll || []).filter(r => (r.flags || []).includes(auditoriaActiveFlag))
          : auditoriaRowsAll;
        renderAuditoriaTable(filtered);
        updateAuditoriaFilterUI();
      }

      // ========= CHART: Auditoría (con click para filtrar tabla) =========
      window.chartAud = window.chartAud || null;
      function renderAuditoriaChart(rows) {
        const el = document.querySelector('#chart-auditoria'); if (!el) return;

        auditoriaRowsAll = rows || [];
        const counts = { 'Excede capacidad':0, 'KM invertido':0, 'Precio atípico':0, 'Posible duplicado':0 };
        auditoriaRowsAll.forEach(r => (r.flags||[]).forEach(f => { if (counts[f] !== undefined) counts[f]++; }));
        const data = AUD_FLAGS.map(f => counts[f]);

        const opts = {
          chart: {
            type: 'bar',
            height: 320,
            toolbar: { show: false },
            events: {
              dataPointSelection: function(event, chartContext, config) {
                const idx = config?.dataPointIndex ?? -1;
                if (idx < 0 || idx >= AUD_FLAGS.length) return;
                const clicked = AUD_FLAGS[idx];
                setAuditoriaFilter(auditoriaActiveFlag === clicked ? null : clicked);
              }
            }
          },
          series: [{ name: 'Incidencias', data }],
          xaxis: { categories: AUD_FLAGS },
          dataLabels: { enabled: true },
          legend: { show: false },
          plotOptions: { bar: { columnWidth: '55%' } },
          tooltip: { y: { formatter: (val) => `${val} registro${val===1?'':'s'}` } }
        };

        if (window.chartAud) window.chartAud.destroy();
        window.chartAud = new ApexCharts(el, opts);
        window.chartAud.render();

        setAuditoriaFilter(null);

        const btn = document.getElementById('auditoria-clear-filter');
        if (btn && !btn._hasHandler) {
          btn.addEventListener('click', (e)=>{ e.preventDefault(); setAuditoriaFilter(null); });
          btn._hasHandler = true;
        }
      }

      // ========= CHART: Verificación (Verificados vs Sin verificar) =========
      window.chartVer = window.chartVer || null;
      function renderVerificacionChart(rows) {
        const el = document.querySelector('#chart-verificacion'); if (!el) return;

        const counts = { 'Verificado': 0, 'Sin verificar': 0 };
        (rows || []).forEach(r => {
          const k = (r.estatus === 'Verificado') ? 'Verificado' : 'Sin verificar';
          counts[k] = (counts[k] || 0) + 1;
        });
        const labels = Object.keys(counts);
        const data   = labels.map(k => counts[k]);
        const total  = data.reduce((a,b)=>a+b,0);

        let opts;
        if (total === 0 || labels.length <= 1) {
          opts = {
            chart: { type: 'bar', height: 320, toolbar: { show: false } },
            series: [{ name: 'Vehículos', data }],
            xaxis: { categories: labels },
            dataLabels: { enabled: true },
            legend: { show: false }
          };
        } else {
          opts = {
            chart: { type: 'pie', height: 320, toolbar: { show: false } },
            series: data,
            labels: labels,
            legend: { position: 'bottom' },
            dataLabels: { enabled: true }
          };
        }

        if (window.chartVer) window.chartVer.destroy();
        window.chartVer = new ApexCharts(el, opts); window.chartVer.render();
      }

      async function loadTab(key) {
        showPanel(key);
        const panel = document.querySelector(`[data-panel="${key}"]`);
        panel.querySelectorAll('tbody').forEach(t => t.innerHTML = `<tr><td colspan="99" class="text-secondary">Cargando…</td></tr>`);
        const url = endpoints[key] + '?' + qsParams().toString();
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const payload = await res.json();

        const tbody = panel.querySelector('tbody');

        // KPIs (solo para tabs 1 y 2)
        if (['rendimiento','costokm'].includes(key) && payload.kpis) {
          if ('litros'   in payload.kpis) document.getElementById('kpiLitros').textContent   = payload.kpis.litros ?? '—';
          if ('gasto'    in payload.kpis) document.getElementById('kpiGasto').textContent    = payload.kpis.gasto ?? '—';
          if ('km'       in payload.kpis) document.getElementById('kpiKm').textContent       = payload.kpis.km ?? '—';
          if ('costo_km' in payload.kpis) document.getElementById('kpiCostoKm').textContent  = payload.kpis.costo_km ?? '—';
        }

        if (key === 'rendimiento') {
          const rows = payload.table || payload.rows || [];
          if (!rows.length) tbody.innerHTML = `<tr><td colspan="8" class="text-secondary">Sin datos.</td></tr>`;
          else tbody.innerHTML = rows.map(r => `
            <tr>
              <td>${vehLabel(r)}</td>
              <td>${r.operador ?? ''}</td>
              <td>${r.km_recorridos}</td><td>${r.litros}</td>
              <td>${r.rend_real}</td><td>${r.indice ?? ''}</td>
              <td>${r.desviacion_pct ?? ''}</td><td>${r.num_cargas}</td>
            </tr>`).join('');
          renderRendChart(payload);
        }

        else if (key === 'costokm') {
          const rows = payload.table || payload.rows || [];
          if (!rows.length) tbody.innerHTML = `<tr><td colspan="8" class="text-secondary">Sin datos.</td></tr>`;
          else tbody.innerHTML = rows.map(r => `
            <tr>
              <td>${vehLabel(r)}</td>
              <td>${r.operador ?? ''}</td>
              <td>${r.litros}</td><td>${r.gasto}</td><td>${r.km}</td>
              <td>${r.costo_km}</td><td>${r.precio_prom}</td><td>${r.num_cargas}</td>
            </tr>`).join('');
          renderCostokmChart(payload);
        }

        else if (key === 'auditoria') {
          const rows = payload.table || payload.rows || [];
          renderAuditoriaChart(rows); // la tabla se llena con vehLabel(r)
        }

        else if (key === 'verificacion') {
          const rows = payload.table || payload.rows || [];
          if (!rows.length) tbody.innerHTML = `<tr><td colspan="4" class="text-secondary">Sin datos.</td></tr>`;
          else tbody.innerHTML = rows.map(r => `
            <tr>
              <td>${vehLabel(r)}</td>
              <td>${r.anio ?? ''}</td>
              <td>${r.estatus ?? ''}</td>
              <td>${r.fecha_verificacion ?? ''}</td>
            </tr>`).join('');
          renderVerificacionChart(rows);
        }

        // Actualiza URL (sin recargar)
        const params = qsParams().toString();
        const newUrl = `{{ route('reportes.index') }}?${params}#${key}`;
        window.history.replaceState({}, '', newUrl);
      }

      // Exportar PDF con imagen del gráfico
      function attachExportWithChart(key, chartRefGetter) {
        const btn = document.getElementById(`exp-${key}-pdf`);
        if (!btn) return;
        btn.addEventListener('click', async (e)=>{
          e.preventDefault();
          const url = '/reportes/' + (key === 'costokm' ? 'costo-km' : key) + '/export.pdf';
          let chartUri = null;
          try {
            const ref = chartRefGetter?.();
            if (ref && typeof ref.dataURI === 'function') {
              const { imgURI } = await ref.dataURI();
              chartUri = imgURI || null;
            }
          } catch(_) { /* noop */ }
          postWithData(url, Object.assign({}, filtrosObj(), { chart_uri: chartUri }));
        });
      }

      attachExportWithChart('rendimiento',  () => window.chartRend);
      attachExportWithChart('costokm',      () => window.chartCkm);
      attachExportWithChart('auditoria',    () => window.chartAud);
      attachExportWithChart('verificacion', () => window.chartVer);

      // Eventos de tabs
      tabs.forEach(tab=>{
        tab.addEventListener('click', (e)=>{
          e.preventDefault();
          tabs.forEach(t=>t.classList.remove('active'));
          tab.classList.add('active');
          loadTab(tab.dataset.tab);
        });
      });

      // Autocarga
      const hash = (location.hash||'').replace('#','');
      const first = hash && endpoints[hash] ? hash : 'rendimiento';
      document.querySelector(`#tab-${first}`)?.classList.add('active');
      loadTab(first);
    })();
    </script>
</x-app-layout>
