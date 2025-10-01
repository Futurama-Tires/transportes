{{-- resources/views/operadores/edit.blade.php — un solo form (datos + fotos subir/borrar + galería) con Material Symbols (Google Fonts) --}}
<x-app-layout>
    @vite(['resources/js/app.js'])

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <p class="text-secondary text-uppercase small mb-1">Operadores</p>
                        <h2 class="page-title mb-0">Editar un Operador</h2>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('operadores.index') }}" class="btn btn-outline-secondary">
                            <span class="material-symbols-outlined me-1 align-middle">arrow_back</span> Volver al listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- FLASHES --}}
            @if (session('success'))
                <div class="alert alert-success mb-4" role="alert">
                    <span class="material-symbols-outlined me-2 align-middle">check_circle</span>{{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4" role="alert">
                    <span class="material-symbols-outlined me-2 align-middle">warning</span>Revisa los campos marcados y vuelve a intentar.
                </div>
            @endif

            @php
                $photosData = $operador->fotos->values()->map(function($f){
                    return ['id' => $f->id, 'src' => route('operadores.fotos.show', $f)];
                })->all();
                $nombreCompleto = trim(($operador->nombre ?? '').' '.($operador->apellido_paterno ?? '').' '.($operador->apellido_materno ?? ''));
                $correo = optional($operador->user)->email ?? '—';
            @endphp

            <div class="row g-4">
                {{-- ===== FORM ÚNICO (incluye datos y fotos) ===== --}}
                <div class="col-12 col-xl-8">
<form id="operador-form" method="POST"
      action="{{ route('operadores.update', $operador) }}"
      enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')

                        {{-- DATOS --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0 d-flex align-items-center">
                                    <span class="material-symbols-outlined me-2">badge</span>
                                    Datos del operador
                                </h3>
                                <div class="card-subtitle ms-2">Completa o corrige la información requerida.</div>
                            </div>

                            <div class="card-body pt-3">
                                <div class="row g-4">
                                    <div class="col-12 col-md-6">
                                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">edit_note</span></span>
                                            <input id="nombre" name="nombre" type="text" autocomplete="given-name"
                                                   class="form-control @error('nombre') is-invalid @enderror"
                                                   value="{{ old('nombre', $operador->nombre) }}" required placeholder="Ej. Juan">
                                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="apellido_paterno" class="form-label">Apellido paterno <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">person</span></span>
                                            <input id="apellido_paterno" name="apellido_paterno" type="text" autocomplete="family-name"
                                                   class="form-control @error('apellido_paterno') is-invalid @enderror"
                                                   value="{{ old('apellido_paterno', $operador->apellido_paterno) }}" required placeholder="Ej. Pérez">
                                            @error('apellido_paterno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="apellido_materno" class="form-label">Apellido materno</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">account_circle</span></span>
                                            <input id="apellido_materno" name="apellido_materno" type="text" autocomplete="additional-name"
                                                   class="form-control @error('apellido_materno') is-invalid @enderror"
                                                   value="{{ old('apellido_materno', $operador->apellido_materno) }}" placeholder="(opcional)">
                                            @error('apellido_materno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="email" class="form-label">Correo electrónico</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">mail</span></span>
                                            <input id="email" name="email" type="email" autocomplete="email"
                                                   class="form-control @error('email') is-invalid @enderror"
                                                   value="{{ old('email', optional($operador->user)->email) }}"
                                                   placeholder="usuario@dominio.com" aria-describedby="email_help">
                                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    {{-- Teléfono --}}
                                    <div class="col-12 col-md-6">
                                        <label for="telefono" class="form-label">Teléfono</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">call</span></span>
                                            <input id="telefono" name="telefono" type="tel"
                                                   class="form-control @error('telefono') is-invalid @enderror"
                                                   value="{{ old('telefono', $operador->telefono) }}"
                                                   placeholder="+52 777 123 4567" autocomplete="tel">
                                            @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    {{-- Tipo de sangre --}}
                                    <div class="col-12 col-md-6">
                                        <label for="tipo_sangre" class="form-label">Tipo de sangre</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">bloodtype</span></span>
                                            <input id="tipo_sangre" name="tipo_sangre" type="text"
                                                   class="form-control @error('tipo_sangre') is-invalid @enderror"
                                                   value="{{ old('tipo_sangre', $operador->tipo_sangre) }}"
                                                   placeholder="Ej. O+, A-, B+" maxlength="5">
                                            @error('tipo_sangre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="form-hint">Formato corto (ej.: O+, A-, AB-).</div>
                                    </div>

                                    {{-- ===== NUEVOS CAMPOS ===== --}}

                                    {{-- Estado civil --}}
                                    <div class="col-12 col-md-6">
                                        <label for="estado_civil" class="form-label">Estado civil</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <span class="material-symbols-outlined">diversity_3</span>
                                            </span>
                                            <select id="estado_civil" name="estado_civil"
                                                    class="form-select @error('estado_civil') is-invalid @enderror">
                                                <option value="">(sin especificar)</option>
                                                @foreach(['soltero'=>'Soltero','casado'=>'Casado','viudo'=>'Viudo','divorciado'=>'Divorciado'] as $val=>$label)
                                                    <option value="{{ $val }}" {{ old('estado_civil', $operador->estado_civil)===$val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('estado_civil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    {{-- CURP --}}
                                    <div class="col-12 col-md-6">
                                        <label for="curp" class="form-label">CURP</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">badge</span></span>
                                            <input id="curp" name="curp" type="text"
                                                   class="form-control @error('curp') is-invalid @enderror"
                                                   value="{{ old('curp', $operador->curp) }}" maxlength="18"
                                                   style="text-transform:uppercase"
                                                   oninput="this.value=this.value.toUpperCase().replace(/\s+/g,'');"
                                                   pattern="[A-ZÑ0-9]{18}"
                                                   title="18 caracteres en mayúsculas (A-Z/Ñ/0-9)">
                                            @error('curp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    {{-- RFC --}}
                                    <div class="col-12 col-md-6">
                                        <label for="rfc" class="form-label">RFC</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">verified</span></span>
                                            <input id="rfc" name="rfc" type="text"
                                                   class="form-control @error('rfc') is-invalid @enderror"
                                                   value="{{ old('rfc', $operador->rfc) }}" maxlength="13"
                                                   style="text-transform:uppercase"
                                                   oninput="this.value=this.value.toUpperCase().replace(/\s+/g,'');"
                                                   pattern="([A-ZÑ&]{3,4})\d{6}[A-Z0-9]{3}"
                                                   title="3-4 letras + 6 dígitos de fecha + 3 alfanum.">
                                            @error('rfc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    {{-- Parentesco del contacto de emergencia --}}
                                    <div class="col-12 col-md-6">
                                        <label for="contacto_emergencia_parentesco" class="form-label">Parentesco del contacto de emergencia</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">diversity_1</span></span>
                                            <input id="contacto_emergencia_parentesco" name="contacto_emergencia_parentesco" type="text"
                                                   class="form-control @error('contacto_emergencia_parentesco') is-invalid @enderror"
                                                   value="{{ old('contacto_emergencia_parentesco', $operador->contacto_emergencia_parentesco) }}" placeholder="Ej. Esposa, Hermano, Amigo">
                                            @error('contacto_emergencia_parentesco') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    {{-- Ubicación del contacto de emergencia --}}
                                    <div class="col-12">
                                        <label for="contacto_emergencia_ubicacion" class="form-label">Ubicación del contacto de emergencia</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">place</span></span>
                                            <input id="contacto_emergencia_ubicacion" name="contacto_emergencia_ubicacion" type="text"
                                                   class="form-control @error('contacto_emergencia_ubicacion') is-invalid @enderror"
                                                   value="{{ old('contacto_emergencia_ubicacion', $operador->contacto_emergencia_ubicacion) }}" placeholder="Ej. Cuernavaca, Morelos">
                                            @error('contacto_emergencia_ubicacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="form-hint">Puedes capturar ciudad/estado o una dirección breve.</div>
                                    </div>

                                    {{-- Contacto emergencia nombre --}}
                                    <div class="col-12 col-md-6">
                                        <label for="contacto_emergencia_nombre" class="form-label">Contacto de emergencia (nombre completo)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">contact_emergency</span></span>
                                            <input id="contacto_emergencia_nombre" name="contacto_emergencia_nombre" type="text"
                                                   class="form-control @error('contacto_emergencia_nombre') is-invalid @enderror"
                                                   value="{{ old('contacto_emergencia_nombre', $operador->contacto_emergencia_nombre) }}"
                                                   placeholder="Ej. Juan Pérez">
                                            @error('contacto_emergencia_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    {{-- Contacto emergencia teléfono --}}
                                    <div class="col-12 col-md-6">
                                        <label for="contacto_emergencia_tel" class="form-label">Contacto de emergencia (teléfono)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">call</span></span>
                                            <input id="contacto_emergencia_tel" name="contacto_emergencia_tel" type="tel"
                                                   class="form-control @error('contacto_emergencia_tel') is-invalid @enderror"
                                                   value="{{ old('contacto_emergencia_tel', $operador->contacto_emergencia_tel) }}"
                                                   placeholder="+52 777 000 0000">
                                            @error('contacto_emergencia_tel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- FOTOS: subir + marcar para borrar + galería --}}
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title mb-0 d-flex align-items-center">
                                    <span class="material-symbols-outlined me-2">add_photo_alternate</span>
                                    Agregar fotografías
                                </h3>
                            </div>

                            {{-- Subir nuevas --}}
                            <div class="card-body">
                                <div class="form-hint mb-3">JPG, JPEG, PNG o WEBP. Máx 8&nbsp;MB por archivo.</div>

                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-lg-8">
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">upload</span></span>
                                            <input type="file" name="fotos[]" accept="image/*" multiple
                                                   class="form-control @error('fotos') is-invalid @enderror @error('fotos.*') is-invalid @enderror">
                                        </div>
                                        @error('fotos')   <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        @error('fotos.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Listado/galería y marcado para borrar --}}
                            <div class="card-body border-top">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <h2 class="h5 mb-0 d-flex align-items-center">
                                            <span class="material-symbols-outlined me-2">photo</span>
                                            Fotografías actuales
                                        </h2
                                        >
                                        <span class="badge bg-secondary-lt">{{ $operador->fotos->count() }} foto(s)</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <button type="button" class="btn btn-dark btn-sm" id="openGalleryAll">
                                            <span class="material-symbols-outlined me-1 align-middle">slideshow</span> Ver galería
                                        </button>
                                        <span class="small text-secondary">
                                            Marcadas para borrar: <strong id="delCount">0</strong>
                                        </span>
                                    </div>
                                </div>

                                @if($operador->fotos->isEmpty())
                                    <div class="empty my-4">
                                        <div class="empty-icon">
                                            <span class="material-symbols-outlined">image_not_supported</span>
                                        </div>
                                        <p class="empty-title">Este operador aún no tiene fotos</p>
                                        <p class="empty-subtitle text-secondary">Puedes subirlas en la sección “Agregar nuevas fotos”.</p>
                                    </div>
                                @else
                                    <div class="row g-3" id="photosGrid" data-photos='@json($photosData)'>
                                        @foreach($operador->fotos as $foto)
                                            <div class="col-6 col-sm-4 col-md-3">
                                                <div class="card position-relative foto-card" data-id="{{ $foto->id }}">
                                                    <div class="ratio ratio-4x3">
                                                        <div class="w-100 h-100 rounded bg-cover"
                                                             style="background-image:url('{{ route('operadores.fotos.show', $foto) }}'); background-size:cover; background-position:center;"></div>
                                                    </div>

                                                    {{-- Botón marcar para borrar (toggle) --}}
                                                    <button type="button"
                                                            class="btn btn-danger btn-icon btn-sm position-absolute top-0 end-0 m-1 toggle-delete z-3"
                                                            data-id="{{ $foto->id }}" title="Marcar para borrar">
                                                        <span class="material-symbols-outlined">delete</span>
                                                    </button>

                                                    {{-- Checkbox oculto que viaja en el form --}}
                                                    <input type="checkbox" class="d-none delete-input"
                                                           id="del-{{ $foto->id }}" name="delete_fotos[]"
                                                           value="{{ $foto->id }}">

                                                    {{-- Cinta visual cuando está marcada --}}
                                                    <div class="position-absolute top-0 start-0 w-100 h-100 rounded bg-danger opacity-25 d-none overlay-del"></div>
                                                    <span class="badge bg-danger position-absolute bottom-0 start-0 m-2 d-none badge-del">
                                                        <span class="material-symbols-outlined me-1 align-middle">delete</span> Se borrará
                                                    </span>

                                                    {{-- Abrir galería (clic en tarjeta) --}}
                                                    <a href="javascript:void(0)" class="stretched-link gal-photo" data-index="{{ $loop->index }}" title="Ver grande"></a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- FOOTER del form: un único botón que guarda TODO --}}
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('operadores.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <span class="material-symbols-outlined me-1 align-middle">save</span> Guardar todo
                            </button>
                        </div>
                    </form>
                </div>

                {{-- LATERAL --}}
                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-body text-center py-4">
                            <span class="avatar avatar-xl avatar-rounded bg-blue-lt mb-3">
                                <span class="material-symbols-outlined" style="font-size:32px; line-height:1;">person</span>
                            </span>
                            <div class="h3 mb-1">{{ $nombreCompleto ?: 'Operador #'.$operador->id }}</div>
                            <div class="text-secondary">{{ $correo }}</div>

                            <div class="mt-3 text-start small text-secondary">
                                @if(optional($operador->created_at)->isValid())
                                    <div>
                                        <span class="material-symbols-outlined me-1 align-middle">calendar_month</span>
                                        Registrado:
                                        <span class="fw-semibold">{{ optional($operador->created_at)->format('Y-m-d') }}</span>
                                    </div>
                                @endif
                                @if(optional($operador->updated_at)->isValid())
                                    <div>
                                        <span class="material-symbols-outlined me-1 align-middle">history</span>
                                        Actualizado:
                                        <span class="fw-semibold">{{ optional($operador->updated_at)->diffForHumans() }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="d-grid gap-2 mt-4">
                            </div>
                        </div>
                    </div>
                </div>
            </div> {{-- /row --}}

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
                        <span class="material-symbols-outlined me-2 align-middle">photo</span>Galería de fotos
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

    {{-- ===== SCRIPTS ===== --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- Galería ---
        const photosGrid = document.getElementById('photosGrid');
        let photos = [];
        try { photos = JSON.parse(photosGrid?.getAttribute('data-photos') || '[]'); } catch { photos = []; }

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

        photosGrid?.addEventListener('click', (e) => {
            const a = e.target.closest('.gal-photo');
            if (!a) return;
            const idx = parseInt(a.getAttribute('data-index') || '0', 10) || 0;
            openGallery(idx);
        });

        document.getElementById('openGalleryAll')?.addEventListener('click', () => openGallery(0));

        // --- Togglear borrado de fotos ---
        const delCountEl = document.getElementById('delCount');
        function refreshDelCount(){
            const checked = document.querySelectorAll('.delete-input:checked').length;
            if (delCountEl) delCountEl.textContent = String(checked);
        }

        photosGrid?.querySelectorAll('.toggle-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const id = btn.getAttribute('data-id');
                const input = document.getElementById('del-' + id);
                const card  = btn.closest('.foto-card');
                const overlay = card?.querySelector('.overlay-del');
                const badge   = card?.querySelector('.badge-del');

                if (!input) return;
                input.checked = !input.checked;

                const marked = input.checked;
                btn.classList.toggle('btn-danger', !marked);
                btn.classList.toggle('btn-warning', marked);
                if (overlay) overlay.classList.toggle('d-none', !marked);
                if (badge)   badge.classList.toggle('d-none', !marked);
                btn.title = marked ? 'Quitar de borrado' : 'Marcar para borrar';

                refreshDelCount();
            });
        });

        refreshDelCount();
    });
    </script>

    <style>
        .bg-cover { background-repeat: no-repeat; }
        /* La capa roja no bloquea clics */
        .overlay-del { pointer-events: none; }
        /* Botón por encima de stretched-link */
        .foto-card .toggle-delete { z-index: 3; }
    </style>
</x-app-layout>
