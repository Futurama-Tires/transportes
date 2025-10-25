{{-- resources/views/operadores/edit.blade.php — Versión Tabler con fotos + Licencias --}}
<x-app-layout>
    {{-- ===== HEADER ===== --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title mb-0 d-flex align-items-center gap-2">
                            Editar Operador
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-flex gap-2">
                        <a href="{{ route('operadores.index') }}" class="btn btn-outline-dark">
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
            {{-- ===== FORM PRINCIPAL (UPDATE) =====
                 Igual que Vehículos: este form envuelve también la galería para enviar los checkboxes de eliminación. --}}
            <form id="operador-form" method="POST" action="{{ route('operadores.update', $operador) }}" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Alertas --}}
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>
                        Revisa los campos marcados y vuelve a intentar.
                    </div>
                @endif
                @error('general')
                    <div class="alert alert-danger" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>{{ $message }}
                    </div>
                @enderror

                <div class="card">
                    <div class="card-header justify-content-between">
                        <h3 class="card-title d-flex align-items-center gap-2 mb-0">
                            <i class="ti ti-id"></i>
                            Datos del operador
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Nombre --}}
                            <div class="col-12 col-md-6">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                    <input id="nombre" type="text" name="nombre" value="{{ old('nombre', $operador->nombre) }}"
                                           class="form-control @error('nombre') is-invalid @enderror" required placeholder="Ej. Juan">
                                </div>
                                @error('nombre') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Apellido paterno --}}
                            <div class="col-12 col-md-6">
                                <label for="apellido_paterno" class="form-label">Apellido paterno <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                    <input id="apellido_paterno" type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $operador->apellido_paterno) }}"
                                           class="form-control @error('apellido_paterno') is-invalid @enderror" required placeholder="Ej. Pérez">
                                </div>
                                @error('apellido_paterno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Apellido materno --}}
                            <div class="col-12 col-md-6">
                                <label for="apellido_materno" class="form-label">Apellido materno</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-user-circle"></i></span>
                                    <input id="apellido_materno" type="text" name="apellido_materno" value="{{ old('apellido_materno', $operador->apellido_materno) }}"
                                           class="form-control @error('apellido_materno') is-invalid @enderror" placeholder="(opcional)">
                                </div>
                                @error('apellido_materno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Email (asociado a user si aplica) --}}
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-mail"></i></span>
                                    <input id="email" type="email" name="email" value="{{ old('email', optional($operador->user)->email) }}"
                                           class="form-control @error('email') is-invalid @enderror" placeholder="usuario@dominio.com">
                                </div>
                                @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Teléfono --}}
                            <div class="col-12 col-md-6">
                                <label for="telefono" class="form-label">Teléfono (10 dígitos)</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-phone"></i></span>
                                    <input id="telefono" type="tel" name="telefono" inputmode="numeric" maxlength="10" pattern="[0-9]{10}"
                                           oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                           value="{{ old('telefono', $operador->telefono) }}"
                                           class="form-control @error('telefono') is-invalid @enderror" placeholder="7771234567">
                                </div>
                                @error('telefono') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Domicilio --}}
                            <div class="col-12">
                                <label for="domicilio" class="form-label">Domicilio</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-home"></i></span>
                                    <input id="domicilio" type="text" name="domicilio" value="{{ old('domicilio', $operador->domicilio) }}"
                                           class="form-control @error('domicilio') is-invalid @enderror" placeholder="Calle, número, colonia, ciudad, estado, C.P.">
                                </div>
                                @error('domicilio') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Estado civil --}}
                            <div class="col-12 col-md-6">
                                <label for="estado_civil" class="form-label">Estado civil</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-users-group"></i></span>
                                    <select id="estado_civil" name="estado_civil" class="form-select @error('estado_civil') is-invalid @enderror">
                                        <option value="">(sin especificar)</option>
                                        @foreach(['soltero'=>'Soltero','casado'=>'Casado','viudo'=>'Viudo','divorciado'=>'Divorciado','union libre'=>'Union libre'] as $val=>$label)
                                            <option value="{{ $val }}" @selected(old('estado_civil', $operador->estado_civil)===$val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('estado_civil') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Tipo de sangre --}}
                            <div class="col-12 col-md-6">
                                <label for="tipo_sangre" class="form-label">Tipo de sangre</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-droplet"></i></span>
                                    <input id="tipo_sangre" type="text" name="tipo_sangre" value="{{ old('tipo_sangre', $operador->tipo_sangre) }}"
                                           class="form-control @error('tipo_sangre') is-invalid @enderror" placeholder="Ej. O+">
                                </div>
                                <div class="form-hint">Formato corto (ej.: O+, A-, AB-).</div>
                                @error('tipo_sangre') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- CURP --}}
                            <div class="col-12 col-md-6">
                                <label for="curp" class="form-label">CURP</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-badge"></i></span>
                                    <input id="curp" type="text" name="curp" value="{{ old('curp', $operador->curp) }}" maxlength="18"
                                           class="form-control @error('curp') is-invalid @enderror"
                                           style="text-transform:uppercase"
                                           oninput="this.value=this.value.toUpperCase().replace(/\s+/g,'');"
                                           pattern="[A-ZÑ0-9]{18}" title="18 caracteres en mayúsculas (A-Z/Ñ/0-9)">
                                </div>
                                @error('curp') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- RFC --}}
                            <div class="col-12 col-md-6">
                                <label for="rfc" class="form-label">RFC</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-id-badge-2"></i></span>
                                    <input id="rfc" type="text" name="rfc" value="{{ old('rfc', $operador->rfc) }}" maxlength="13"
                                           class="form-control @error('rfc') is-invalid @enderror"
                                           style="text-transform:uppercase"
                                           oninput="this.value=this.value.toUpperCase().replace(/\s+/g,'');"
                                           pattern="([A-ZÑ&]{3,4})\d{6}[A-Z0-9]{3}"
                                           title="3-4 letras + 6 dígitos de fecha + 3 alfanum.">
                                </div>
                                @error('rfc') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Contacto de emergencia: nombre --}}
                            <div class="col-12 col-md-6">
                                <label for="contacto_emergencia_nombre" class="form-label">Contacto de emergencia (nombre)</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-user-heart"></i></span>
                                    <input id="contacto_emergencia_nombre" type="text" name="contacto_emergencia_nombre" value="{{ old('contacto_emergencia_nombre', $operador->contacto_emergencia_nombre) }}"
                                           class="form-control @error('contacto_emergencia_nombre') is-invalid @enderror" placeholder="Ej. Juan Pérez">
                                </div>
                                @error('contacto_emergencia_nombre') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Parentesco --}}
                            <div class="col-12 col-md-6">
                                <label for="contacto_emergencia_parentesco" class="form-label">Parentesco</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-heart"></i></span>
                                    <input id="contacto_emergencia_parentesco" type="text" name="contacto_emergencia_parentesco"
                                           value="{{ old('contacto_emergencia_parentesco', $operador->contacto_emergencia_parentesco) }}"
                                           class="form-control @error('contacto_emergencia_parentesco') is-invalid @enderror" placeholder="Esposa, Hermano, Amigo">
                                </div>
                                @error('contacto_emergencia_parentesco') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Teléfono emergencia --}}
                            <div class="col-12 col-md-6">
                                <label for="contacto_emergencia_tel" class="form-label">Teléfono de emergencia (10 dígitos)</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-phone-call"></i></span>
                                    <input id="contacto_emergencia_tel" type="tel" name="contacto_emergencia_tel" inputmode="numeric" maxlength="10" pattern="[0-9]{10}"
                                           oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                           value="{{ old('contacto_emergencia_tel', $operador->contacto_emergencia_tel) }}"
                                           class="form-control @error('contacto_emergencia_tel') is-invalid @enderror" placeholder="7770000000">
                                </div>
                                @error('contacto_emergencia_tel') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Ubicación emergencia --}}
                            <div class="col-12 col-md-6">
                                <label for="contacto_emergencia_ubicacion" class="form-label">Ubicación del contacto</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-map-pin"></i></span>
                                    <input id="contacto_emergencia_ubicacion" type="text" name="contacto_emergencia_ubicacion"
                                           value="{{ old('contacto_emergencia_ubicacion', $operador->contacto_emergencia_ubicacion) }}"
                                           class="form-control @error('contacto_emergencia_ubicacion') is-invalid @enderror" placeholder="Cuernavaca, Morelos">
                                </div>
                                @error('contacto_emergencia_ubicacion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Subir nuevas fotos --}}
                    <div class="card-body border-top">
                        <h3 class="card-title d-flex align-items-center gap-2">
                            <i class="ti ti-photo-plus"></i>
                            Agregar fotografías
                        </h3>
                        <div class="form-hint mb-2">Máx 8MB c/u.</div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-upload"></i></span>
                            <input type="file" name="fotos[]" accept="image/*" multiple class="form-control @error('fotos') is-invalid @enderror @error('fotos.*') is-invalid @enderror">
                        </div>
                        @error('fotos')   <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @error('fotos.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- ===== FOTOS ACTUALES (DENTRO DEL MISMO FORM) ===== --}}
                    <div class="card-body border-top">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h3 class="card-title d-flex align-items-center gap-2 mb-0">
                                <i class="ti ti-photo"></i>
                                Fotografías actuales
                            </h3>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary-lt">
                                    {{ $operador->fotos->count() }} foto(s)
                                </span>
                                <span id="marcadasBadge" class="badge bg-red-lt d-none">
                                    0 seleccionadas
                                </span>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnSelectAll">
                                        <i class="ti ti-checkbox me-1"></i>Seleccionar todo
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearAll">
                                        <i class="ti ti-square-off me-1"></i>Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if($operador->fotos->isEmpty())
                            <div class="empty">
                                <div class="empty-icon"><i class="ti ti-photo-off"></i></div>
                                <p class="empty-title">Este operador aún no tiene fotografías</p>
                            </div>
                        @else
                            @php
                                $oldEliminar = collect(old('fotos_eliminar', []))->map(fn($v)=> (int)$v)->all();
                            @endphp

                            <div class="row g-2">
                                @foreach($operador->fotos as $foto)
                                    @php $checked = in_array($foto->id, $oldEliminar, true); @endphp
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <div class="card photo-card h-100 position-relative {{ $checked ? 'is-selected' : '' }}">
                                            <a href="{{ route('operadores.fotos.show', $foto) }}"
                                               target="_blank" rel="noopener noreferrer" title="Abrir en nueva pestaña" class="d-block">
                                                <div class="img-responsive img-responsive-4x3 card-img-top"
                                                     style="background-image: url('{{ route('operadores.fotos.show', $foto) }}')"></div>
                                            </a>

                                            <div class="card-body py-2">
                                                <label class="form-check m-0 w-100 d-flex align-items-center gap-2">
                                                    <input class="form-check-input foto-check" type="checkbox"
                                                           name="fotos_eliminar[]"
                                                           value="{{ $foto->id }}"
                                                           @checked($checked)
                                                           data-photo-card
                                                    >
                                                    <span class="form-check-label small text-danger fw-semibold">
                                                        Marcar para eliminar al guardar
                                                    </span>
                                                </label>
                                            </div>

                                            {{-- Overlay de selección (visual) --}}
                                            <div class="photo-overlay-check">
                                                <i class="ti ti-check"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-2 text-secondary small d-none d-md-block">
                                <i class="ti ti-info-circle me-1"></i>
                                Tip: puedes abrir la imagen en una nueva pestaña y marcar la casilla para eliminarla. Todo se aplica al presionar <b>Guardar cambios</b>.
                            </div>
                        @endif
                    </div>

                    {{-- Footer acciones --}}
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ url()->previous() ?: route('operadores.index') }}" class="btn btn-outline-dark">
                            <i class="ti ti-x me-1"></i> Cancelar
                        </a>
                        <button type="submit" form="operador-form" class="btn btn-danger">
                            <i class="ti ti-device-floppy me-1"></i> Guardar cambios
                        </button>
                    </div>
                </div>
            </form>

            {{-- ===== LICENCIAS DEL OPERADOR ===== --}}
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="ti ti-badge"></i> Licencias de conducir
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('licencias.create', ['operador_id' => $operador->id]) }}"
                           class="btn btn-outline-danger btn-sm">
                            <i class="ti ti-plus me-1"></i>Agregar licencia
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
                                                            <a href="{{ route('licencias.archivos.inline', $a) }}"
                                                               class="btn btn-outline-dark btn-xs"
                                                               target="_blank" rel="noopener"
                                                               title="{{ $a->nombre_original }}">
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('licencias.edit', $l) }}" class="btn btn-outline-dark btn-sm">
                                                    <i class="ti ti-edit me-1"></i>Editar
                                                </a>
                                                <form action="{{ route('licencias.destroy', $l) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Eliminar la licencia seleccionada? Esta acción borrará sus archivos.');">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm">
                                                        <i class="ti ti-trash me-1"></i>Eliminar
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

    <style>
        /* Foto-card igual que Vehículos */
        .photo-card { transition: transform .12s ease, box-shadow .12s ease; }
        .photo-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.08); }
        .photo-card.is-selected { outline: 2px solid var(--tblr-red, #f03e3e); outline-offset: 0; }
        .photo-overlay-check {
            position: absolute; inset: .5rem .5rem auto auto;
            width: 1.75rem; height: 1.75rem; border-radius: 50%;
            background: rgba(240,62,62,.95); color: #fff; display: none;
            align-items: center; justify-content: center; font-size: 1rem; z-index: 2;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }
        .photo-card.is-selected .photo-overlay-check { display: flex; }
    </style>

    <script>
        (function () {
            const checks = Array.from(document.querySelectorAll('.foto-check'));
            const badge  = document.getElementById('marcadasBadge');
            const btnAll = document.getElementById('btnSelectAll');
            const btnClr = document.getElementById('btnClearAll');

            function syncCardState(input) {
                const card = input.closest('.photo-card');
                if (!card) return;
                card.classList.toggle('is-selected', input.checked);
            }

            function refreshBadge() {
                const total = checks.filter(ch => ch.checked).length;
                if (total > 0) {
                    badge.textContent = `${total} seleccionadas`;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }
            }

            checks.forEach(ch => {
                syncCardState(ch);
                ch.addEventListener('change', () => {
                    syncCardState(ch);
                    refreshBadge();
                });
            });

            btnAll?.addEventListener('click', () => {
                checks.forEach(ch => ch.checked = true);
                checks.forEach(syncCardState);
                refreshBadge();
            });
            btnClr?.addEventListener('click', () => {
                checks.forEach(ch => ch.checked = false);
                checks.forEach(syncCardState);
                refreshBadge();
            });

            refreshBadge();
        })();
    </script>
</x-app-layout>
