{{-- resources/views/cargas/edit.blade.php — versión Tabler ejecutiva (total auto y editable) --}}
<x-app-layout>
    @vite(['resources/js/app.js'])

    @php
        /** @var \App\Models\CargaCombustible $carga */
        $isEdit = isset($carga) && $carga->exists;
        $fechaValue   = old('fecha', isset($carga->fecha) ? \Illuminate\Support\Carbon::parse($carga->fecha)->format('Y-m-d') : '');
        $estadoActual = $carga->estado ?? 'Pendiente';
        $estadoValue  = old('estado', $estadoActual);

        // (Preparado por si luego quieres revivir una galería; actualmente no se usa)
        $galleryItems = [];
        $fotosCol = isset($carga->fotos) ? $carga->fotos : collect();
        $fotosCol = method_exists($fotosCol, 'values') ? $fotosCol->values() : $fotosCol;
        foreach ($fotosCol as $f) {
            $tipo = isset($f->tipo) && $f->tipo !== null ? strtoupper($f->tipo) : 'FOTO';
            $galleryItems[] = [
                'src'  => route('cargas.fotos.show', $f),
                'tipo' => $tipo,
                'id'   => $f->id,
            ];
        }
    @endphp

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a>Inicio</a></li>
                        <li class="breadcrumb-item"><a>Panel</a></li>
                        <li class="breadcrumb-item"><a>Cargas de combustible</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Editar carga de combustible</li>
                    </ol>
                    <div class="col">
                        <h2 class="page-title mb-0">Editar Carga de Combustible</h2>
                    </div>
                    <div class="col-auto ms-auto d-flex align-items-center gap-2">
                        <a href="{{ route('cargas.index') }}" class="btn btn-primary">
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

            {{-- === FORM PRINCIPAL: UPDATE === --}}
            <form method="POST" action="{{ route('cargas.update', $carga) }}" autocomplete="off">
                @csrf
                @method('PUT')

                <div class="row row-cards">
                    {{-- Card: Datos de la carga --}}
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <h3 class="card-title">Datos de la carga #{{ $carga->id }}</h3>
                                {{-- (Sin botón extra de aprobar aquí) --}}
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Estado --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label d-flex align-items-center gap-2">
                                            Estado
                                            <span id="inlineEstadoBadge" class="badge">
                                                {{ $estadoValue === 'Aprobada' ? 'Aprobada' : 'Pendiente' }}
                                            </span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-shield-check"></i></span>
                                            <select id="estadoSelect"
                                                    name="estado"
                                                    class="form-select estado-select @error('estado') is-invalid @enderror"
                                                    required>
                                                <option value="Pendiente" @selected($estadoValue === 'Pendiente')>Pendiente</option>
                                                <option value="Aprobada"  @selected($estadoValue === 'Aprobada')>Aprobada</option>
                                            </select>
                                            @error('estado')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="form-hint">
                                            Cambia el estado y presiona <strong>Guardar cambios</strong>.
                                        </div>
                                    </div>

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

                                    {{-- Total (auto-llenado tras teclear precio/litros, pero editable) --}}
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
                                        <div class="form-hint">
                                            Se propone automáticamente como <em>Litros × Precio</em>;
                                            puedes modificarlo manualmente si el ticket dice otra cosa.
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
                                        <label class="form-label">Vehículo<span class="text-danger">*</span></label>
                                        <select name="vehiculo_id" class="form-select @error('vehiculo_id') is-invalid @enderror" required>
                                            <option value="">Seleccione…</option>
                                            @foreach($vehiculos as $v)
                                                <option value="{{ $v->id }}"
                                                    @selected((int)old('vehiculo_id', $carga->vehiculo_id ?? 0) === $v->id)>
                                                    {{ $v->unidad }} — {{ $v->placa ?? $v->placas }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('vehiculo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="form-hint">
                                            Si cambias el vehículo, el sistema recalculará el <strong>KM inicial</strong> usando la carga previa del vehículo seleccionado.
                                        </div>
                                    </div>

                                    {{-- KM Inicial (solo lectura) --}}
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">KM Inicial</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-road"></i></span>
                                            <input type="number" name="km_inicial"
                                                   value="{{ old('km_inicial', $carga->km_inicial ?? null) }}"
                                                   class="form-control @error('km_inicial') is-invalid @enderror"
                                                   readonly>
                                            @error('km_inicial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="form-hint">Se determina automáticamente a partir del histórico.</div>
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
                                        <div class="form-hint">Si esta carga queda como la más reciente, actualizará el odómetro del vehículo.</div>
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

                            {{-- FOOTER de la card principal --}}
                            <div class="card-footer d-flex justify-content-between">
                                <button type="submit"
                                        class="btn btn-outline-danger"
                                        form="delete-carga-{{ $carga->id }}"
                                        onclick="return confirm('¿Seguro que quieres eliminar la carga #{{ $carga->id }}?');">
                                    <i class="ti ti-trash me-1"></i> Eliminar
                                </button>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('cargas.index') }}" class="btn btn-link">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i> Guardar cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Métricas calculadas --}}
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
                </div>
            </form>

            {{-- DELETE oculto (CORREGIDO: apunta a cargas.destroy) --}}
            <form id="delete-carga-{{ $carga->id }}"
                  action="{{ route('cargas.destroy', $carga) }}"
                  method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>

            {{-- ===== FOTOS DE LA CARGA (sin galería flotante) ===== --}}
            <div class="row row-cards mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <h3 class="card-title mb-0">Fotografías</h3>
                                {{-- (Se eliminó el botón "Ver galería") --}}
                            </div>

                            <div class="d-none d-md-block">
                                @if (session('success'))
                                    <span class="badge bg-green-lt">{{ session('success') }}</span>
                                @endif
                                @if (session('error'))
                                    <span class="badge bg-red-lt">{{ session('error') }}</span>
                                @endif
                            </div>

                            <form class="d-flex flex-wrap align-items-center gap-2"
                                  action="{{ route('cargas.fotos.store', $carga) }}"
                                  method="POST" enctype="multipart/form-data">
                                @csrf
                                <select name="tipo" class="form-select" style="width:auto">
                                    <option value="ticket">Ticket</option>
                                    <option value="voucher">Voucher</option>
                                    <option value="odometro">Odómetro</option>
                                    <option value="extra" selected>Extra</option>
                                </select>
                                <input type="file" name="image" class="form-control" style="width:260px" accept="image/*" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-upload me-1"></i> Subir foto
                                </button>
                            </form>
                        </div>

                        <div class="card-body">
                            @php $fotos = $carga->fotos ?? collect(); @endphp

                            <div class="d-md-none mb-2">
                                @if (session('success'))
                                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger py-2">{{ session('error') }}</div>
                                @endif
                            </div>

                            @if ($fotos->isEmpty())
                                <div class="text-secondary">No hay fotografías asociadas todavía.</div>
                            @else
                                <div id="cargaPhotosGrid" class="row g-3">
                                    @foreach ($fotos as $idx => $foto)
                                        @php $url = route('cargas.fotos.show', $foto); @endphp
                                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                            <div class="card card-sm shadow-sm">
                                                <div class="card-body p-2">
                                                    <div class="ratio ratio-4x3 rounded overflow-hidden border">
                                                        <a href="{{ $url }}"
                                                           class="carga-photo-link"
                                                           title="Abrir imagen ({{ strtoupper($foto->tipo) }})"
                                                           target="_blank" rel="noopener">
                                                            <img src="{{ $url }}"
                                                                 alt="{{ $foto->tipo }}"
                                                                 class="w-100 h-100"
                                                                 loading="lazy"
                                                                 style="object-fit: cover;">
                                                        </a>
                                                    </div>
                                                    <div class="mt-2 small d-flex justify-content-between align-items-center">
                                                        <div class="text-secondary">
                                                            <i class="ti ti-tag"></i> {{ strtoupper($foto->tipo) }}
                                                        </div>
                                                        <form action="{{ route('cargas.fotos.destroy', [$carga, $foto]) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('Eliminar esta foto?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    @if($foto->original_name || $foto->size)
                                                        <div class="text-secondary mt-1" style="font-size: 11px;">
                                                            {{ $foto->original_name ?? basename($foto->path) }}
                                                            @if($foto->size) · {{ number_format($foto->size/1024, 1) }} KB @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>

        </div>
    </div>

    {{-- ===== ESTILOS PARA EL SELECT DE ESTADO ===== --}}
    <style>
        .estado-select.pendiente {
            background-color: var(--tblr-yellow-lt, #fff7e6) !important;
            border-color: var(--tblr-yellow, #f59f00) !important;
            color: #8a6d00 !important;
            font-weight: 600;
        }
        .estado-select.aprobada {
            background-color: var(--tblr-green-lt, #e6fcf5) !important;
            border-color: var(--tblr-green, #2fb344) !important;
            color: #1b6b2b !important;
            font-weight: 600;
        }
    </style>

    {{-- ===== SCRIPTS ===== --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Estado UI (select coloreado + badges en tiempo real)
        const estadoSelect = document.getElementById('estadoSelect');
        const headerBadge  = document.getElementById('headerEstadoBadge'); // puede no existir
        const inlineBadge  = document.getElementById('inlineEstadoBadge');

        function applyEstadoStyles() {
            if (!estadoSelect) return;
            const v = estadoSelect.value === 'Aprobada' ? 'Aprobada' : 'Pendiente';

            estadoSelect.classList.remove('aprobada', 'pendiente');
            estadoSelect.classList.add(v === 'Aprobada' ? 'aprobada' : 'pendiente');

            if (headerBadge) {
                headerBadge.textContent = v;
                headerBadge.classList.remove('bg-green-lt','bg-yellow-lt');
                headerBadge.classList.add(v === 'Aprobada' ? 'bg-green-lt' : 'bg-yellow-lt');
            }
            if (inlineBadge) {
                inlineBadge.textContent = v;
                inlineBadge.classList.remove('bg-green-lt','bg-yellow-lt');
                inlineBadge.classList.add(v === 'Aprobada' ? 'bg-green-lt' : 'bg-yellow-lt');
            }
        }

        if (estadoSelect) {
            applyEstadoStyles();
            estadoSelect.addEventListener('change', applyEstadoStyles, { passive: true });
        }
    });
    </script>

    {{-- Total = Litros × Precio (auto-llenado simple, editable por el usuario) --}}
    <script>
      (function () {
        const precio = document.getElementById('precioInput');
        const litros = document.getElementById('litrosInput');
        const total  = document.getElementById('totalInput');
        if (!precio || !litros || !total) return;

        let lastAuto = null;
        const tol = 0.005; // tolerancia
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

          if (total.value === '' || (lastAuto !== null && approxEq(tVal, lastAuto))) {
            total.value = toMoney(cand);
            lastAuto = cand;
          }
        }

        // Inicial
        if (!total.value) {
          recalcIfAppropriate();
        } else {
          const p0 = fnum(precio.value);
          const l0 = fnum(litros.value);
          if (!isNaN(p0) && !isNaN(l0)) {
            const cand0 = parseFloat(toMoney(p0 * l0));
            const t0 = fnum(total.value);
            if (!isNaN(t0) && approxEq(t0, cand0)) lastAuto = t0;
          }
        }

        // Eventos
        precio.addEventListener('input', recalcIfAppropriate);
        litros.addEventListener('input', recalcIfAppropriate);
        total.addEventListener('input', function () {
          const tVal = fnum(total.value);
          if (lastAuto !== null && approxEq(tVal, lastAuto)) {
            // sigue coincidiendo, mantener autocompletado
          } else {
            // usuario lo modificó; deja de autollenar
            lastAuto = null;
          }
        });
      })();
    </script>
</x-app-layout>
