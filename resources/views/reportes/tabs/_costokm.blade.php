{{-- resources/views/reportes/tabs/_costokm.blade.php --}}
<div data-panel="costokm" class="report-panel d-none">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h3 class="card-title mb-0">Costo por km & Gasto</h3>
    <div class="btn-group">
      <a id="exp-costokm-pdf" href="#" class="btn btn-outline-dark" aria-disabled="true">Exportar PDF</a>
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
          <th>Vehículo</th><th>Operador</th><th>Litros</th><th>Gasto $</th>
          <th>Kilómetros</th><th>$ / km</th><th>$ / L prom.</th><th># Cargas</th>
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="8" class="text-secondary">[Datos se cargan al activar la pestaña]</td></tr>
      </tbody>
    </table>
  </div>
  <div id="pager-costokm" class="mt-2"></div>
</div>
