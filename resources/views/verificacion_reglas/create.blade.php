{{-- resources/views/verificacion_reglas/create.blade.php --}}
<x-app-layout>
    {{-- ================= HEADER (estilo cargas) ================= --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a>Inicio</a></li>
                        <li class="breadcrumb-item"><a>Panel</a></li>
                        <li class="breadcrumb-item"><a>Verificación</a></li>
                        <li class="breadcrumb-item"><a>Reglas</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Nueva</li>
                    </ol>
                    <div class="col">
                        <h2 class="page-title mb-0">Nueva regla de verificación</h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="d-flex gap-2">
                            <a href="{{ route('verificacion-reglas.index') }}" class="btn btn-outline-dark">
                                <i class="ti ti-arrow-left me-1" aria-hidden="true"></i><span>Volver</span>
                            </a>
                            {{-- El botón de header dispara el botón del form (respeta el disabled) --}}
                            <button type="button" id="btn-guardar-header" class="btn btn-danger" onclick="document.getElementById('btn-guardar')?.click();" disabled>
                                <i class="ti ti-device-floppy me-1" aria-hidden="true"></i><span>Guardar regla</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <style>
        /* Ajustes sutiles de layout y tabla */
        .page-header .page-title { margin-bottom: .25rem; }
        .card { border: 0; box-shadow: var(--tblr-shadow, 0 1px 2px rgba(0,0,0,.06)); }

        .table-sticky thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: var(--tblr-bg-surface, #fff);
        }
        .table thead th { font-weight: 600; }
        .table-nowrap { white-space: nowrap; }
        .table-sm > :not(caption) > * > * { padding-top:.5rem; padding-bottom:.5rem; }

        .col-nombre { max-width: 560px; }
        @media (max-width: 992px) { .col-nombre { max-width: 420px; } }
        @media (max-width: 576px) { .col-nombre { max-width: 100%; } }

        .form-hint { margin-top: .25rem; display: inline-block; }

        /* Grid de estados compacto y parejo */
        #estados-wrap .row { --tblr-gutter-x: .75rem; --tblr-gutter-y: .5rem; }

        /* Footer del form siempre bien alineado */
        .card-footer { gap: .5rem; }

        /* ===== CONTRASTE: “Ya ocupados este año” =====
           Forzamos color de texto y chips para que se lean bien en claro/oscuro */
        #estados-ocupados .text-line { color: var(--tblr-body-color) !important; }
        .badge.chip-ocupado{
            background-color: transparent;
            color: var(--tblr-body-color);
            border: 1px solid var(--tblr-border-color);
            font-weight: 500;
        }
    </style>

    <div class="page-body">
        <div class="container-xl">

            {{-- Errores --}}
            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <form id="form-create-regla" method="post" action="{{ route('verificacion-reglas.store') }}" class="card">
                @csrf

                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-12 col-lg-5">
                            <label class="form-label mb-1">Nombre de la regla</label>
                            <input type="text" name="nombre" class="form-control col-nombre" required
                                   placeholder="Ej. Megalópolis {{ date('Y') }}" value="{{ old('nombre') }}">
                        </div>
                        <div class="col-6 col-lg-2">
                            <label class="form-label mb-1">Versión (opcional)</label>
                            <input type="text" name="version" class="form-control" placeholder="v1.0" value="{{ old('version') }}">
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label mb-1">Frecuencia</label>
                            <select name="frecuencia" id="frecuencia" class="form-select" required>
                                <option value="Semestral" {{ old('frecuencia','Semestral')==='Semestral' ? 'selected' : '' }}>Semestral</option>
                                <option value="Anual" {{ old('frecuencia')==='Anual' ? 'selected' : '' }}>Anual</option>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label class="form-label mb-1">Año</label>
                            <input type="number" class="form-control" name="anio" id="anio"
                                   min="2000" max="2999" value="{{ old('anio', $anioDefault ?? now()->year) }}" required>
                        </div>

                        {{-- ===== Estados con checkboxes dinámicos ===== --}}
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-label mb-0">Estados (disponibles para el año)</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-select-all">
                                        <i class="ti ti-checkbox"></i> Seleccionar todos
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-dark" id="btn-clear-all">
                                        <i class="ti ti-square"></i> Limpiar
                                    </button>
                                </div>
                            </div>

                            <div id="estados-wrap" class="mt-2">
                                <div class="text-secondary">Cargando estados…</div>
                            </div>
                            <small class="form-hint">Solo aparecen seleccionables los estados que no están asignados en otra regla para este año.</small>

                            <div id="estados-ocupados" class="mt-2" style="display:none;">
                                <div class="small text-line">
                                    <i class="ti ti-info-circle"></i> Ya ocupados este año:
                                    <span id="chips-ocupados"></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label mb-1">Notas (opcional)</label>
                            <textarea class="form-control" name="notas" rows="2" placeholder="Observaciones...">{{ old('notas') }}</textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- =================== SEMESTRAL =================== --}}
                    <div id="tabla-semestral" style="{{ old('frecuencia','Semestral')==='Semestral' ? '' : 'display:none' }}">
                        <h3 class="h4 text-dark mb-2">Calendario por terminación — Semestral</h3>
                        <div class="table-responsive">
                            <table class="table table-vcenter table-sm table-sticky table-nowrap align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-dark">Terminación</th>
                                        <th class="text-dark">Semestre 1 — Mes inicio</th>
                                        <th class="text-dark">Semestre 1 — Mes fin</th>
                                        <th class="text-dark">Semestre 2 — Mes inicio</th>
                                        <th class="text-dark">Semestre 2 — Mes fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $mapS1 = [
                                            5 => [1,2], 6 => [1,2],
                                            7 => [2,3], 8 => [2,3],
                                            3 => [3,4], 4 => [3,4],
                                            1 => [4,5], 2 => [4,5],
                                            9 => [5,6], 0 => [5,6],
                                        ];
                                        $mapS2 = [];
                                        foreach ($mapS1 as $k => [$mi,$mf]) {
                                            $mi2 = $mi + 6; $mf2 = $mf + 6;
                                            if ($mi2 > 12) $mi2 -= 12;
                                            if ($mf2 > 12) $mf2 -= 12;
                                            $mapS2[$k] = [$mi2,$mf2];
                                        }
                                    @endphp

                                    @foreach (range(0,9) as $d)
                                        @php
                                            [$s1iDef, $s1fDef] = $mapS1[$d] ?? [1,2];
                                            [$s2iDef, $s2fDef] = $mapS2[$d] ?? [7,8];

                                            $s1i = (int)old("detalles.$d.1.mes_inicio", $s1iDef);
                                            $s1f = (int)old("detalles.$d.1.mes_fin",    $s1fDef);
                                            $s2i = (int)old("detalles.$d.2.mes_inicio", $s2iDef);
                                            $s2f = (int)old("detalles.$d.2.mes_fin",    $s2fDef);
                                        @endphp
                                        <tr>
                                            <td class="text-dark fw-bold">{{ $d }}</td>
                                            <td>
                                                <select name="detalles[{{ $d }}][1][mes_inicio]" class="form-select form-select-sm">
                                                    @foreach ($meses as $k=>$m)
                                                        <option value="{{ $k }}" {{ (int)$k===$s1i ? 'selected' : '' }}>{{ $m }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="detalles[{{ $d }}][1][mes_fin]" class="form-select form-select-sm">
                                                    @foreach ($meses as $k=>$m)
                                                        <option value="{{ $k }}" {{ (int)$k===$s1f ? 'selected' : '' }}>{{ $m }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="detalles[{{ $d }}][2][mes_inicio]" class="form-select form-select-sm">
                                                    @foreach ($meses as $k=>$m)
                                                        <option value="{{ $k }}" {{ (int)$k===$s2i ? 'selected' : '' }}>{{ $m }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="detalles[{{ $d }}][2][mes_fin]" class="form-select form-select-sm">
                                                    @foreach ($meses as $k=>$m)
                                                        <option value="{{ $k }}" {{ (int)$k===$s2f ? 'selected' : '' }}>{{ $m }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- =================== ANUAL =================== --}}
                    <div id="tabla-anual" style="{{ old('frecuencia','Semestral')==='Anual' ? '' : 'display:none' }}">
                        <h3 class="h4 text-dark mb-2">Calendario por terminación — Anual</h3>
                        <div class="table-responsive">
                            <table class="table table-vcenter table-sm table-sticky table-nowrap align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-dark">Terminación</th>
                                        <th class="text-dark">Mes inicio</th>
                                        <th class="text-dark">Mes fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (range(0,9) as $d)
                                        @php
                                            $def0 = $defaultsAnual[$d][0] ?? ['mes_inicio'=>1,'mes_fin'=>2];
                                            $a0i = (int)old("detalles.$d.0.mes_inicio", $def0['mes_inicio']);
                                            $a0f = (int)old("detalles.$d.0.mes_fin",    $def0['mes_fin']);
                                        @endphp
                                        <tr>
                                            <td class="text-dark fw-bold">{{ $d }}</td>
                                            <td>
                                                <select name="detalles[{{ $d }}][0][mes_inicio]" class="form-select form-select-sm">
                                                    @foreach ($meses as $k=>$m)
                                                        <option value="{{ $k }}" {{ (int)$k===$a0i ? 'selected' : '' }}>{{ $m }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="detalles[{{ $d }}][0][mes_fin]" class="form-select form-select-sm">
                                                    @foreach ($meses as $k=>$m)
                                                        <option value="{{ $k }}" {{ (int)$k===$a0f ? 'selected' : '' }}>{{ $m }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <small class="form-hint">“Anual” significa una sola ventana al año por terminación (como Jalisco). Se sincroniza automáticamente al guardar.</small>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    <a href="{{ route('verificacion-reglas.index') }}" class="btn btn-link">Cancelar</a>
                    <button id="btn-guardar" class="btn btn-danger" disabled>
                        <i class="ti ti-device-floppy"></i> Guardar regla
                    </button>
                </div>
            </form>

            {{-- FOOTER (estilo cargas) --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    <script>
        function setDisabled(container, disabled) {
            container.querySelectorAll('select, input, textarea, button').forEach(el => {
                if (el.id !== 'btn-guardar-header') { // el del header lo controlamos aparte
                    el.disabled = disabled;
                }
            });
        }

        function renderEstadosCheckboxes(disponibles, ocupados) {
            const wrap = document.getElementById('estados-wrap');
            wrap.innerHTML = '';

            if (!disponibles || disponibles.length === 0) {
                wrap.innerHTML = '<div class="text-secondary">No hay estados disponibles para este año.</div>';
                document.getElementById('estados-ocupados').style.display = (ocupados && ocupados.length) ? '' : 'none';
                renderOcupadosChips(ocupados);
                updateGuardarEnabled();
                return;
            }

            const row = document.createElement('div');
            row.className = 'row g-2';

            const olds = @json(old('estados', []));

            disponibles.forEach((it, idx) => {
                const col = document.createElement('div');
                col.className = 'col-12 col-sm-6 col-md-4';

                const id = 'estado_cb_' + idx;
                const checked = olds.includes(it.value) ? 'checked' : '';

                col.innerHTML = `
                    <label class="form-check w-100">
                        <input class="form-check-input estado-cb" type="checkbox" name="estados[]" value="${it.value}" id="${id}" ${checked}>
                        <span class="form-check-label">${it.label}</span>
                    </label>
                `;
                row.appendChild(col);
            });

            wrap.appendChild(row);

            document.querySelectorAll('.estado-cb').forEach(cb => cb.addEventListener('change', updateGuardarEnabled));

            document.getElementById('estados-ocupados').style.display = (ocupados && ocupados.length) ? '' : 'none';
            renderOcupadosChips(ocupados);
            updateGuardarEnabled();
        }

        function renderOcupadosChips(ocupados) {
            const cont = document.getElementById('chips-ocupados');
            if (!cont) return;
            cont.innerHTML = '';
            if (!ocupados || !ocupados.length) return;

            ocupados.forEach(o => {
                const span = document.createElement('span');
                span.className = 'badge chip-ocupado me-1 mb-1';
                span.textContent = o;
                cont.appendChild(span);
            });
        }

        function anyEstadoChecked() {
            return Array.from(document.querySelectorAll('#estados-wrap input.estado-cb')).some(cb => cb.checked);
        }

        function updateGuardarEnabled() {
            const btn = document.getElementById('btn-guardar');
            const headerBtn = document.getElementById('btn-guardar-header');
            const hasCheckbox = document.querySelector('#estados-wrap input.estado-cb') !== null;
            const enable = (hasCheckbox && anyEstadoChecked());
            btn.disabled = !enable;
            if (headerBtn) headerBtn.disabled = !enable;
        }

        async function cargarEstadosDisponibles() {
            const anio = document.getElementById('anio').value;
            const wrap = document.getElementById('estados-wrap');
            wrap.innerHTML = '<div class="text-secondary">Cargando estados…</div>';
            document.getElementById('btn-guardar').disabled = true;
            const headerBtn = document.getElementById('btn-guardar-header');
            if (headerBtn) headerBtn.disabled = true;

            try {
                const url = '{{ route('verificacion-reglas.estados-disponibles') }}' + '?anio=' + encodeURIComponent(anio);
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!res.ok) {
                    let msg = 'Error al cargar estados';
                    try {
                        const err = await res.json();
                        msg = err.message || msg;
                    } catch {
                        const txt = await res.text();
                        if (txt) msg = txt.substring(0, 200);
                    }
                    wrap.innerHTML = '<div class="text-danger">'+msg+'</div>';
                    document.getElementById('estados-ocupados').style.display = 'none';
                    updateGuardarEnabled();
                    return;
                }

                const data = await res.json();
                renderEstadosCheckboxes(data.disponibles || [], data.ocupados || []);
            } catch (e) {
                wrap.innerHTML = '<div class="text-danger">Error al cargar estados (ver consola)</div>';
                document.getElementById('estados-ocupados').style.display = 'none';
                console.error(e);
                updateGuardarEnabled();
            }
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

        function selectAllEstados() {
            document.querySelectorAll('#estados-wrap input.estado-cb').forEach(cb => cb.checked = true);
            updateGuardarEnabled();
        }
        function clearAllEstados() {
            document.querySelectorAll('#estados-wrap input.estado-cb').forEach(cb => cb.checked = false);
            updateGuardarEnabled();
        }

        document.getElementById('anio').addEventListener('change', cargarEstadosDisponibles);
        document.getElementById('frecuencia').addEventListener('change', toggleFrecuencia);
        document.getElementById('btn-select-all').addEventListener('click', selectAllEstados);
        document.getElementById('btn-clear-all').addEventListener('click', clearAllEstados);

        // Inicial
        cargarEstadosDisponibles();
        toggleFrecuencia();
        updateGuardarEnabled();
    </script>
</x-app-layout>
