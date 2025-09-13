{{-- resources/views/vehiculos/edit.blade.php — Versión Tabler (estilo ejecutivo + galería con modal) --}}
<x-app-layout>
    {{-- Si ya incluyes @vite en tu layout, puedes quitar esta línea --}}
    @vite(['resources/js/app.js'])

    {{-- ===== HEADER ===== --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title mb-0 d-flex align-items-center gap-2">
                            <i class="ti ti-steering-wheel"></i>
                            Editar Vehículo
                        </h2>
                        <div class="text-secondary small mt-1">Actualiza la información general del vehículo.</div>
                    </div>
                    <div class="col-auto ms-auto d-flex gap-2">
                        <a href="{{ route('vehiculos.tanques.index', $vehiculo) }}" class="btn btn-warning">
                            <i class="ti ti-gas-station me-1"></i>
                            Editar capacidad del tanque
                        </a>
                        <a href="{{ route('vehiculos.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Volver al listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- ===== FORM PRINCIPAL (UPDATE) ===== --}}
            <form id="vehiculo-form" method="POST" action="{{ route('vehiculos.update', $vehiculo) }}" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Alertas --}}
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>
                        Revisa los campos marcados y vuelve a intentar.
                    </div>
                @endif

                <div class="card">
                    <div class="card-header justify-content-between">
                        <h3 class="card-title d-flex align-items-center gap-2 mb-0">
                            <i class="ti ti-car"></i>
                            Datos del vehículo
                        </h3>
                        <span class="badge bg-azure-lt">
                            <i class="ti ti-pencil me-1"></i> Edición
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Ubicación --}}
                            <div class="col-12 col-md-6">
                                <label for="ubicacion" class="form-label">Ubicación <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-map-pin"></i></span>
                                    <select id="ubicacion" name="ubicacion" class="form-select @error('ubicacion') is-invalid @enderror" required>
                                        <option value="">-- Selecciona ubicación --</option>
                                        <option value="CVC" @selected(old('ubicacion', $vehiculo->ubicacion ?? '')=='CVC')>Cuernavaca</option>
                                        <option value="IXT" @selected(old('ubicacion', $vehiculo->ubicacion ?? '')=='IXT')>Ixtapaluca</option>
                                        <option value="QRO" @selected(old('ubicacion', $vehiculo->ubicacion ?? '')=='QRO')>Querétaro</option>
                                        <option value="VALL" @selected(old('ubicacion', $vehiculo->ubicacion ?? '')=='VALL')>Vallejo</option>
                                        <option value="GDL" @selected(old('ubicacion', $vehiculo->ubicacion ?? '')=='GDL')>Guadalajara</option>
                                    </select>
                                </div>
                                @error('ubicacion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Propietario --}}
                            <div class="col-12 col-md-6">
                                <label for="propietario" class="form-label">Propietario <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                    <input id="propietario" type="text" name="propietario" value="{{ old('propietario', $vehiculo->propietario ?? '') }}" class="form-control @error('propietario') is-invalid @enderror" required placeholder="Nombre del propietario">
                                </div>
                                @error('propietario') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Unidad --}}
                            <div class="col-12 col-md-6">
                                <label for="unidad" class="form-label">Unidad <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-truck"></i></span>
                                    <input id="unidad" type="text" name="unidad" value="{{ old('unidad', $vehiculo->unidad ?? '') }}" class="form-control @error('unidad') is-invalid @enderror" required placeholder="Ej. U-012">
                                </div>
                                @error('unidad') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Serie (VIN) --}}
                            <div class="col-12 col-md-6">
                                <label for="serie" class="form-label">Serie (VIN) <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-barcode"></i></span>
                                    <input id="serie" type="text" name="serie" value="{{ old('serie', $vehiculo->serie ?? '') }}" class="form-control @error('serie') is-invalid @enderror" required aria-describedby="serie_help" placeholder="Número de serie completo">
                                </div>
                                <div id="serie_help" class="form-hint">Usa el número de serie completo registrado en la tarjeta.</div>
                                @error('serie') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Marca --}}
                            <div class="col-12 col-md-6">
                                <label for="marca" class="form-label">Marca</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-badge"></i></span>
                                    <input id="marca" type="text" name="marca" value="{{ old('marca', $vehiculo->marca ?? '') }}" class="form-control @error('marca') is-invalid @enderror" placeholder="Ej. Nissan, Toyota…">
                                </div>
                                @error('marca') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Año --}}
                            <div class="col-12 col-md-6">
                                <label for="anio" class="form-label">Año</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-calendar-stats"></i></span>
                                    <input id="anio" type="number" name="anio" min="1900" max="{{ date('Y') + 1 }}" value="{{ old('anio', $vehiculo->anio ?? '') }}" class="form-control @error('anio') is-invalid @enderror" placeholder="{{ date('Y') }}">
                                </div>
                                @error('anio') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Motor --}}
                            <div class="col-12 col-md-6">
                                <label for="motor" class="form-label">Motor</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-engine"></i></span>
                                    <input id="motor" type="text" name="motor" value="{{ old('motor', $vehiculo->motor ?? '') }}" class="form-control @error('motor') is-invalid @enderror" placeholder="Ej. 2.5 L / Diesel">
                                </div>
                                @error('motor') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Placa --}}
                            <div class="col-12 col-md-6">
                                <label for="placa" class="form-label">Placa</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-license"></i></span>
                                    <input id="placa" type="text" name="placa" value="{{ old('placa', $vehiculo->placa ?? '') }}" class="form-control @error('placa') is-invalid @enderror" placeholder="ABC-123-4">
                                </div>
                                @error('placa') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Estado --}}
                            <div class="col-12 col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-map"></i></span>
                                    <input id="estado" type="text" name="estado" value="{{ old('estado', $vehiculo->estado ?? '') }}" class="form-control @error('estado') is-invalid @enderror" placeholder="Entidad federativa">
                                </div>
                                @error('estado') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Tarjeta SiVale --}}
                            <div class="col-12">
                                <label for="tarjeta_si_vale_id" class="form-label">Tarjeta SiVale</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-credit-card"></i></span>
                                    <select id="tarjeta_si_vale_id" name="tarjeta_si_vale_id" class="form-select @error('tarjeta_si_vale_id') is-invalid @enderror" aria-describedby="sivale_help">
                                        <option value="">-- Sin tarjeta asignada --</option>
                                        @foreach($tarjetas as $tarjeta)
                                            <option value="{{ $tarjeta->id }}" @selected(old('tarjeta_si_vale_id', $vehiculo->tarjeta_si_vale_id ?? '')==$tarjeta->id)>
                                                {{ $tarjeta->numero_tarjeta }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="sivale_help" class="form-hint">Selecciona una tarjeta vigente, si aplica.</div>
                                @error('tarjeta_si_vale_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Vencimiento tarjeta de circulación --}}
                            <div class="col-12 col-md-6">
                                <label for="vencimiento_t_circulacion" class="form-label">Vencimiento tarjeta de circulación</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-calendar-event"></i></span>
                                    <input id="vencimiento_t_circulacion" type="date" name="vencimiento_t_circulacion" value="{{ old('vencimiento_t_circulacion', $vehiculo->vencimiento_t_circulacion ?? '') }}" class="form-control @error('vencimiento_t_circulacion') is-invalid @enderror">
                                </div>
                                @error('vencimiento_t_circulacion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Cambio de placas --}}
                            <div class="col-12 col-md-6">
                                <label for="cambio_placas" class="form-label">Cambio de placas</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-calendar-event"></i></span>
                                    <input id="cambio_placas" type="date" name="cambio_placas" value="{{ old('cambio_placas', $vehiculo->cambio_placas ?? '') }}" class="form-control @error('cambio_placas') is-invalid @enderror">
                                </div>
                                @error('cambio_placas') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Póliza HDI --}}
                            <div class="col-12 col-md-6">
                                <label for="poliza_hdi" class="form-label">Póliza HDI</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-shield-check"></i></span>
                                    <input id="poliza_hdi" type="text" name="poliza_hdi" value="{{ old('poliza_hdi', $vehiculo->poliza_hdi ?? '') }}" class="form-control @error('poliza_hdi') is-invalid @enderror" placeholder="Número de póliza">
                                </div>
                                @error('poliza_hdi') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Póliza Latino --}}
                            <div class="col-12 col-md-6">
                                <label for="poliza_latino" class="form-label">Póliza Latino</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-shield-plus"></i></span>
                                    <input id="poliza_latino" type="text" name="poliza_latino" value="{{ old('poliza_latino', $vehiculo->poliza_latino ?? '') }}" class="form-control @error('poliza_latino') is-invalid @enderror" placeholder="Número de póliza">
                                </div>
                                @error('poliza_latino') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Póliza Qualitas --}}
                            <div class="col-12 col-md-6">
                                <label for="poliza_qualitas" class="form-label">Póliza Qualitas</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-shield-lock"></i></span>
                                    <input id="poliza_qualitas" type="text" name="poliza_qualitas" value="{{ old('poliza_qualitas', $vehiculo->poliza_qualitas ?? '') }}" class="form-control @error('poliza_qualitas') is-invalid @enderror" placeholder="Número de póliza">
                                </div>
                                @error('poliza_qualitas') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Subir nuevas fotos --}}
                    <div class="card-body border-top">
                        <h3 class="card-title d-flex align-items-center gap-2">
                            <i class="ti ti-photo-plus"></i>
                            Agregar nuevas fotos
                        </h3>
                        <div class="form-hint mb-2">JPG, JPEG, PNG o WEBP. Máx 8MB c/u.</div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-upload"></i></span>
                            <input type="file" name="fotos[]" accept="image/*" multiple class="form-control @error('fotos') is-invalid @enderror @error('fotos.*') is-invalid @enderror">
                        </div>
                        @error('fotos')   <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @error('fotos.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Footer acciones --}}
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ url()->previous() ?: route('vehiculos.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i> Cancelar
                        </a>
                        <button type="submit" form="vehiculo-form" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Guardar cambios
                        </button>
                    </div>
                </div>
            </form>

            {{-- ===== FOTOS ACTUALES ===== --}}
            @php
                // PRECOMPUTAR datos de fotos para evitar problemas de parseo dentro del atributo HTML
                $photosData = $vehiculo->fotos->values()->map(function($f){
                    return [
                        'id'  => $f->id,
                        'src' => route('vehiculos.fotos.show', $f),
                    ];
                })->all();
            @endphp

            <div class="card mt-3">
                <div class="card-header justify-content-between">
                    <h3 class="card-title d-flex align-items-center gap-2 mb-0">
                        <i class="ti ti-photo"></i>
                        Fotos actuales
                    </h3>
                    <span class="badge bg-secondary-lt">
                        {{ $vehiculo->fotos->count() }} foto(s)
                    </span>
                </div>
                <div class="card-body">
                    @if($vehiculo->fotos->isEmpty())
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-photo-off"></i></div>
                            <p class="empty-title">Este vehículo aún no tiene fotos</p>
                            <p class="empty-subtitle text-secondary">Puedes subirlas desde la sección “Agregar nuevas fotos”.</p>
                        </div>
                    @else
                        <div class="row g-2" id="photosGrid" data-photos='@json($photosData)'>
                            @foreach($vehiculo->fotos as $foto)
                                <div class="col-6 col-sm-4 col-md-3">
                                    <div class="card card-link position-relative">
                                        <div class="img-responsive img-responsive-4x3 card-img-top" style="background-image: url('{{ route('vehiculos.fotos.show', $foto) }}')"></div>

                                        {{-- Eliminar foto --}}
                                        <form method="POST"
                                            action="{{ route('vehiculos.fotos.destroy', [$vehiculo, $foto]) }}"
                                            onsubmit="return confirm('¿Eliminar esta foto?')"
                                            class="position-absolute top-0 end-0 m-1 z-3">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-icon btn-sm position-relative">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>

                                        {{-- Abrir galería --}}
                                        <a href="javascript:void(0)" class="stretched-link veh-photo" data-index="{{ $loop->index }}" title="Ver grande"></a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-dark btn-sm" id="openGalleryAll">
                                <i class="ti ti-slideshow me-1"></i> Ver galería
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-secondary small mt-3">
                <i class="ti ti-info-circle me-1"></i>
                Nota: si los campos de fecha están almacenados como texto, el selector enviará el valor en formato <code>YYYY-MM-DD</code>.
                Considera migrarlos a tipo <code>DATE</code> para validaciones y reportes más consistentes.
            </div>

            {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    {{-- ===== MODAL GALERÍA ===== --}}
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title h4" id="galleryModalLabel">
                        <i class="ti ti-photo me-2"></i>Galería de fotos
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="galleryCarousel" class="carousel slide" data-bs-ride="false">
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
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SCRIPTS =====
         Asegúrate en resources/js/app.js:
         import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js'; window.bootstrap = bootstrap;
    --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const photosGrid = document.getElementById('photosGrid');
        if (!photosGrid) return;

        let photos = [];
        try { photos = JSON.parse(photosGrid.getAttribute('data-photos') || '[]'); }
        catch { photos = []; }

        const galleryInner   = document.getElementById('galleryInner');
        const galleryEl      = document.getElementById('galleryCarousel');
        const galleryModalEl = document.getElementById('galleryModal');

        function openGallery(startIndex = 0){
            if (!photos.length) return;
            galleryInner.innerHTML = '';
            photos.forEach((p, i) => {
                const div = document.createElement('div');
                div.className = 'carousel-item' + (i === startIndex ? ' active' : '');
                div.innerHTML = `<img src="${p.src}" class="d-block w-100 rounded" alt="Foto ${i+1}">`;
                galleryInner.appendChild(div);
            });
            const Carousel = window.bootstrap?.Carousel;
            if (Carousel) {
                const instance = Carousel.getInstance(galleryEl) || new Carousel(galleryEl, { interval: false });
                instance.to(startIndex);
            }
            const modal = window.bootstrap ? new window.bootstrap.Modal(galleryModalEl) : null;
            modal?.show();
        }

        // Abrir por tarjeta
        photosGrid.addEventListener('click', (e) => {
            const a = e.target.closest('.veh-photo');
            if (!a) return;
            const idx = parseInt(a.getAttribute('data-index') || '0', 10) || 0;
            openGallery(idx);
        });

        // Abrir todo
        const openAllBtn = document.getElementById('openGalleryAll');
        if (openAllBtn) openAllBtn.addEventListener('click', () => openGallery(0));
    });
    </script>
</x-app-layout>
