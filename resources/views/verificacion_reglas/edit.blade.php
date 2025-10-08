{{-- resources/views/verificacion_reglas/edit.blade.php --}}
<x-app-layout>
  @vite(['resources/js/app.js'])

  <style>
    /* Pulido sutil y distribución */
    .page-header .page-title { margin-bottom: .25rem; }
    .card { border: 0; box-shadow: var(--tblr-shadow, 0 1px 2px rgba(0,0,0,.06)); }

    .table-sticky thead th {
      position: sticky; top: 0; z-index: 2;
      background: var(--tblr-bg-surface, #fff);
    }
    .table thead th { font-weight: 600; }
    .table-nowrap { white-space: nowrap; }
    .table-sm > :not(caption) > * > * { padding-top:.5rem; padding-bottom:.5rem; }
    .form-hint { margin-top: .25rem; display: inline-block; }

    .card-header { gap: .25rem; }
    .card-footer { gap: .5rem; }

    /* Alinear inputs y labels compactos */
    .form-label { margin-bottom: .25rem; }
  </style>

  <div class="container-xl">
    {{-- Header --}}
    <div class="page-header d-print-none mb-3">
      <div class="row align-items-center g-2">
        <div class="col">
          <br>
          <h2 class="page-title">Editar regla</h2>
          <div class="page-subtitle text-secondary">{{ $regla->nombre }}</div>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ route('verificacion-reglas.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left"></i> Volver
          </a>
        </div>
      </div>
    </div>

    {{-- Alertas --}}
    @if ($errors->any())
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif
    @if (session('success'))
      <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <form method="post" action="{{ route('verificacion-reglas.update',$regla) }}" class="card">
      @csrf @method('PUT')

      <div class="card-header">
        <h3 class="card-title">Detalles generales</h3>
      </div>
      <div class="card-body py-3">
        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required
                   value="{{ old('nombre',$regla->nombre) }}">
          </div>
          <div class="col-6 col-lg-3">
            <label class="form-label">Versión</label>
            <input type="text" name="version" class="form-control"
                   value="{{ old('version',$regla->version) }}">
          </div>
          <div class="col-6 col-lg-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              @foreach (['published','draft','archived'] as $opt)
                <option value="{{ $opt }}" @selected(old('status',$regla->status)===$opt)>{{ $opt }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-6 col-lg-3">
            <label class="form-label">Frecuencia</label>
            <select name="frecuencia" id="frecuencia" class="form-select" required>
              @foreach (['Semestral','Anual'] as $opt)
                <option value="{{ $opt }}" @selected(old('frecuencia',$regla->frecuencia)===$opt)>{{ $opt }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Notas</label>
            <textarea name="notas" class="form-control" rows="2">{{ old('notas',$regla->notas) }}</textarea>
          </div>
        </div>
      </div>

      <hr class="m-0">

      {{-- ======= EDICIÓN DEL CALENDARIO POR TERMINACIÓN ======= --}}
      <div class="card-header">
        <h3 class="card-title">Calendario por terminación</h3>
        <div class="card-subtitle">Ajusta los meses; aplica con “Regenerar” para el año elegido.</div>
      </div>
      <div class="card-body py-3">
        @php
          $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
          $det = $regla->detalles()->get()->groupBy('semestre'); // 0,1,2
          $byTerm = [];
          foreach ($regla->detalles as $d) {
            $byTerm[$d->terminacion][$d->semestre] = ['mes_inicio'=>$d->mes_inicio,'mes_fin'=>$d->mes_fin];
          }
        @endphp

        {{-- SEMESTRAL --}}
        <div id="tabla-semestral" style="{{ old('frecuencia',$regla->frecuencia)==='Semestral' ? '' : 'display:none' }}">
          <div class="table-responsive">
            <table class="table table-vcenter table-sm table-sticky table-nowrap align-middle mb-0">
              <thead>
                <tr>
                  <th>Terminación</th>
                  <th>Semestre 1 — Mes inicio</th>
                  <th>Semestre 1 — Mes fin</th>
                  <th>Semestre 2 — Mes inicio</th>
                  <th>Semestre 2 — Mes fin</th>
                </tr>
              </thead>
              <tbody>
                @foreach (range(0,9) as $d)
                  @php
                    $s1 = $byTerm[$d][1] ?? ['mes_inicio'=>1,'mes_fin'=>2];
                    $s2 = $byTerm[$d][2] ?? ['mes_inicio'=>7,'mes_fin'=>8];
                    $s1i = (int)old("detalles.$d.1.mes_inicio", $s1['mes_inicio']);
                    $s1f = (int)old("detalles.$d.1.mes_fin",    $s1['mes_fin']);
                    $s2i = (int)old("detalles.$d.2.mes_inicio", $s2['mes_inicio']);
                    $s2f = (int)old("detalles.$d.2.mes_fin",    $s2['mes_fin']);
                  @endphp
                  <tr>
                    <td class="fw-bold">{{ $d }}</td>
                    <td>
                      <select name="detalles[{{ $d }}][1][mes_inicio]" class="form-select form-select-sm">
                        @foreach ($meses as $k=>$m) <option value="{{ $k }}" @selected((int)$k===$s1i)>{{ $m }}</option> @endforeach
                      </select>
                    </td>
                    <td>
                      <select name="detalles[{{ $d }}][1][mes_fin]" class="form-select form-select-sm">
                        @foreach ($meses as $k=>$m) <option value="{{ $k }}" @selected((int)$k===$s1f)>{{ $m }}</option> @endforeach
                      </select>
                    </td>
                    <td>
                      <select name="detalles[{{ $d }}][2][mes_inicio]" class="form-select form-select-sm">
                        @foreach ($meses as $k=>$m) <option value="{{ $k }}" @selected((int)$k===$s2i)>{{ $m }}</option> @endforeach
                      </select>
                    </td>
                    <td>
                      <select name="detalles[{{ $d }}][2][mes_fin]" class="form-select form-select-sm">
                        @foreach ($meses as $k=>$m) <option value="{{ $k }}" @selected((int)$k===$s2f)>{{ $m }}</option> @endforeach
                      </select>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <small class="form-hint">Valida que el par inicio/fin esté dentro de 1–12.</small>
          </div>
        </div>

        {{-- ANUAL --}}
        <div id="tabla-anual" style="{{ old('frecuencia',$regla->frecuencia)==='Anual' ? '' : 'display:none' }}">
          <div class="table-responsive">
            <table class="table table-vcenter table-sm table-sticky table-nowrap align-middle mb-0">
              <thead>
                <tr>
                  <th>Terminación</th>
                  <th>Mes inicio</th>
                  <th>Mes fin</th>
                </tr>
              </thead>
              <tbody>
                @foreach (range(0,9) as $d)
                  @php
                    $a0 = $byTerm[$d][0] ?? ['mes_inicio'=>1,'mes_fin'=>2];
                    $a0i = (int)old("detalles.$d.0.mes_inicio", $a0['mes_inicio']);
                    $a0f = (int)old("detalles.$d.0.mes_fin",    $a0['mes_fin']);
                  @endphp
                  <tr>
                    <td class="fw-bold">{{ $d }}</td>
                    <td>
                      <select name="detalles[{{ $d }}][0][mes_inicio]" class="form-select form-select-sm">
                        @foreach ($meses as $k=>$m) <option value="{{ $k }}" @selected((int)$k===$a0i)>{{ $m }}</option> @endforeach
                      </select>
                    </td>
                    <td>
                      <select name="detalles[{{ $d }}][0][mes_fin]" class="form-select form-select-sm">
                        @foreach ($meses as $k=>$m) <option value="{{ $k }}" @selected((int)$k===$a0f)>{{ $m }}</option> @endforeach
                      </select>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <small class="form-hint">“Anual” usa solo el semestre 0 (una ventana por terminación).</small>
          </div>
        </div>
      </div>

      <hr class="m-0">

      {{-- ======= REGENERAR AL GUARDAR ======= --}}
      <div class="card-header">
        <h3 class="card-title">Aplicar cambios al calendario</h3>
      </div>
      <div class="card-body py-3">
        <div class="row g-3">
          <div class="col-6 col-lg-3">
            <label class="form-label">Año a regenerar</label>
            @php
              $anioSugerido = optional($regla->vigencia_inicio)->format('Y') ?? now()->year;
            @endphp
            <input type="number" class="form-control" name="anio_regenerar" min="2000" max="2999"
                   value="{{ old('anio_regenerar', $anioSugerido) }}">
          </div>
          <div class="col-12 col-lg-9 d-flex align-items-end">
            <label class="form-check">
              <input class="form-check-input" type="checkbox" name="regenerar_al_guardar" value="1">
              <span class="form-check-label">
                Regenerar periodos de <strong>este año</strong> al guardar (sobrescribe periodos de esta regla para ese año).
              </span>
            </label>
          </div>
        </div>
        <small class="form-hint">Si no marcas esta opción, solo se guardará la regla; podrás regenerar luego desde el botón.</small>
      </div>

      <div class="card-footer d-flex justify-content-between flex-wrap">
        <a href="{{ route('verificacion-reglas.generar.form',$regla) }}" class="btn btn-outline-indigo">
          <i class="ti ti-refresh"></i> Regenerar (avanzado)
        </a>
        <div>
          <button class="btn btn-primary">
            <i class="ti ti-device-floppy"></i> Guardar cambios
          </button>
        </div>
      </div>
    </form>
    {{-- FOOTER --}}
    <br>
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
  </div>

  <script>
    function toggleFrecuencia() {
      const f = document.getElementById('frecuencia').value;
      document.getElementById('tabla-semestral').style.display = (f === 'Semestral') ? '' : 'none';
      document.getElementById('tabla-anual').style.display = (f === 'Anual') ? '' : 'none';
    }
    document.getElementById('frecuencia').addEventListener('change', toggleFrecuencia);
  </script>
</x-app-layout>
