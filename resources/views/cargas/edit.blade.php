{{-- resources/views/cargas/edit.blade.php — versión Tabler ejecutiva (con estado como desplegable en el formulario principal) --}}
<x-app-layout>
    @vite(['resources/js/app.js'])

    @php
        /** @var \App\Models\CargaCombustible $carga */
        $isEdit = isset($carga) && $carga->exists;
        $fechaValue   = old('fecha', isset($carga->fecha) ? \Illuminate\Support\Carbon::parse($carga->fecha)->format('Y-m-d') : '');
        $estadoActual = $carga->estado ?? 'Pendiente';
        $estadoValue  = old('estado', $estadoActual);

        // ✅ Precomputar items de galería (sin arrow functions ni coalesce) para evitar errores de compilación de Blade/PHP
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
                    <div class="col">
                        <h2 class="page-title mb-0">Editar Carga de Combustible</h2>
                        <div class="text-secondary small mt-1">Actualiza los datos y guarda los cambios.</div>
                    </div>
                    <div class="col-auto ms-auto d-flex align-items-center gap-2">
                        {{-- Badge de estado visible en header (se actualizará en vivo cuando cambie el select) --}}
                        @if($estadoActual === 'Aprobada')
                            <span id="headerEstadoBadge" class="badge bg-green-lt">Aprobada</span>
                        @else
                            <span id="headerEstadoBadge" class="badge bg-yellow-lt">Pendiente</span>
                        @endif

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
                                {{-- ❌ Se eliminó el botón/form de "Aprobar" para evitar formularios anidados --}}
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Estado (select dentro del formulario principal, con colores) --}}
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

            {{-- DELETE oculto --}}
            <form id="delete-carga-{{ $carga->id }}"
                  action="{{ route('cargas.destroy', $carga) }}"
                  method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>

            {{-- ===== GALERÍA DE FOTOS DE LA CARGA ===== --}}
            <div class="row row-cards mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <h3 class="card-title mb-0">Fotos de la carga #{{ $carga->id }}</h3>
                                <button id="openGalleryBtn" type="button" class="btn btn-dark btn-sm d-none">
                                    <i class="ti ti-slideshow me-1"></i> Ver galería
                                </button>
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
                                <div class="text-secondary">No hay fotos asociadas todavía.</div>
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
                                                           data-gallery-index="{{ $idx }}"
                                                           title="Ver imagen ({{ strtoupper($foto->tipo) }})"
                                                           target="_blank">
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

                        <div class="card-footer text-muted small">
                            Las imágenes se sirven a través de una ruta protegida. 
                            Si no se muestran, verifica que la sesión esté activa y que la ruta <code>cargas.fotos.show</code> tenga middleware de autenticación.
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
        /* Realza el select según el estado seleccionado */
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

    {{-- ===== MODAL GALERÍA ===== --}}
    <style>
        #galleryModal .modal-dialog { max-width: min(96vw, 1200px); }
        #galleryModal .modal-content { background: #0b0b0b; color: #fff; border: 0; }
        #galleryModal .modal-header { border: 0; background: transparent; }
        #galleryModal .btn-close { filter: invert(1); opacity: .9; }
        #galleryModal .modal-body { padding: 0; background: #000; }
        #galleryModal .carousel,
        #galleryModal .carousel-inner { height: 82vh; }
        @media (max-width: 768px){
            #galleryModal .carousel,
            #galleryModal .carousel-inner { height: 75vh; }
        }
        #galleryModal .carousel-item { height: 100%; }
        #galleryModal .carousel-item.active,
        #galleryModal .carousel-item-next,
        #galleryModal .carousel-item-prev,
        #galleryModal .carousel-item-start,
        #galleryModal .carousel-item-end {
          display: flex;
          align-items: center;
          justify-content: center;
        }
        #galleryModal .lightbox-img {
          max-height: 80vh; width: auto; max-width: 100%;
          object-fit: contain; user-select: none;
        }
        #galleryModal .carousel-control-prev,
        #galleryModal .carousel-control-next { filter: drop-shadow(0 0 6px rgba(0,0,0,.6)); }
        #galleryModal .carousel-control-prev-icon,
        #galleryModal .carousel-control-next-icon { width: 3rem; height: 3rem; }
        #galleryModal .thumbs { display:flex; gap:.5rem; overflow-x:auto; scrollbar-width:thin; padding:.75rem 1rem 1rem; background:#0b0b0b; }
        #galleryModal .thumb { flex:0 0 auto; width:76px; height:56px; border-radius:.5rem; overflow:hidden; border:2px solid transparent; cursor:pointer; opacity:.9; }
        #galleryModal .thumb:hover { opacity:1; }
        #galleryModal .thumb img { width:100%; height:100%; object-fit:cover; display:block; }
        #galleryModal .thumb.active { border-color:#5b9cff; }
        #galleryModal .caption-badge{ position:absolute; left:1rem; top:1rem; z-index:2; }
    </style>

    <div class="modal modal-blur fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title h4" id="galleryModalLabel">
                        <i class="ti ti-photo me-2"></i>Galería de fotos
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body position-relative">
                    <div id="galleryCarousel" class="carousel slide" data-bs-interval="false" data-bs-touch="true">
                        <div class="carousel-inner" id="galleryInner"></div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>
                </div>
                <div class="thumbs" id="galleryThumbs"></div>
            </div>
        </div>
    </div>

    {{-- ===== SCRIPTS ===== --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // ====== Estado UI (select coloreado + badges en tiempo real) ======
        const estadoSelect = document.getElementById('estadoSelect');
        const headerBadge  = document.getElementById('headerEstadoBadge');
        const inlineBadge  = document.getElementById('inlineEstadoBadge');

        function applyEstadoStyles() {
            if (!estadoSelect) return;
            const v = estadoSelect.value === 'Aprobada' ? 'Aprobada' : 'Pendiente';

            // Select coloreado
            estadoSelect.classList.remove('aprobada', 'pendiente');
            estadoSelect.classList.add(v === 'Aprobada' ? 'aprobada' : 'pendiente');

            // Badges
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

        // ====== Galería ======
        const getCarouselCtor = () => (window.bootstrap && window.bootstrap.Carousel) ? window.bootstrap.Carousel : (window.Carousel || null);
        const getModalCtor    = () => (window.bootstrap && window.bootstrap.Modal)    ? window.bootstrap.Modal    : (window.Modal || null);

        const openGalleryBtn    = document.getElementById('openGalleryBtn');
        const galleryModalEl    = document.getElementById('galleryModal');
        const galleryCarouselEl = document.getElementById('galleryCarousel');
        const galleryInner      = document.getElementById('galleryInner');
        const galleryThumbs     = document.getElementById('galleryThumbs');

        // JSON seguro desde PHP (sin closures ni sintaxis moderna en Blade)
        const galleryItems = @json($galleryItems);

        if (Array.isArray(galleryItems) && galleryItems.length > 0) {
            if (openGalleryBtn) openGalleryBtn.classList.remove('d-none');
        }

        function updateThumbsActive(i){
            Array.prototype.forEach.call(galleryThumbs.querySelectorAll('.thumb'), function(el, idx){
                if (idx === i) el.classList.add('active'); else el.classList.remove('active');
            });
        }

        function buildCarouselSlides(startIndex){
            if (typeof startIndex !== 'number') startIndex = 0;
            galleryInner.innerHTML = '';
            galleryThumbs.innerHTML = '';

            galleryItems.forEach(function(it, i){
                var item = document.createElement('div');
                item.className = 'carousel-item' + (i === startIndex ? ' active' : '');
                item.setAttribute('data-index', i);
                item.innerHTML =
                    '<div class="position-relative h-100 w-100 d-flex align-items-center justify-content-center">' +
                        '<span class="badge bg-primary caption-badge">' + (it.tipo || '') + '</span>' +
                        '<img src="' + it.src + '" class="lightbox-img" alt="Foto ' + (i+1) + ' — ' + (it.tipo || '') + '">' +
                    '</div>';
                galleryInner.appendChild(item);

                var th = document.createElement('button');
                th.type = 'button';
                th.className = 'thumb' + (i === startIndex ? ' active' : '');
                th.setAttribute('data-index', i);
                th.innerHTML = '<img src="' + it.src + '" alt="Miniatura ' + (i+1) + '">';
                th.addEventListener('click', function(){
                    var Carousel = getCarouselCtor();
                    if (!Carousel) return;
                    var car = Carousel.getInstance(galleryCarouselEl);
                    if (car && typeof car.to === 'function') { car.to(i); }
                    updateThumbsActive(i);
                });
                galleryThumbs.appendChild(th);
            });

            var Carousel = getCarouselCtor();
            if (Carousel) {
                var existing = Carousel.getInstance(galleryCarouselEl);
                if (existing && typeof existing.dispose === 'function') existing.dispose();
                var carousel = new Carousel(galleryCarouselEl, { interval: false, ride: false, wrap: true, keyboard: true, touch: true });

                galleryCarouselEl.addEventListener('slid.bs.carousel', function(){
                    var children = Array.prototype.slice.call(galleryInner.children);
                    var idx = children.findIndex(function(el){ return el.classList.contains('active'); });
                    updateThumbsActive(idx);
                }, { passive: true });

                if (startIndex > 0 && typeof carousel.to === 'function') carousel.to(startIndex);
            }
        }

        function openGallery(startIndex){
            if (!Array.isArray(galleryItems) || !galleryItems.length) return;
            buildCarouselSlides(startIndex || 0);
            var Modal = getModalCtor();
            if (!Modal) return;
            var modal = new Modal(galleryModalEl);
            modal.show();
        }

        document.addEventListener('click', function(e){
            var link = e.target.closest ? e.target.closest('.carga-photo-link[data-gallery-index]') : null;
            if (!link) return;
            e.preventDefault();
            var idx = parseInt(link.getAttribute('data-gallery-index'), 10) || 0;
            openGallery(idx);
        });

        if (openGalleryBtn) {
            openGalleryBtn.addEventListener('click', function(){ openGallery(0); });
        }
    });
    </script>
</x-app-layout>
