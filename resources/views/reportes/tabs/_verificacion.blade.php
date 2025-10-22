{{-- resources/views/reportes/tabs/_verificacion.blade.php --}}
<div data-panel="verificacion" class="report-panel d-none">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h3 class="card-title mb-0">Verificación anual: Verificados vs Sin verificar</h3>
    <div class="btn-group">
      <a id="exp-verificacion-pdf" href="#" class="btn btn-outline-dark" aria-disabled="true">Exportar PDF</a>
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
          <th>Vehículo</th><th>Año</th><th>Estado</th><th>Estatus</th><th>Fecha verif.</th>
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="5" class="text-secondary">[Datos se cargan al activar la pestaña]</td></tr>
      </tbody>
    </table>
  </div>
  <div id="pager-verificacion" class="mt-2"></div>
</div>
