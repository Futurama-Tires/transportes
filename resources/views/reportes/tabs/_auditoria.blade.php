{{-- resources/views/reportes/tabs/_auditoria.blade.php --}}
<div data-panel="auditoria" class="report-panel d-none">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h3 class="card-title mb-0">Auditoría de cargas y anomalías</h3>
    <div class="btn-group">
      <a id="exp-auditoria-pdf" href="#" class="btn btn-outline-dark" aria-disabled="true">Exportar PDF</a>
    </div>
  </div>

  <div class="card mb-3"><div class="card-body">
    <div id="chart-auditoria" style="height: 320px;">
      <div class="text-secondary">[Gráfica aquí]</div>
    </div>
  </div></div>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-secondary small">
      Mostrando: <span id="auditoria-filter-badge" class="badge bg-secondary">Todas</span>
    </div>
    <div class="btn-group">
      <button id="auditoria-clear-filter" class="btn btn-sm btn-outline-dark" disabled>Quitar filtro</button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-striped" id="tbl-auditoria">
      <thead>
        <tr>
          <th>Fecha</th><th>Vehículo</th><th>Operador</th><th>Litros</th>
          <th>$ / L</th><th>Total $</th><th>Capacidad</th><th>Flags</th>
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="8" class="text-secondary">[Datos se cargan al activar la pestaña]</td></tr>
      </tbody>
    </table>
  </div>
  <div id="pager-auditoria" class="mt-2"></div>
</div>
