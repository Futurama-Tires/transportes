// resources/js/reportes/index.js
(() => {
  const BOOT = window.REPORTES_BOOT || {};
  const endpoints = BOOT.endpoints || {};
  const VEH_BY_ID = BOOT.vehById || {};
  const VEH_BY_PLACA = BOOT.vehByPlaca || {};
  const INDEX_URL = BOOT.indexUrl || '/reportes';
  const CSRF = BOOT.csrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // === helpers pequeños ===
  function clamp(n,min,max){ return Math.max(min, Math.min(max, n)); }
  function vehLabelFrom(unidad, placa) {
    const u = (unidad ?? '').toString().trim();
    const p = (placa ?? '').toString().trim();
    return (u && p) ? `${u} - ${p}` : (u || p || '');
  }
  function postWithData(url, data) {
    const form = document.createElement('form');
    form.method = 'POST'; form.action = url;
    const inpCsrf = document.createElement('input');
    inpCsrf.type = 'hidden'; inpCsrf.name = '_token'; inpCsrf.value = CSRF; form.appendChild(inpCsrf);
    Object.entries(data || {}).forEach(([k,v])=>{
      if (Array.isArray(v)) v.forEach(val=>{ const i=document.createElement('input'); i.type='hidden'; i.name=k+'[]'; i.value=val; form.appendChild(i); });
      else if (v !== undefined && v !== null) { const i=document.createElement('input'); i.type='hidden'; i.name=k; i.value=v; form.appendChild(i); }
    });
    document.body.appendChild(form); form.submit();
  }

  // === referencias del DOM ===
  const tabs   = document.querySelectorAll('#reportTabs a.nav-link');
  const panels = document.querySelectorAll('.report-panel');

  // paginación
  const PER_PAGE = 25;
  const pageState = {
    rendimiento:  { page: 1, last: 1, total: 0, per_page: PER_PAGE },
    costokm:      { page: 1, last: 1, total: 0, per_page: PER_PAGE },
    auditoria:    { page: 1, last: 1, total: 0, per_page: PER_PAGE },
    verificacion: { page: 1, last: 1, total: 0, per_page: PER_PAGE },
  };

  function showPanel(key) { panels.forEach(p => p.classList.toggle('d-none', p.dataset.panel !== key)); }
  function vehLabel(row) {
    if (row && (row.unidad || row.placa)) {
      const lbl = vehLabelFrom(row.unidad, row.placa);
      if (lbl) return lbl;
    }
    const vid = row?.vehiculo_id;
    if (vid && VEH_BY_ID[vid]) return vehLabelFrom(VEH_BY_ID[vid].unidad, VEH_BY_ID[vid].placa);
    const p = row?.placa;
    if (p && VEH_BY_PLACA[p]) return vehLabelFrom(VEH_BY_PLACA[p].unidad, VEH_BY_PLACA[p].placa);
    return row?.placa ?? '';
  }

  function renderPager(key, meta) {
    const host = document.getElementById(`pager-${key}`);
    if (!host) return;

    if (!meta || !meta.total || meta.last_page <= 1) {
      host.innerHTML = '';
      return;
    }

    pageState[key].page     = meta.current_page || 1;
    pageState[key].last     = meta.last_page || 1;
    pageState[key].total    = meta.total || 0;
    pageState[key].per_page = meta.per_page || PER_PAGE;

    const cur = pageState[key].page;
    const last = pageState[key].last;
    const windowSize = 5;
    const start = Math.max(1, cur - Math.floor(windowSize/2));
    const end   = Math.min(last, start + windowSize - 1);
    const realStart = Math.max(1, end - windowSize + 1);

    const pageLink = (p, label = null, disabled = false, active = false) => {
      const cls = ['page-item'];
      if (disabled) cls.push('disabled');
      if (active)   cls.push('active');
      const lab = label ?? p;
      return `
        <li class="${cls.join(' ')}">
          <a class="page-link" href="#" data-page="${p}">${lab}</a>
        </li>`;
    };

    let nums = '';
    if (realStart > 1) {
      nums += pageLink(1, '1');
      if (realStart > 2) nums += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
    }
    for (let p = realStart; p <= end; p++) {
      nums += pageLink(p, String(p), false, p === cur);
    }
    if (end < last) {
      if (end < last - 1) nums += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
      nums += pageLink(last, String(last));
    }

    host.innerHTML = `
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <nav>
          <ul class="pagination mb-0">
            ${pageLink(1, '&laquo;', cur === 1)}
            ${pageLink(cur - 1, '&lsaquo;', cur === 1)}
            ${nums}
            ${pageLink(cur + 1, '&rsaquo;', cur === last)}
            ${pageLink(last, '&raquo;', cur === last)}
          </ul>
        </nav>
        <div class="text-secondary small">
          Página <strong>${cur}</strong> de <strong>${last}</strong> — ${meta.from}-${meta.to} de ${meta.total} registros
        </div>
      </div>
    `;

    host.querySelectorAll('a.page-link[data-page]').forEach(a=>{
      a.addEventListener('click', (e)=>{
        e.preventDefault();
        const p = parseInt(a.dataset.page, 10);
        if (isNaN(p)) return;
        gotoPage(key, clamp(p, 1, pageState[key].last));
      });
    });
  }

  function gotoPage(key, page) {
    pageState[key].page = clamp(page, 1, pageState[key].last || 1);
    loadTab(key);
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
  function getFilterPayload() {
    const form = document.getElementById('filtrosForm');
    const payload = {};
    const fd = new FormData(form);
    ['desde','hasta','destino','tipo_comb','anio'].forEach(k=>{
      const v = fd.get(k);
      if (v !== null && v !== '') payload[k] = v;
    });
    ['vehiculos[]','operadores[]'].forEach(name=>{
      const opts = form.querySelectorAll(`select[name="${name}"] option:checked`);
      const arr = Array.from(opts).map(o=>o.value);
      const base = name.replace('[]','');
      if (arr.length) payload[base] = arr;
    });
    return payload;
  }

  // ===== Charts =====
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

  // Auditoría
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

  // Verificación
  window.chartVer = window.chartVer || null;
  function renderVerificacionChartFromPayload(payload) {
    const el = document.querySelector('#chart-verificacion'); if (!el) return;

    if (payload?.chart?.series?.length) {
      const opts = {
        chart: { type: 'pie', height: 320, toolbar: { show: false } },
        series: payload.chart.series[0].data,
        labels: payload.chart.categories,
        legend: { position: 'bottom' },
        dataLabels: { enabled: true }
      };
      if (window.chartVer) window.chartVer.destroy();
      window.chartVer = new ApexCharts(el, opts); window.chartVer.render();
      return;
    }

    // Fallback
    const rows = payload?.table || payload?.rows || [];
    const counts = { 'Verificado': 0, 'Sin verificar': 0 };
    (rows || []).forEach(r => { const k = (r.estatus === 'Verificado') ? 'Verificado' : 'Sin verificar'; counts[k]++; });
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

  // Carga por tab
  function renderKPIs(kpis){
    if (!kpis) return;
    const el = (id, val) => { const e=document.getElementById(id); if (e) e.textContent = (val ?? '—'); };
    el('kpiLitros', kpis.litros);
    el('kpiGasto', kpis.gasto);
    el('kpiKm', kpis.km);
    el('kpiCostoKm', kpis.costo_km);
  }

  async function loadTab(key) {
    showPanel(key);
    const panel = document.querySelector(`[data-panel="${key}"]`);
    panel.querySelectorAll('tbody').forEach(t => t.innerHTML = `<tr><td colspan="99" class="text-secondary">Cargando…</td></tr>`);

    const params = qsParams();
    params.set('page', pageState[key].page || 1);
    params.set('per_page', pageState[key].per_page || PER_PAGE);

    const url = endpoints[key] + '?' + params.toString();
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const payload = await res.json();

    const tbody = panel.querySelector('tbody');

    if (['rendimiento','costokm'].includes(key)) renderKPIs(payload.kpis);

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
      renderPager('rendimiento', payload.pagination);
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
      renderPager('costokm', payload.pagination);
    }
    else if (key === 'auditoria') {
      const rows = payload.table || payload.rows || [];
      renderAuditoriaChart(rows);
      renderPager('auditoria', payload.pagination);
    }
    else if (key === 'verificacion') {
      const rows = payload.table || payload.rows || [];
      if (!rows.length) tbody.innerHTML = `<tr><td colspan="5" class="text-secondary">Sin datos.</td></tr>`;
      else tbody.innerHTML = rows.map(r => `
        <tr>
          <td>${vehLabel(r)}</td>
          <td>${r.anio ?? ''}</td>
          <td>${r.estado ?? ''}</td>
          <td>${r.estatus ?? ''}</td>
          <td>${r.fecha_verificacion ?? ''}</td>
        </tr>`).join('');
      renderVerificacionChartFromPayload(payload);
      renderPager('verificacion', payload.pagination);
    }

    // Actualiza URL
    const paramsForUrl = qsParams().toString();
    const newUrl = `${INDEX_URL}?${paramsForUrl}#${key}`;
    window.history.replaceState({}, '', newUrl);
  }

  // Exportar PDF con imagen del gráfico
  function attachExportWithChart(key, chartRefGetter) {
    const btn = document.getElementById(`exp-${key}-pdf`);
    if (!btn) return;
    btn.addEventListener('click', async (e)=>{
      e.preventDefault();
      const url = BOOT.endpoints.exportBase + '/' + (key === 'costokm' ? 'costo-km' : key) + '/export.pdf';
      let chartUri = null;
      try {
        const ref = chartRefGetter?.();
        if (ref && typeof ref.dataURI === 'function') {
          const { imgURI } = await ref.dataURI();
          chartUri = imgURI || null;
        }
      } catch(_) { /* noop */ }
      const filters = getFilterPayload();
      postWithData(url, Object.assign({}, filters, { chart_uri: chartUri }));
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
