{{-- resources/views/reportes/tabs/_rendimiento.blade.php --}}
<div data-panel="rendimiento" class="report-panel">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h3 class="card-title mb-0">Rendimiento vs Índice Estándar (km/L)</h3>
    <div class="btn-group">
      <a id="exp-rendimiento-pdf" href="#" class="btn btn-outline-dark" aria-disabled="true">Exportar PDF</a>
    </div>
  </div>

  <div class="card mb-3"><div class="card-body">
    <div id="chart-rendimiento" style="height: 320px;">
      <div class="text-secondary">[Gráfica aquí]</div>
    </div>
  </div></div>

  <div class="table-responsive">
    <table class="table table-striped" id="tbl-rendimiento">
      <thead>
        <tr>
          <th>Vehículo</th><th>Operador</th><th>Kilómetros</th><th>Litros</th>
          <th>Rend. real</th><th>Índice</th><th>Desviación %</th><th># Cargas</th>
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="8" class="text-secondary">[Datos se cargan al activar la pestaña]</td></tr>
      </tbody>
    </table>
  </div>
  <div id="pager-rendimiento" class="mt-2"></div>
</div>
