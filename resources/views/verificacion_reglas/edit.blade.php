{{-- resources/views/verificacion_reglas/edit.blade.php --}}
<x-app-layout>
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

    /* Dual list */
    .dual-list { display: grid; grid-template-columns: 1fr auto 1fr; gap: .75rem; align-items: center; }
    .dual-list select { height: 260px; }
    .dual-buttons { display: grid; gap: .5rem; }
    .dual-badge { font-size: .75rem; }
  </style>

  <div class="container-xl">
    {{-- Header --}}
    <div class="page-header d-print-none mb-3">
      <div class="row align-items-center g-2">
        <div class="col">
          <br>
          <h2 class="page-title">Editar regla</h2>
          <div class="page-subtitle text-secondary">
            {{ $regla->nombre }} · Al guardar, se sincronizarán automáticamente los calendarios de todos los años asignados a esta regla.
          </div>
        </div>
        <div class="col-auto ms-auto">
          <a href="{{ route('verificacion-reglas.index') }}" class="btn btn-outline-dark">
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

    @php
      $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

      $byTerm = [];
      foreach ($regla->detalles as $d) {
        $byTerm[$d->terminacion][$d->semestre] = ['mes_inicio'=>$d->mes_inicio,'mes_fin'=>$d->mes_fin];
      }

      // Año fijo de la regla (no seleccionable). Usamos FQCN para evitar "use" dentro de @php.
      $anioRegla = old('anio')
        ?: (\Illuminate\Support\Carbon::parse($regla->vigencia_inicio)->year ?? now()->year);
    @endphp

    <form method="post"
          action="{{ route('verificacion-reglas.update',$regla) }}"
          class="card"
          id="form-edit-regla"
          data-estados-url="{{ route('verificacion-reglas.estados-disponibles') }}"
          data-regla-id="{{ $regla->id }}"
          data-anio-regla="{{ $anioRegla }}">
      @csrf @method('PUT')

      {{-- ======= Detalles generales ======= --}}
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

      {{-- ======= Edición de ESTADOS de la regla (año fijo) ======= --}}
      <div class="card-header">
        <h3 class="card-title">Estados de la regla</h3>
        <div class="card-subtitle">
          <strong>Año: {{ $anioRegla }}</strong> (definido por la regla). Activa para <strong>agregar/quitar estados</strong>.
          Al guardar, el calendario de ese año se reconciliará automáticamente.
        </div>
      </div>
      <div class="card-body py-3">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-check">
              <input class="form-check-input" type="checkbox" id="toggle-editar-estados">
              <span class="form-check-label">Editar estados de la regla ({{ $anioRegla }})</span>
            </label>
            {{-- Enviamos el año de la regla SOLO si la edición está activa --}}
            <input type="hidden" name="anio" id="anio-hidden" value="{{ $anioRegla }}" disabled>
          </div>

          <div id="edicion-estados" class="col-12" style="display:none;">
            <div class="mt-1 dual-list">
              <div>
                <label class="form-label d-flex align-items-center justify-content-between">
                  <span>Disponibles</span>
                  <span class="text-secondary dual-badge" id="badge-disponibles">0</span>
                </label>
                <select id="lista-disponibles" class="form-select" multiple disabled></select>
                <small class="form-hint">Selecciona uno o varios y usa los botones → / ←</small>
              </div>

              <div class="dual-buttons">
                <button type="button" class="btn btn-outline-secondary" id="btn-agregar" disabled>Agregar →</button>
                <button type="button" class="btn btn-outline-secondary" id="btn-quitar" disabled>← Quitar</button>
                <button type="button" class="btn btn-outline-secondary" id="btn-agregar-todo" disabled>Agregar todo »</button>
                <button type="button" class="btn btn-outline-secondary" id="btn-quitar-todo" disabled>« Quitar todo</button>
              </div>

              <div>
                <label class="form-label d-flex align-items-center justify-content-between">
                  <span>Seleccionados (se guardarán)</span>
                  <span class="text-secondary dual-badge" id="badge-seleccionados">0</span>
                </label>
                {{-- Este select SÍ tiene name para enviar al servidor --}}
                <select id="lista-seleccionados" name="estados[]" class="form-select" multiple disabled></select>
                <small class="form-hint">Estos serán los estados vigentes para {{ $anioRegla }}.</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr class="m-0">

      {{-- ======= EDICIÓN DEL CALENDARIO POR TERMINACIÓN ======= --}}
      <div class="card-header">
        <h3 class="card-title">Calendario por terminación</h3>
        <div class="card-subtitle">
          Ajusta los meses; al guardar se sincronizarán automáticamente para todos los años que tenga asignados esta regla.
        </div>
      </div>
      <div class="card-body py-3">
        @php /* $byTerm y $meses ya construidos arriba */ @endphp

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
            <small class="form-hint">Valida que el par inicio/fin esté dentro de 1–12. Se sincroniza automáticamente al guardar.</small>
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
            <small class="form-hint">“Anual” usa solo el semestre 0 (una ventana por terminación). Se sincroniza automáticamente al guardar.</small>
          </div>
        </div>
      </div>

      <div class="card-footer d-flex justify-content-end flex-wrap">
        <div>
          <button class="btn btn-danger">
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
    /* ===== Helpers generales ===== */
    function setDisabled(container, disabled) {
      if (!container) return;
      container.querySelectorAll('select, input, textarea, button').forEach(el => el.disabled = disabled);
    }

    function toggleFrecuencia() {
      const f = document.getElementById('frecuencia').value;
      const semDiv = document.getElementById('tabla-semestral');
      const anuDiv = document.getElementById('tabla-anual');
      const semActive = (f === 'Semestral');

      semDiv.style.display = semActive ? '' : 'none';
      anuDiv.style.display = semActive ? 'none' : '';

      // Habilitar lo visible, deshabilitar lo oculto para no postear basura
      setDisabled(semDiv, !semActive);
      setDisabled(anuDiv, semActive);
    }

    // Inicial calendario
    toggleFrecuencia();
    document.getElementById('frecuencia').addEventListener('change', toggleFrecuencia);

    /* ===== Edición de ESTADOS (año fijo de la regla) ===== */
    (function estadosReglaAnioFijo() {
      const form = document.getElementById('form-edit-regla');
      const url  = form.dataset.estadosUrl;
      const reglaId = form.dataset.reglaId;

      const toggle = document.getElementById('toggle-editar-estados');
      const cont   = document.getElementById('edicion-estados');
      const anioHidden = document.getElementById('anio-hidden');

      const selDisp   = document.getElementById('lista-disponibles');
      const selSel    = document.getElementById('lista-seleccionados');

      const btnAdd = document.getElementById('btn-agregar');
      const btnRem = document.getElementById('btn-quitar');
      const btnAddAll = document.getElementById('btn-agregar-todo');
      const btnRemAll = document.getElementById('btn-quitar-todo');

      const badgeDisp = document.getElementById('badge-disponibles');
      const badgeSel  = document.getElementById('badge-seleccionados');

      function updateBadges() {
        badgeDisp.textContent = selDisp.options.length;
        badgeSel.textContent  = selSel.options.length;
      }

      function enableEstadosUI(enabled) {
        cont.style.display = enabled ? '' : 'none';
        [selDisp, selSel, btnAdd, btnRem, btnAddAll, btnRemAll].forEach(el => el.disabled = !enabled);
        anioHidden.disabled = !enabled;
        if (!enabled) {
          selDisp.innerHTML = '';
          selSel.innerHTML  = '';
          updateBadges();
        }
      }

      function option(label) {
        const o = document.createElement('option');
        o.value = label;
        o.text  = label;
        return o;
      }

      function moveSelected(from, to) {
        const moves = Array.from(from.selectedOptions);
        moves.forEach(opt => {
          to.appendChild(option(opt.value));
          opt.remove();
        });
        updateBadges();
      }

      function moveAll(from, to) {
        const all = Array.from(from.options);
        all.forEach(opt => {
          to.appendChild(option(opt.value));
          opt.remove();
        });
        updateBadges();
      }

      async function cargarEstados() {
        selDisp.innerHTML = '';
        selSel.innerHTML  = '';
        updateBadges();

        try {
          const q = new URLSearchParams({ anio: String(form.dataset.anioRegla), regla_id: String(reglaId) });
          const res = await fetch(`${url}?${q.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
          if (!res.ok) {
            const msg = await res.text();
            throw new Error(msg || 'No se pudo cargar la lista de estados.');
          }
          const data = await res.json();

          // Disponibles
          (data.disponibles || []).forEach(it => selDisp.appendChild(option(it.label)));
          // Seleccionados (tal cual estén en BD)
          (data.seleccionados || []).forEach(lbl => selSel.appendChild(option(lbl)));

          // Si hubo validación previa, respeta old('estados[]')
          const estadosOld = @json(old('estados', []));
          if (Array.isArray(estadosOld) && estadosOld.length > 0) {
            selSel.innerHTML = '';
            estadosOld.forEach(lbl => selSel.appendChild(option(lbl)));

            const setSel = new Set(estadosOld);
            const catalogo = (data.disponibles || []).map(it => it.label)
              .concat((data.seleccionados || []));
            const unicos = Array.from(new Set(catalogo));
            selDisp.innerHTML = '';
            unicos.filter(lbl => !setSel.has(lbl)).forEach(lbl => selDisp.appendChild(option(lbl)));
          }

          updateBadges();
        } catch (e) {
          alert('Error: ' + (e && e.message ? e.message : e));
        }
      }

      // Eventos dual list
      btnAdd.addEventListener('click', () => moveSelected(selDisp, selSel));
      btnRem.addEventListener('click', () => moveSelected(selSel, selDisp));
      btnAddAll.addEventListener('click', () => moveAll(selDisp, selSel));
      btnRemAll.addEventListener('click', () => moveAll(selSel, selDisp));

      // Toggle maestro
      toggle.addEventListener('change', () => {
        enableEstadosUI(toggle.checked);
        if (toggle.checked) {
          cargarEstados(); // carga automáticamente usando el año fijo
        }
      });

      // Estado inicial (si el usuario venía de un error con old('estados'))
      const hadOldEstados = Array.isArray(@json(old('estados', []))) && @json(old('estados', []))?.length > 0;
      if (hadOldEstados) {
        toggle.checked = true;
        enableEstadosUI(true);
        cargarEstados();
      } else {
        enableEstadosUI(false);
      }

      // Antes de enviar el form:
      form.addEventListener('submit', () => {
        if (!toggle.checked) {
          // Si no está activa la edición de estados, no envíes los selects ni el año
          selSel.disabled = true;
          anioHidden.disabled = true;
        } else {
          // Marca todo como seleccionado para que se envíe
          Array.from(selSel.options).forEach(o => o.selected = true);
          selSel.disabled = false;
          anioHidden.disabled = false;
        }
      });
    })();
  </script>
</x-app-layout>
