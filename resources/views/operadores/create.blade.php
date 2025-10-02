{{-- resources/views/operadores/create.blade.php — campos ordenados lógicamente y accesibles --}}
<x-app-layout>
    @vite(['resources/js/app.js'])

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <p class="text-secondary text-uppercase small mb-1">Operadores</p>
                        <h2 class="page-title mb-0">Registrar un nuevo Operador</h2>
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

            {{-- FLASH ÉXITO --}}
            @if(session('success'))
                <div class="alert alert-success mb-4" role="alert">
                    <span class="material-symbols-outlined me-2 align-middle">check_circle</span>{{ session('success') }}
                </div>
            @endif

            {{-- ERRORES --}}
            @if ($errors->any())
                <div class="alert alert-danger mb-4" role="alert">
                    <span class="material-symbols-outlined me-2 align-middle">warning</span>Revisa los campos marcados y vuelve a intentar.
                    @if($errors->count() > 0)
                        <ul class="mt-2 mb-0 ps-4">
                            @foreach ($errors->all() as $error)
                                <li class="small">{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            {{-- ===== FORM ÚNICO (abarca ambas columnas) ===== --}}
            <form id="operador-form" method="POST" action="{{ route('operadores.store') }}" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="row g-4">
                    {{-- ===== COLUMNA IZQUIERDA: DATOS ===== --}}
                    <div class="col-12 col-xl-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0 d-flex align-items-center gap-2">
                                    <span class="material-symbols-outlined">badge</span> Datos del operador
                                </h3>
                                <div class="card-subtitle">&nbsp;Completa la información requerida.</div>
                            </div>

                            <div class="card-body pt-3">
                                <div class="row g-4">

                                    {{-- ================= 1) IDENTIDAD ================= --}}
                                    <div class="col-12">
                                        <div class="text-secondary text-uppercase fw-semibold small mb-2">Identidad</div>
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">person</span></span>
                                                    <input id="nombre" name="nombre" type="text" autocomplete="given-name"
                                                           class="form-control @error('nombre') is-invalid @enderror"
                                                           value="{{ old('nombre') }}" required placeholder="Ej. Juan">
                                                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="apellido_paterno" class="form-label">Apellido paterno <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">person</span></span>
                                                    <input id="apellido_paterno" name="apellido_paterno" type="text" autocomplete="family-name"
                                                           class="form-control @error('apellido_paterno') is-invalid @enderror"
                                                           value="{{ old('apellido_paterno') }}" required placeholder="Ej. Pérez">
                                                    @error('apellido_paterno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="apellido_materno" class="form-label">Apellido materno</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">account_circle</span></span>
                                                    <input id="apellido_materno" name="apellido_materno" type="text" autocomplete="additional-name"
                                                           class="form-control @error('apellido_materno') is-invalid @enderror"
                                                           value="{{ old('apellido_materno') }}" placeholder="(opcional)">
                                                    @error('apellido_materno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ================= 2) CONTACTO BÁSICO ================= --}}
                                    <div class="col-12">
                                        <div class="text-secondary text-uppercase fw-semibold small mt-2 mb-2">Contacto básico</div>
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">mail</span></span>
                                                    <input id="email" name="email" type="email" autocomplete="email"
                                                           class="form-control @error('email') is-invalid @enderror"
                                                           value="{{ old('email') }}" required
                                                           placeholder="usuario@dominio.com" aria-describedby="email_help">
                                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div id="email_help" class="form-hint">Usa un correo válido al que el operador tenga acceso.</div>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">call</span></span>
                                                    <input id="telefono" name="telefono" type="tel"
                                                           inputmode="tel" autocomplete="tel"
                                                           class="form-control @error('telefono') is-invalid @enderror"
                                                           value="{{ old('telefono') }}" placeholder="+52 777 123 4567">
                                                    @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ================= 3) DATOS PERSONALES ================= --}}
                                    <div class="col-12">
                                        <div class="text-secondary text-uppercase fw-semibold small mt-2 mb-2">Datos personales</div>
                                        <div class="row g-3">
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
                                                            <option value="{{ $val }}" {{ old('estado_civil')===$val ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('estado_civil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="tipo_sangre" class="form-label">Tipo de sangre</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">bloodtype</span></span>
                                                    <input id="tipo_sangre" name="tipo_sangre" type="text"
                                                           class="form-control @error('tipo_sangre') is-invalid @enderror"
                                                           value="{{ old('tipo_sangre') }}" placeholder="Ej. O+, A-, B+" maxlength="5">
                                                    @error('tipo_sangre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="form-hint">Formato corto (ej.: O+, A-, AB-).</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ================= 4) IDENTIFICADORES OFICIALES ================= --}}
                                    <div class="col-12">
                                        <div class="text-secondary text-uppercase fw-semibold small mt-2 mb-2">Identificadores oficiales</div>
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <label for="curp" class="form-label">CURP</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">badge</span></span>
                                                    <input id="curp" name="curp" type="text"
                                                           class="form-control @error('curp') is-invalid @enderror"
                                                           value="{{ old('curp') }}" maxlength="18"
                                                           style="text-transform:uppercase"
                                                           oninput="this.value=this.value.toUpperCase().replace(/\s+/g,'');"
                                                           pattern="[A-ZÑ0-9]{18}"
                                                           title="18 caracteres en mayúsculas (A-Z/Ñ/0-9)">
                                                    @error('curp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="rfc" class="form-label">RFC</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">verified</span></span>
                                                    <input id="rfc" name="rfc" type="text"
                                                           class="form-control @error('rfc') is-invalid @enderror"
                                                           value="{{ old('rfc') }}" maxlength="13"
                                                           style="text-transform:uppercase"
                                                           oninput="this.value=this.value.toUpperCase().replace(/\s+/g,'');"
                                                           pattern="([A-ZÑ&]{3,4})\d{6}[A-Z0-9]{3}"
                                                           title="3-4 letras + 6 dígitos de fecha + 3 alfanum.">
                                                    @error('rfc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ================= 5) CONTACTO DE EMERGENCIA ================= --}}
                                    <div class="col-12">
                                        <div class="text-secondary text-uppercase fw-semibold small mt-2 mb-2">Contacto de emergencia</div>
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <label for="contacto_emergencia_nombre" class="form-label">Nombre</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">contact_emergency</span></span>
                                                    <input id="contacto_emergencia_nombre" name="contacto_emergencia_nombre" type="text"
                                                           class="form-control @error('contacto_emergencia_nombre') is-invalid @enderror"
                                                           value="{{ old('contacto_emergencia_nombre') }}" placeholder="Ej. Juan Pérez">
                                                    @error('contacto_emergencia_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="contacto_emergencia_parentesco" class="form-label">Parentesco</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">diversity_1</span></span>
                                                    <input id="contacto_emergencia_parentesco" name="contacto_emergencia_parentesco" type="text"
                                                           class="form-control @error('contacto_emergencia_parentesco') is-invalid @enderror"
                                                           value="{{ old('contacto_emergencia_parentesco') }}" placeholder="Ej. Esposa, Hermano, Amigo">
                                                    @error('contacto_emergencia_parentesco') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="contacto_emergencia_tel" class="form-label">Teléfono</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">call</span></span>
                                                    <input id="contacto_emergencia_tel" name="contacto_emergencia_tel" type="tel"
                                                           class="form-control @error('contacto_emergencia_tel') is-invalid @enderror"
                                                           value="{{ old('contacto_emergencia_tel') }}" placeholder="+52 777 000 0000">
                                                    @error('contacto_emergencia_tel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="contacto_emergencia_ubicacion" class="form-label">Ubicación</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">place</span></span>
                                                    <input id="contacto_emergencia_ubicacion" name="contacto_emergencia_ubicacion" type="text"
                                                           class="form-control @error('contacto_emergencia_ubicacion') is-invalid @enderror"
                                                           value="{{ old('contacto_emergencia_ubicacion') }}" placeholder="Ej. Cuernavaca, Morelos">
                                                    @error('contacto_emergencia_ubicacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="form-hint">Puedes capturar ciudad/estado o una dirección breve.</div>
                                            </div>
                                        </div>
                                    </div>

                                </div> {{-- /row --}}
                            </div>
                        </div>
                    </div>

                    {{-- ===== COLUMNA DERECHA: AVATAR + FOTOS ===== --}}
                    <div class="col-12 col-xl-4">
                        {{-- Tarjeta de avatar/info --}}
                        <div class="card mb-4">
                            <div class="card-body text-center py-4">
                                <span class="avatar avatar-xl avatar-rounded bg-blue-lt mb-3 d-inline-flex align-items-center justify-content-center">
                                    <span class="material-symbols-outlined" style="font-size:32px; line-height:1;">person</span>
                                </span>
                                <div class="h3 mb-1">Nuevo operador</div>
                                <div class="text-secondary">Se creará una cuenta asociada.</div>
                            </div>
                        </div>

                        {{-- FOTOS: subir + previsualizar --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0 d-flex align-items-center gap-2">
                                    <span class="material-symbols-outlined">add_photo_alternate</span> Agregar fotografías
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="form-hint mb-3">Máx 5&nbsp;MB por archivo. Máx 12 archivos.</div>

                                <div class="row g-3 align-items-end">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><span class="material-symbols-outlined">upload</span></span>
                                            <input type="file" id="fotos" name="fotos[]" accept="image/*" multiple
                                                   class="form-control @error('fotos') is-invalid @enderror @error('fotos.*') is-invalid @enderror"
                                                   aria-describedby="fotos_help">
                                        </div>
                                        @error('fotos')   <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        @error('fotos.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                {{-- Controles y contador --}}
                                <div class="d-flex justify-content-between align-items-center my-3">
                                    <div class="text-secondary small">
                                        <span id="fotos-count">0</span> seleccionada(s)
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" id="btn-clear-fotos" class="btn btn-outline-secondary btn-sm" disabled>
                                            <span class="material-symbols-outlined align-middle me-1">delete</span>Quitar todas
                                        </button>
                                    </div>
                                </div>

                                {{-- Grid de vista previa --}}
                                <div id="fotos-grid" class="row g-2"></div>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER del form: un único botón que guarda TODO --}}
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('operadores.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <span class="material-symbols-outlined me-1 align-middle">person_add</span> Guardar operador
                            </button>
                        </div>
                    </div>
                </div> {{-- /row --}}
            </form>

            {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    {{-- MODAL DE CREDENCIALES (se muestra si viene en sesión) --}}
    @if(session('created') && session('email') && session('password'))
        <div class="modal fade" id="createdModal" tabindex="-1" aria-labelledby="createdModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title h4" id="createdModalLabel">
                            <span class="material-symbols-outlined me-2 text-success align-middle">check_circle</span>Operador creado exitosamente
                        </h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Guarda estas credenciales de acceso:</p>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold">Correo:</span>
                                    <code class="select-all">{{ session('email') }}</code>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Contraseña:</span>
                                    <code id="gen-pass" class="select-all">{{ session('password') }}</code>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                                onclick="navigator.clipboard.writeText(document.getElementById('gen-pass').innerText)">
                            <span class="material-symbols-outlined me-1 align-middle">content_copy</span>Copiar contraseña
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">Aceptar</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Auto mostrar el modal al cargar --}}
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                try {
                    var modalEl = document.getElementById('createdModal');
                    if (modalEl) {
                        var modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                } catch (e) {}
            });
        </script>
    @endif

    {{-- =============== JS: manejo de selección y previsualización de fotos =============== --}}
    <script>
        (function () {
            const MAX_FILES = 12;
            const MAX_SIZE_MB = 5;
            const MAX_SIZE = MAX_SIZE_MB * 1024 * 1024;

            const input = document.getElementById('fotos');
            const grid  = document.getElementById('fotos-grid');
            const count = document.getElementById('fotos-count');
            const btnClear = document.getElementById('btn-clear-fotos');

            /** Lista viva de archivos seleccionados (mutable) */
            let selectedFiles = [];

            function updateInputFiles() {
                const dt = new DataTransfer();
                selectedFiles.forEach(f => dt.items.add(f));
                input.files = dt.files;
                count.textContent = selectedFiles.length.toString();
                btnClear.disabled = selectedFiles.length === 0;
            }

            function renderPreviews() {
                grid.innerHTML = '';
                selectedFiles.forEach((file, idx) => {
                    const url = URL.createObjectURL(file);

                    const col = document.createElement('div');
                    col.className = 'col-6 col-md-6 col-xl-12'; // mejor ajuste en columna derecha angosta

                    const card = document.createElement('div');
                    card.className = 'card card-sm';

                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = file.name;
                    img.className = 'card-img-top img-fluid';
                    img.onload = () => URL.revokeObjectURL(url);

                    const body = document.createElement('div');
                    body.className = 'card-body p-2';

                    const meta = document.createElement('div');
                    meta.className = 'small text-truncate mb-1';
                    meta.title = file.name;
                    meta.textContent = file.name;

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-outline-danger btn-sm w-100';
                    btn.innerHTML = '<span class="material-symbols-outlined align-middle me-1">delete</span>Quitar';
                    btn.addEventListener('click', () => {
                        selectedFiles.splice(idx, 1);
                        updateInputFiles();
                        renderPreviews();
                    });

                    body.appendChild(meta);
                    body.appendChild(btn);
                    card.appendChild(img);
                    card.appendChild(body);
                    col.appendChild(card);
                    grid.appendChild(col);
                });
            }

            function addFiles(files) {
                const incoming = Array.from(files || []);
                const accepted = [];

                for (const f of incoming) {
                    const typeOk = /^image\//i.test(f.type);
                    const sizeOk = f.size <= MAX_SIZE;

                    if (!typeOk || !sizeOk) { continue; }

                    const dup = selectedFiles.some(sf => sf.name === f.name && sf.size === f.size && sf.lastModified === f.lastModified);
                    if (dup) { continue; }

                    accepted.push(f);
                }

                const availableSlots = MAX_FILES - selectedFiles.length;
                if (availableSlots <= 0) return;

                selectedFiles = selectedFiles.concat(accepted.slice(0, availableSlots));
                updateInputFiles();
                renderPreviews();
            }

            input?.addEventListener('change', (e) => {
                const el = e.currentTarget;
                const files = el && el.files ? Array.from(el.files) : [];
                el.value = '';
                addFiles(files);
            });

            btnClear?.addEventListener('click', () => {
                selectedFiles = [];
                updateInputFiles();
                renderPreviews();
            });

            // Soporte drag & drop al grid
            grid?.addEventListener('dragover', (e) => { e.preventDefault(); grid.classList.add('border', 'border-primary'); });
            grid?.addEventListener('dragleave', () => grid.classList.remove('border', 'border-primary'));
            grid?.addEventListener('drop', (e) => {
                e.preventDefault();
                grid.classList.remove('border', 'border-primary');
                addFiles(e.dataTransfer.files);
            });
        })();
    </script>
</x-app-layout>
