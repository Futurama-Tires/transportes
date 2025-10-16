{{-- resources/views/operadores/edit.blade.php — campos ordenados lógicamente + fotos (clic abre en nueva pestaña) --}}
<x-app-layout>
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
                    <ul class="mt-2 mb-0 ps-4">
                        @foreach ($errors->all() as $error)
                            <li class="small">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $nombreCompleto = trim(($operador->nombre ?? '').' '.($operador->apellido_paterno ?? '').' '.($operador->apellido_materno ?? ''));
                $correo = optional($operador->user)->email ?? '—';
            @endphp

            {{-- ===== FORM ÚNICO (abarca ambas columnas) ===== --}}
            <form id="operador-form" method="POST"
                  action="{{ route('operadores.update', $operador) }}"
                  enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- ===== COLUMNA IZQUIERDA: DATOS ===== --}}
                    <div class="col-12 col-xl-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0 d-flex align-items-center gap-2">
                                    <span class="material-symbols-outlined">badge</span> Datos del operador
                                </h3>
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
                                        </div>
                                    </div>

                                    {{-- ================= 2) CONTACTO BÁSICO ================= --}}
                                    <div class="col-12">
                                        <div class="text-secondary text-uppercase fw-semibold small mt-2 mb-2">Contacto básico</div>
                                        <div class="row g-3">
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
                                                <div id="email_help" class="form-hint">Usa un correo válido al que el operador tenga acceso.</div>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="telefono" class="form-label">Teléfono (10 dígitos)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">call</span></span>
                                                    <input id="telefono" name="telefono" type="tel"
                                                           inputmode="numeric" maxlength="10" pattern="[0-9]{10}"
                                                           oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                                           class="form-control @error('telefono') is-invalid @enderror"
                                                           value="{{ old('telefono', $operador->telefono) }}"
                                                           placeholder="Ej. 7771234567" autocomplete="tel"
                                                           title="Ingresa exactamente 10 dígitos (solo números)">
                                                    @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>

                                            {{-- ===== NUEVO: DOMICILIO ===== --}}
                                            <div class="col-12">
                                                <label for="domicilio" class="form-label">Domicilio</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><span class="material-symbols-outlined">home_pin</span></span>
                                                    <input id="domicilio" name="domicilio" type="text"
                                                           class="form-control @error('domicilio') is-invalid @enderror"
                                                           value="{{ old('domicilio', $operador->domicilio) }}"
                                                           placeholder="Calle, número, colonia, ciudad, estado, C.P.">
                                                    @error('domicilio') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                                        @foreach(['soltero'=>'Soltero','casado'=>'Casado','viudo'=>'Viudo','divorciado'=>'Divorciado','union libre'=>'Union libre'] as $val=>$label)
                                                            <option value="{{ $val }}" {{ old('estado_civil', $operador->estado_civil)===$val ? 'selected' : '' }}>{{ $label }}</option>
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
                                                           value="{{ old('tipo_sangre', $operador->tipo_sangre) }}"
                                                           placeholder="Ej. O+, A-, B+" maxlength="5">
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
                                                           value="{{ old('curp', $operador->curp) }}" maxlength="18"
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
                                                           value="{{ old('rfc', $operador->rfc) }}" maxlength="13"
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
                           value="{{ old('contacto_emergencia_nombre', $operador->contacto_emergencia_nombre) }}"
                           placeholder="Ej. Juan Pérez">
                    @error('contacto_emergencia_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <label for="contacto_emergencia_parentesco" class="form-label">Parentesco</label>
                <div class="input-group">
                    <span class="input-group-text"><span class="material-symbols-outlined">diversity_1</span></span>
                    <input id="contacto_emergencia_parentesco" name="contacto_emergencia_parentesco" type="text"
                           class="form-control @error('contacto_emergencia_parentesco') is-invalid @enderror"
                           value="{{ old('contacto_emergencia_parentesco', $operador->contacto_emergencia_parentesco) }}" placeholder="Ej. Esposa, Hermano, Amigo">
                    @error('contacto_emergencia_parentesco') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <label for="contacto_emergencia_tel" class="form-label">Teléfono de emergencia (10 dígitos)</label>
                <div class="input-group">
                    <span class="input-group-text"><span class="material-symbols-outlined">call</span></span>
                    <input id="contacto_emergencia_tel" name="contacto_emergencia_tel" type="tel"
                           inputmode="numeric" maxlength="10" pattern="[0-9]{10}"
                           oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                           class="form-control @error('contacto_emergencia_tel') is-invalid @enderror"
                           value="{{ old('contacto_emergencia_tel', $operador->contacto_emergencia_tel) }}"
                           placeholder="Ej. 7770000000"
                           title="Ingresa exactamente 10 dígitos (solo números)">
                    @error('contacto_emergencia_tel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="col-12 col-md-6">
                <label for="contacto_emergencia_ubicacion" class="form-label">Ubicación</label>
                <div class="input-group">
                    <span class="input-group-text"><span class="material-symbols-outlined">place</span></span>
                    <input id="contacto_emergencia_ubicacion" name="contacto_emergencia_ubicacion" type="text"
                           class="form-control @error('contacto_emergencia_ubicacion') is-invalid @enderror"
                           value="{{ old('contacto_emergencia_ubicacion', $operador->contacto_emergencia_ubicacion) }}" placeholder="Ej. Cuernavaca, Morelos">
                    @error('contacto_emergencia_ubicacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

                                </div> {{-- /row --}}
                            </div>
                        </div>
                    </div>

                    {{-- ===== COLUMNA DERECHA: AVATAR + FOTOS ===== --}}
                    <div class="col-12 col-xl-4">

                        {{-- FOTOS: subir + marcado borrar + lista (clic abre en nueva pestaña) --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0 d-flex align-items-center">
                                    <span class="material-symbols-outlined me-2">add_photo_alternate</span>
                                    Agregar fotografías
                                </h3>
                            </div>

                            {{-- Subir nuevas --}}
                            <div class="card-body">
                                <div class="form-hint mb-3">Máx 8&nbsp;MB por archivo.</div>

                                <div class="row g-3 align-items-end">
                                    <div class="col-12">
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

                            {{-- Listado/galería simple y marcado para borrar --}}
                            <div class="card-body border-top">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <h2 class="h5 mb-0 d-flex align-items-center">
                                            <span class="material-symbols-outlined me-2">photo</span>
                                            Fotografías actuales
                                        </h2>
                                        <span class="badge bg-secondary-lt">{{ $operador->fotos->count() }} foto(s)</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="small text-secondary">
                                            Marcadas: <strong id="delCount">0</strong>
                                        </span>
                                    </div>
                                </div>

                                @if($operador->fotos->isEmpty())
                                    <div class="empty my-4">
                                        <div class="empty-icon">
                                            <span class="material-symbols-outlined">image_not_supported</span>
                                        </div>
                                        <p class="empty-title">Este operador aún no tiene fotos</p>
                                    </div>
                                @else
                                    <div class="row g-3" id="photosGrid">
                                        @foreach($operador->fotos as $foto)
                                            <div class="col-12">
                                                <div class="card position-relative foto-card" data-id="{{ $foto->id }}">
                                                    <div class="ratio ratio-4x3">
                                                        <a href="{{ route('operadores.fotos.show', $foto) }}"
                                                           target="_blank" rel="noopener noreferrer"
                                                           title="Abrir en nueva pestaña"
                                                           class="stretched-link"></a>
                                                        <img src="{{ route('operadores.fotos.show', $foto) }}"
                                                             alt="Foto {{ $loop->iteration }} de {{ $nombreCompleto ?: ('Operador #'.$operador->id) }}"
                                                             class="w-100 h-100 rounded object-fit-cover">
                                                    </div>

                                                    <button type="button"
                                                            class="btn btn-danger btn-icon btn-sm position-absolute top-0 end-0 m-1 toggle-delete z-3"
                                                            data-id="{{ $foto->id }}" title="Marcar para borrar">
                                                        <span class="material-symbols-outlined">delete</span>
                                                    </button>

                                                    <input type="checkbox" class="d-none delete-input"
                                                           id="del-{{ $foto->id }}" name="delete_fotos[]" value="{{ $foto->id }}">

                                                    <div class="position-absolute top-0 start-0 w-100 h-100 rounded bg-danger opacity-25 d-none overlay-del"></div>
                                                    <span class="badge bg-danger position-absolute bottom-0 start-0 m-2 d-none badge-del">
                                                        <span class="material-symbols-outlined me-1 align-middle">delete</span> Se borrará
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER del form: un único botón que guarda TODO --}}
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('operadores.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <span class="material-symbols-outlined me-1 align-middle">save</span> Guardar todo
                            </button>
                        </div>
                    </div>
                </div> {{-- /row --}}
            </form>

            {{-- ===== LICENCIAS DEL OPERADOR ===== --}}
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">badge</span> Licencias de conducir
                    </h3>
                    <div>
                        <a href="{{ route('licencias.create', ['operador_id' => $operador->id]) }}"
                           class="btn btn-primary btn-sm">
                            <span class="material-symbols-outlined me-1 align-middle">add</span>Agregar licencia
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    @php $lics = $operador->licencias()->with('archivos')->orderByDesc('fecha_vencimiento')->get(); @endphp

                    @if($lics->isEmpty())
                        <div class="p-4 text-secondary">Este operador no tiene licencias registradas.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-striped table-hover mb-0">
                                <thead>
                                    <tr class="text-uppercase text-secondary small">
                                        <th>Ámbito</th>
                                        <th>Tipo</th>
                                        <th>Folio</th>
                                        <th>Expedición</th>
                                        <th>Vencimiento</th>
                                        <th>Estatus</th>
                                        <th>Archivos</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lics as $l)
                                        @php $status = $l->estatus; @endphp
                                        <tr>
                                            <td class="text-capitalize">{{ $l->ambito ?: '—' }}</td>
                                            <td>{{ $l->tipo ?: '—' }}</td>
                                            <td>{{ $l->folio ?: '—' }}</td>
                                            <td>{{ optional($l->fecha_expedicion)->format('Y-m-d') ?: '—' }}</td>
                                            <td>{{ optional($l->fecha_vencimiento)->format('Y-m-d') ?: '—' }}</td>
                                            <td>
                                                @if($status==='vigente')
                                                    <span class="badge bg-success">Vigente</span>
                                                @elseif($status==='por_vencer')
                                                    <span class="badge bg-warning text-dark">Por vencer</span>
                                                @elseif($status==='vencida')
                                                    <span class="badge bg-danger">Vencida</span>
                                                @else
                                                    <span class="badge bg-secondary">N/D</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($l->archivos->isEmpty())
                                                    <span class="text-secondary">0</span>
                                                @else
                                                    <div class="d-flex gap-1 flex-wrap">
                                                        @foreach($l->archivos as $a)
                                                            <a href="{{ route('licencias.archivos.inline', $a) }}" class="btn btn-outline-secondary btn-xs" target="_blank" rel="noopener" title="{{ $a->nombre_original }}">
                                                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('licencias.edit', $l) }}" class="btn btn-outline-secondary btn-sm">
                                                    <span class="material-symbols-outlined me-1 align-middle">edit</span>Editar
                                                </a>
                                                <form action="{{ route('licencias.destroy', $l) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Eliminar la licencia seleccionada? Esta acción borrará sus archivos.');">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm">
                                                        <span class="material-symbols-outlined me-1 align-middle">delete</span>Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    {{-- ===== SCRIPTS ===== --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- Togglear borrado de fotos ---
        const delCountEl = document.getElementById('delCount');
        function refreshDelCount(){
            const checked = document.querySelectorAll('.delete-input:checked').length;
            if (delCountEl) delCountEl.textContent = String(checked);
        }

        document.getElementById('photosGrid')?.querySelectorAll('.toggle-delete').forEach(btn => {
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
        .overlay-del { pointer-events: none; }
        .foto-card .toggle-delete { z-index: 3; }
        .object-fit-cover { object-fit: cover; }
    </style>
</x-app-layout>
