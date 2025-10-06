{{-- resources/views/cargas/create.blade.php — versión Tabler sencilla (total auto y editable) --}}
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

            {{-- === FORM PRINCIPAL: CREATE/UPDATE === --}}
            <form method="POST" action="{{ $action }}" autocomplete="off">
                @csrf
                @if($isEdit) @method('PUT') @endif

                {{-- En creación web, el controlador ya forza "Aprobada"; este hidden es inofensivo --}}
                @unless($isEdit)
                    <input type="hidden" name="estado" value="Aprobada">
                @endunless

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
                                            <input id="precioInput" type="number" step="0.01" min="0" name="precio"
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
                                            <input id="litrosInput" type="number" step="0.001" min="0.001" name="litros"
                                                   value="{{ old('litros', $carga->litros ?? null) }}"
                                                   class="form-control @error('litros') is-invalid @enderror"
                                                   required>
                                            @error('litros')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    {{-- Total (auto-llenado tras teclear precio/litros, pero editable siempre) --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Total ($) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-receipt-2"></i></span>
                                            <input id="totalInput" type="number" step="0.01" min="0.01" name="total"
                                                   value="{{ old('total', $carga->total ?? null) }}"
                                                   class="form-control @error('total') is-invalid @enderror"
                                                   required>
                                            @error('total')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                        <label class="form-label">Vehículo <span class="text-danger">*</span></label>
                                        <select id="vehiculoSelect" name="vehiculo_id" class="form-select @error('vehiculo_id') is-invalid @enderror" required>
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
                                        <div class="form-hint">Se toma del odómetro del vehículo o de la carga previa.</div>
                                    </div>

                                    {{-- KM Final --}}
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
                                        <div class="form-hint">Si esta carga es la más reciente, actualizará el odómetro.</div>
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
                                        <div class="col-12 col-sm-6 col-lg-4">
                                            <label class="form-label">Recorrido (km)</label>
                                            <input type="text" class="form-control" value="{{ $carga->recorrido ?? '' }}" disabled>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-4">
                                            <label class="form-label">Rendimiento (km/L)</label>
                                            <input type="text" class="form-control" value="{{ $carga->rendimiento ?? '' }}" disabled>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-4">
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

    {{-- JS: KM Inicial desde el vehículo (solo en creación) --}}
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
        setKmInicialFromSelect();
      })();
    </script>
    @endif

    {{-- JS: Total = Litros × Precio (auto-llenado simple, editable por el usuario) --}}
    <script>
      (function () {
        const precio = document.getElementById('precioInput');
        const litros = document.getElementById('litrosInput');
        const total  = document.getElementById('totalInput');
        if (!precio || !litros || !total) return;

        // Si el usuario escribe en "total", no lo sobreescribimos a menos que "coincida" con el último auto.
        let lastAuto = null;
        const tol = 0.005; // tolerancia para comparar

        const toMoney = (n) => (Math.round(n * 100) / 100).toFixed(2);
        const fnum = (v) => {
          const x = parseFloat(String(v).replace(',', '.'));
          return isFinite(x) ? x : NaN;
        };
        const approxEq = (a, b) => Math.abs(a - b) <= tol;

        function recalcIfAppropriate() {
          const p = fnum(precio.value);
          const l = fnum(litros.value);
          if (isNaN(p) || isNaN(l) || p < 0 || l < 0) return;

          const cand = parseFloat(toMoney(p * l));

          const tVal = fnum(total.value);
          // Regla:
          // - Si total está vacío, siempre autollenar.
          // - Si total == último auto (≈), actualizar al nuevo cálc.
          // - Si total difiere de último auto (usuario ya lo ajustó), NO tocar.
          if (total.value === '' || (lastAuto !== null && approxEq(tVal, lastAuto))) {
            total.value = toMoney(cand);
            lastAuto = cand;
          }
        }

        // Inicial: si total viene vacío, proponlo
        if (!total.value) {
          recalcIfAppropriate();
        } else {
          // Si el total coincide con el cálculo actual, registrar lastAuto
          const p0 = fnum(precio.value);
          const l0 = fnum(litros.value);
          if (!isNaN(p0) && !isNaN(l0)) {
            const cand0 = parseFloat(toMoney(p0 * l0));
            const t0 = fnum(total.value);
            if (!isNaN(t0) && approxEq(t0, cand0)) {
              lastAuto = t0;
            }
          }
        }

        // Eventos de entrada en precio/litros -> intentar autollenar/actualizar
        precio.addEventListener('input', recalcIfAppropriate);
        litros.addEventListener('input', recalcIfAppropriate);

        // Si el usuario modifica manualmente total, ya no forzamos cambios a menos que vuelva a igualar
        total.addEventListener('input', function () {
          const tVal = fnum(total.value);
          if (!isNaN(tVal)) {
            // Si coincide con el auto actual, permite seguir autollenando
            if (lastAuto !== null && approxEq(tVal, lastAuto)) {
              // mantener lastAuto
            } else {
              // marca que ahora es edición del usuario
              lastAuto = null;
            }
          } else {
            lastAuto = null;
          }
        });
      })();
    </script>
</x-app-layout>
