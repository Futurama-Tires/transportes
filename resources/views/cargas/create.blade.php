{{-- resources/views/cargas_combustible/create.blade.php — versión Tabler ejecutiva --}}
<x-app-layout>
    @php
        /** @var \App\Models\CargaCombustible|null $carga */
        $isEdit = isset($carga) && $carga->exists;
        $fechaValue = old('fecha', isset($carga->fecha) ? \Illuminate\Support\Carbon::parse($carga->fecha)->format('Y-m-d') : '');
        $title = $isEdit ? 'Editar Carga de Combustible' : 'Nueva Carga de Combustible';
        $action = $isEdit ? route('cargas.update', $carga) : route('cargas.store');
    @endphp

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title mb-0">{{ $title }}</h2>
                        <div class="text-secondary small mt-1">Completa los campos y guarda los cambios.</div>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('cargas.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Volver a la lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- Errores globales --}}
            @if ($errors->any())
                <div class="alert alert-danger mb-3" role="alert">
                    <div class="d-flex">
                        <div class="me-2"><i class="ti ti-alert-circle"></i></div>
                        <div>
                            <strong>Revisa los campos:</strong>
                            <ul class="mb-0 ps-4">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ $action }}" autocomplete="off">
                @csrf
                @if($isEdit) @method('PUT') @endif

                <div class="row row-cards">
                    {{-- Card: Datos principales --}}
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Datos de la carga</h3>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Fecha --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                                        <div class="input-icon">
                                            <span class="input-icon-addon"><i class="ti ti-calendar"></i></span>
                                            <input type="date"
                                                   name="fecha"
                                                   value="{{ $fechaValue }}"
                                                   class="form-control @error('fecha') is-invalid @enderror"
                                                   required>
                                            @error('fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    {{-- Ubicación --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Ubicación</label>
                                        <select name="ubicacion" class="form-select @error('ubicacion') is-invalid @enderror">
                                            <option value="">Seleccione…</option>
                                            @foreach($ubicaciones as $u)
                                                <option value="{{ $u }}" @selected(old('ubicacion', $carga->ubicacion ?? null) === $u)>{{ $u }}</option>
                                            @endforeach
                                        </select>
                                        @error('ubicacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    {{-- Tipo de combustible --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Tipo de combustible <span class="text-danger">*</span></label>
                                        <select name="tipo_combustible" class="form-select @error('tipo_combustible') is-invalid @enderror" required>
                                            @foreach($tipos as $t)
                                                <option value="{{ $t }}" @selected(old('tipo_combustible', $carga->tipo_combustible ?? null) === $t)>{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        @error('tipo_combustible')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    {{-- Precio --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Precio ($) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-currency-dollar"></i></span>
                                            <input type="number" step="0.01" min="0" name="precio"
                                                   value="{{ old('precio', $carga->precio ?? null) }}"
                                                   class="form-control @error('precio') is-invalid @enderror"
                                                   required>
                                            @error('precio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="form-hint">Precio por litro.</div>
                                    </div>

                                    {{-- Litros --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Litros <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-gas-station"></i></span>
                                            <input type="number" step="0.001" min="0.001" name="litros"
                                                   value="{{ old('litros', $carga->litros ?? null) }}"
                                                   class="form-control @error('litros') is-invalid @enderror"
                                                   required>
                                            @error('litros')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    {{-- Custodio --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Custodio</label>
                                        <div class="input-icon">
                                            <span class="input-icon-addon"><i class="ti ti-user-shield"></i></span>
                                            <input type="text" name="custodio"
                                                   value="{{ old('custodio', $carga->custodio ?? null) }}"
                                                   class="form-control @error('custodio') is-invalid @enderror">
                                            @error('custodio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    {{-- Operador --}}
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label">Operador <span class="text-danger">*</span></label>
                                        <select name="operador_id" class="form-select @error('operador_id') is-invalid @enderror" required>
                                            <option value="">Seleccione…</option>
                                            @foreach($operadores as $op)
                                                <option value="{{ $op->id }}"
                                                    @selected((int)old('operador_id', $carga->operador_id ?? 0) === $op->id)>
                                                    {{ $op->nombre }} {{ $op->apellido_paterno }} {{ $op->apellido_materno }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('operador_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    {{-- Vehículo --}}
                                    <div class="col-12 col-lg-8">
                                        <label class="form-label">Vehículo (Unidad / Placa) <span class="text-danger">*</span></label>
                                        <select id="vehiculoSelect" name="vehiculo_id" class="form-select @error('vehiculo_id') is-invalid @enderror" required {{ $isEdit ? '' : '' }}>
                                            <option value="">Seleccione…</option>
                                            @foreach($vehiculos as $v)
                                                <option value="{{ $v->id }}"
                                                        data-km="{{ (int)($v->kilometros ?? 0) }}"
                                                        @selected((int)old('vehiculo_id', $carga->vehiculo_id ?? 0) === $v->id)>
                                                    {{ $v->unidad }} — {{ $v->placa ?? $v->placas }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('vehiculo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="form-hint">El KM Inicial se tomará del odómetro actual del vehículo seleccionado.</div>
                                    </div>

                                    {{-- KM Inicial (solo lectura) --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">KM Inicial</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-road"></i></span>
                                            <input id="kmInicialInput" type="number" name="km_inicial"
                                                   value="{{ old('km_inicial', $carga->km_inicial ?? null) }}"
                                                   class="form-control @error('km_inicial') is-invalid @enderror"
                                                   readonly>
                                            @error('km_inicial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="form-hint">Se calcula desde el odómetro del vehículo o de la carga previa.</div>
                                    </div>

                                    {{-- KM Final (requerido en servidor) --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">KM Final</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-road"></i></span>
                                            <input type="number" name="km_final"
                                                   value="{{ old('km_final', $carga->km_final ?? null) }}"
                                                   class="form-control @error('km_final') is-invalid @enderror"
                                                   inputmode="numeric" min="0">
                                            @error('km_final')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="form-hint">Al guardar, este valor actualizará el odómetro del vehículo si esta carga es la más reciente.</div>
                                    </div>

                                    {{-- Destino --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Destino</label>
                                        <div class="input-icon">
                                            <span class="input-icon-addon"><i class="ti ti-map-pin"></i></span>
                                            <input type="text" name="destino"
                                                   value="{{ old('destino', $carga->destino ?? null) }}"
                                                   class="form-control @error('destino') is-invalid @enderror">
                                            @error('destino')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    {{-- Observaciones --}}
                                    <div class="col-12">
                                        <label class="form-label">Observaciones</label>
                                        <textarea name="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror"
                                                  placeholder="Notas, comentarios u observaciones…">{{ old('observaciones', $carga->observaciones ?? null) }}</textarea>
                                        @error('observaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Footer acciones --}}
                            <div class="card-footer d-flex justify-content-between">
                                <a href="{{ route('cargas.index') }}" class="btn btn-link">
                                    Cancelar
                                </a>
                                <div class="d-flex gap-2">
                                    @if(!$isEdit)
                                        <button type="reset" class="btn btn-outline-secondary">
                                            <i class="ti ti-eraser me-1"></i> Limpiar
                                        </button>
                                    @endif
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i> Guardar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Métricas calculadas (solo edición) --}}
                    @if($isEdit)
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Métricas calculadas</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <label class="form-label">Total ($)</label>
                                            <input type="text" class="form-control" value="{{ number_format((float)($carga->total ?? 0), 2) }}" disabled>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <label class="form-label">Recorrido (km)</label>
                                            <input type="text" class="form-control" value="{{ $carga->recorrido ?? '' }}" disabled>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <label class="form-label">Rendimiento (km/L)</label>
                                            <input type="text" class="form-control" value="{{ $carga->rendimiento ?? '' }}" disabled>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <label class="form-label">Diferencia ($)</label>
                                            <input type="text" class="form-control" value="{{ isset($carga->diferencia) ? number_format((float)$carga->diferencia, 2) : '' }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <a href="{{ route('cargas.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-arrow-left me-1"></i> Volver
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </form>

            {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    {{-- JS: Autorrelleno de KM Inicial con el odómetro del vehículo (solo en creación) --}}
    @if(!$isEdit)
    <script>
      (function () {
        const sel = document.getElementById('vehiculoSelect');
        const kmInput = document.getElementById('kmInicialInput');
        if (!sel || !kmInput) return;

        function setKmInicialFromSelect() {
          const opt = sel.options[sel.selectedIndex];
          if (!opt) return;
          const km = opt.getAttribute('data-km');
          kmInput.value = (km !== null && km !== '') ? km : '';
        }

        sel.addEventListener('change', setKmInicialFromSelect);
        // Inicializa si ya viene un vehículo seleccionado por old(...)
        setKmInicialFromSelect();
      })();
    </script>
    @endif
</x-app-layout>
