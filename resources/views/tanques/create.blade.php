{{-- resources/views/vehiculos/tanques/create.blade.php — Versión Tabler ejecutiva (1 tanque por vehículo) --}}
<x-app-layout>
    {{-- Quita esta línea si tu layout ya inyecta app.js --}}
    @vite(['resources/js/app.js'])

    {{-- ===== HEADER ===== --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            <i class="ti ti-gas-station me-1"></i> Tanque
                        </div>
                        <h2 class="page-title d-flex align-items-center gap-2 mb-0">
                            <i class="ti ti-circle-plus"></i>
                            Agregar tanque — Vehículo {{ $vehiculo->unidad ?? '#'.$vehiculo->id }}
                        </h2>

                        {{-- Breadcrumbs --}}
                        <div class="mt-2">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('vehiculos.index') }}"><i class="ti ti-steering-wheel me-1"></i> Vehículos</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('vehiculos.tanques.index', $vehiculo) }}"><i class="ti ti-gas-station me-1"></i> Tanque</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Agregar</li>
                            </ol>
                        </div>
                    </div>

                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('vehiculos.tanques.index', $vehiculo) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i>
                                Volver
                            </a>
                            <a href="{{ route('vehiculos.edit', $vehiculo) }}" class="btn btn-outline-primary">
                                <i class="ti ti-car me-1"></i>
                                Editar vehículo
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Meta del vehículo --}}
                <div class="row g-3 mt-2">
                    <div class="col-auto">
                        <span class="badge bg-blue-lt">
                            <i class="ti ti-hash me-1"></i> Unidad: {{ $vehiculo->unidad ?? '—' }}
                        </span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-azure-lt">
                            <i class="ti ti-license me-1"></i> Placa: {{ $vehiculo->placa ?? '—' }}
                        </span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-teal-lt">
                            <i class="ti ti-map-pin me-1"></i> Ubicación: {{ $vehiculo->ubicacion ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- ===== PAGE BODY ===== --}}
    <div class="page-body">
        <div class="container-xl">

            {{-- Alert de validación --}}
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="d-flex">
                        <div>
                            <i class="ti ti-alert-triangle icon alert-icon"></i>
                        </div>
                        <div>
                            <h4 class="alert-title">Hay errores en el formulario</h4>
                            <div class="text-secondary">Revisa los campos marcados y vuelve a intentar.</div>
                        </div>
                    </div>
                </div>
            @endif

            <form id="tanque-form" method="POST" action="{{ route('vehiculos.tanques.store', $vehiculo) }}" novalidate>
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-lg-8">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <h3 class="card-title d-flex align-items-center gap-2 mb-0">
                                    <i class="ti ti-gauge"></i>
                                    Datos del tanque
                                </h3>
                                <span class="badge bg-green-lt">
                                    <i class="ti ti-circle-plus me-1"></i> Nuevo
                                </span>
                            </div>

                            <div class="card-body">
                                <div class="row g-3">

                                    {{-- Cantidad de tanques físicos --}}
                                    <div class="col-md-4">
                                        <label for="cantidad_tanques" class="form-label">
                                            Cantidad de tanques <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-icon">
                                            <span class="input-icon-addon"><i class="ti ti-hash"></i></span>
                                            <input id="cantidad_tanques" type="number" name="cantidad_tanques" min="1" max="255"
                                                   value="{{ old('cantidad_tanques', 1) }}"
                                                   class="form-control @error('cantidad_tanques') is-invalid @enderror"
                                                   placeholder="1" required>
                                        </div>
                                        @error('cantidad_tanques') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        <div class="form-hint">Número de tanques físicos que tiene la unidad (1 o más).</div>
                                    </div>

                                    {{-- Tipo de combustible (selectgroup) --}}
                                    <div class="col-md-8">
                                        <label class="form-label">Tipo de combustible <span class="text-danger">*</span></label>
                                        <div class="form-selectgroup form-selectgroup-boxes d-flex">
                                            @php
                                                $combActual = old('tipo_combustible');
                                                $combList = [
                                                    ['label'=>'Magna',   'icon'=>'flame'],
                                                    ['label'=>'Diesel',  'icon'=>'engine'],
                                                    ['label'=>'Premium', 'icon'=>'flame'],
                                                ];
                                            @endphp
                                            @foreach($combList as $c)
                                                <label class="form-selectgroup-item flex-fill">
                                                    <input type="radio"
                                                           name="tipo_combustible"
                                                           value="{{ $c['label'] }}"
                                                           class="form-selectgroup-input"
                                                           @checked($combActual === $c['label']) required>
                                                    <span class="form-selectgroup-label d-flex align-items-center p-3">
                                                        <span class="me-2 avatar avatar-sm">
                                                            <i class="ti ti-{{ $c['icon'] }}"></i>
                                                        </span>
                                                        <span>{{ $c['label'] }}</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('tipo_combustible') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        <div class="form-hint">Selecciona el combustible que carga este vehículo.</div>
                                    </div>

                                    {{-- Capacidad total (L) --}}
                                    <div class="col-md-6">
                                        <label for="capacidad_litros" class="form-label">Capacidad total (L) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-droplet"></i></span>
                                            <input id="capacidad_litros" type="number" step="0.01" min="0"
                                                   name="capacidad_litros" required
                                                   value="{{ old('capacidad_litros') }}"
                                                   class="form-control @error('capacidad_litros') is-invalid @enderror"
                                                   placeholder="Ej. 80.00">
                                            <span class="input-group-text">L</span>
                                        </div>
                                        @error('capacidad_litros') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        <div class="form-hint">Es la suma manual de los N tanques del vehículo.</div>
                                    </div>

                                    {{-- Rendimiento (km/L) --}}
                                    <div class="col-md-6">
                                        <label for="rendimiento_estimado" class="form-label">
                                            Rendimiento estimado (km/L)
                                            <i class="ti ti-info-circle text-secondary ms-1" data-bs-toggle="tooltip" title="Promedio histórico o estimado de la unidad."></i>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-road"></i></span>
                                            <input id="rendimiento_estimado" type="number" step="0.01" min="0"
                                                   name="rendimiento_estimado"
                                                   value="{{ old('rendimiento_estimado') }}"
                                                   class="form-control @error('rendimiento_estimado') is-invalid @enderror"
                                                   placeholder="Ej. 7.50">
                                            <span class="input-group-text">km/L</span>
                                        </div>
                                        @error('rendimiento_estimado') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Costo tanque lleno (auto) --}}
                                    <div class="col-md-6">
                                        <label for="costo_tanque_lleno" class="form-label">Costo tanque lleno</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-cash"></i></span>
                                            <input id="costo_tanque_lleno" type="number" step="0.01" min="0"
                                                   name="costo_tanque_lleno"
                                                   value="{{ old('costo_tanque_lleno') }}"
                                                   class="form-control @error('costo_tanque_lleno') is-invalid @enderror"
                                                   placeholder="Ej. 1800.00">
                                            <span class="input-group-text">MXN</span>
                                        </div>
                                        @error('costo_tanque_lleno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        <div class="form-hint">
                                            Se calcula automático con el <strong>precio por litro</strong> vigente.
                                            <span id="precio-litro-hint" class="text-teal ms-1"></span>
                                        </div>
                                    </div>

                                    {{-- Km que recorre (capacidad × rendimiento) --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Km que recorre (capacidad × rendimiento)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-ruler-measure"></i></span>
                                            <input id="km_calculados" type="text" class="form-control"
                                                   value="{{ number_format((float) (old('capacidad_litros',0) * old('rendimiento_estimado',0)), 2) }}" readonly>
                                            <span class="input-group-text">km</span>
                                        </div>
                                        <div class="form-hint">Se recalcula automáticamente al editar capacidad o rendimiento.</div>
                                    </div>

                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-end gap-2">
                                <a href="{{ route('vehiculos.tanques.index', $vehiculo) }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i> Guardar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Panel lateral con tips --}}
                    <div class="col-12 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title d-flex align-items-center gap-2 mb-0">
                                    <i class="ti ti-bulb"></i> Consejos
                                </h3>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-3 d-flex">
                                        <i class="ti ti-check me-2 text-teal"></i>
                                        Registra la <strong>cantidad de tanques físicos</strong> aunque sea 1.
                                    </li>
                                    <li class="mb-3 d-flex">
                                        <i class="ti ti-check me-2 text-teal"></i>
                                        La <strong>capacidad total</strong> es la suma manual de todos los tanques.
                                    </li>
                                    <li class="mb-0 d-flex">
                                        <i class="ti ti-check me-2 text-teal"></i>
                                        El campo “Km que recorre” es informativo (se guarda el valor calculado).
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Estado rápido del vehículo --}}
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <span class="avatar me-3">
                                        <i class="ti ti-truck"></i>
                                    </span>
                                    <div>
                                        <div class="strong">Vehículo</div>
                                        <div class="text-secondary">
                                            {{ $vehiculo->marca ?? '—' }} {{ $vehiculo->anio ?? '' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    <span class="badge bg-gray-lt"><i class="ti ti-id me-1"></i> ID: {{ $vehiculo->id }}</span>
                                    @if(!empty($vehiculo->placa))
                                        <span class="badge bg-azure-lt"><i class="ti ti-license me-1"></i> {{ $vehiculo->placa }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

            <div class="text-secondary small mt-3">
                <i class="ti ti-info-circle me-1"></i>
                Los valores se guardarán tal como se muestran. El campo “Km que recorre” es informativo.
            </div>

            {{-- Footer --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    {{-- ===== SCRIPTS ===== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new window.bootstrap.Tooltip(el);
            });

            const ROUTE_CURRENT = "{{ route('precios-combustible.current') }}";

            // Inputs
            const radios = [...document.querySelectorAll('input[name="tipo_combustible"]')];
            const cap    = document.getElementById('capacidad_litros');
            const rend   = document.getElementById('rendimiento_estimado');
            const kmOut  = document.getElementById('km_calculados');
            const costo  = document.getElementById('costo_tanque_lleno');
            const hint   = document.getElementById('precio-litro-hint');

            let precios = {}; // { magna: 22.99, premium: 24.99, diesel: 23.50 }

            function selectedCombustible() {
                const r = radios.find(x => x.checked);
                return r ? (r.value || '').toLowerCase() : '';
            }
            function getPrecioLitro() {
                const tipo = selectedCombustible();
                return precios[tipo] ?? 0;
            }
            function recalcKm() {
                const c = parseFloat(cap?.value || '0')  || 0;
                const r = parseFloat(rend?.value || '0') || 0;
                const km = c * r;
                if (kmOut) kmOut.value = km.toFixed(2);
            }
            function recalcCosto() {
                const c = parseFloat(cap?.value || '0') || 0;
                const p = parseFloat(getPrecioLitro() || 0) || 0;
                const total = c * p; // Capacidad total * precio vigente
                if (costo) costo.value = total.toFixed(2);
                if (hint) {
                    if (p > 0) hint.textContent = `(Usando $${p.toFixed(3)} MXN/L — ${selectedCombustible().toUpperCase()})`;
                    else hint.textContent = '(Precio no disponible)';
                }
            }

            async function loadPrecios() {
                try {
                    const res = await fetch(ROUTE_CURRENT, { headers: { 'Accept': 'application/json', 'X-Requested-With':'XMLHttpRequest' }});
                    const data = await res.json();
                    precios = {};
                    (data?.data || []).forEach(x => {
                        precios[(x.combustible || '').toLowerCase()] = Number(x.precio_por_litro);
                    });
                } catch (e) {
                    precios = {};
                }
            }

            // Listeners
            radios.forEach(r => r.addEventListener('change', () => { recalcCosto(); }));
            cap?.addEventListener('input', () => { recalcKm(); recalcCosto(); });
            rend?.addEventListener('input', recalcKm);

            // Init
            (async () => {
                await loadPrecios();
                // UX: si aún no hay selección, marcamos la primera opción
                if (!selectedCombustible() && radios.length) {
                    radios[0].checked = true;
                }
                recalcKm();
                recalcCosto();
            })();
        });
    </script>
</x-app-layout>
