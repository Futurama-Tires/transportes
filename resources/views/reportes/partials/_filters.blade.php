{{-- resources/views/reportes/partials/_filters.blade.php --}}
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
        <button type="submit" class="btn btn-danger">Aplicar filtros</button>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-dark">Limpiar</a>
      </div>
    </form>
  </div>
</div>
