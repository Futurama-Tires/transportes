{{-- resources/views/vehiculos/tanques/create.blade.php — Versión Tabler ejecutiva --}}
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
                            <i class="ti ti-gas-station me-1"></i> Tanques
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
                                    <a href="{{ route('vehiculos.tanques.index', $vehiculo) }}"><i class="ti ti-gas-station me-1"></i> Tanques</a>
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

                                    {{-- Número de tanque --}}
                                    <div class="col-md-4">
                                        <label for="numero_tanque" class="form-label">
                                            Número de tanque
                                            <i class="ti ti-info-circle text-secondary ms-1" data-bs-toggle="tooltip" title="Identificador interno (1, 2, 3…)."></i>
                                        </label>
                                        <div class="input-icon">
                                            <span class="input-icon-addon"><i class="ti ti-hash"></i></span>
                                            <input id="numero_tanque" type="number" name="numero_tanque" min="1" max="255"
                                                   value="{{ old('numero_tanque') }}"
                                                   class="form-control @error('numero_tanque') is-invalid @enderror"
                                                   placeholder="1">
                                        </div>
                                        @error('numero_tanque') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
                                        <div class="form-hint">Selecciona el combustible que carga este tanque.</div>
                                    </div>

                                    {{-- Capacidad (L) --}}
                                    <div class="col-md-6">
                                        <label for="capacidad_litros" class="form-label">Capacidad (L) <span class="text-danger">*</span></label>
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

                                    {{-- Costo tanque lleno --}}
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
                                        <div class="form-hint">Opcional; útil para proyecciones de gasto.</div>
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
                                        Define un número de tanque único por vehículo.
                                    </li>
                                    <li class="mb-3 d-flex">
                                        <i class="ti ti-check me-2 text-teal"></i>
                                        Usa el rendimiento promedio real cuando sea posible.
                                    </li>
                                    <li class="mb-0 d-flex">
                                        <i class="ti ti-check me-2 text-teal"></i>
                                        El costo de tanque lleno ayuda a presupuestar rutas.
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

            // Cálculo en vivo de km (capacidad * rendimiento)
            const cap = document.getElementById('capacidad_litros');
            const rend = document.getElementById('rendimiento_estimado');
            const out = document.getElementById('km_calculados');

            function recalc() {
                const c = parseFloat(cap?.value || '0') || 0;
                const r = parseFloat(rend?.value || '0') || 0;
                const km = c * r;
                if (out) out.value = km.toFixed(2);
            }
            cap?.addEventListener('input', recalc);
            rend?.addEventListener('input', recalc);
        });
    </script>
</x-app-layout>
