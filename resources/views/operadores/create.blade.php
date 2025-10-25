{{-- resources/views/operadores/create.blade.php  --}}
<x-app-layout>
    {{-- ===== HEADER ===== --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title mb-0 d-flex align-items-center gap-2">
                            Agregar Operador
                        </h2>
                    </div>
                    <div class="col-auto ms-auto">
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
            <form method="POST" action="{{ route('operadores.store') }}" novalidate enctype="multipart/form-data">
                @csrf

                {{-- Alertas --}}
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>
                        Revisa los campos marcados y vuelve a intentar.
                    </div>
                @endif

                {{-- ===== Card: Datos del operador ===== --}}
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
                                    <input id="nombre" type="text" name="nombre" value="{{ old('nombre') }}"
                                           class="form-control @error('nombre') is-invalid @enderror" required placeholder="Ej. Juan">
                                </div>
                                @error('nombre') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Apellido paterno --}}
                            <div class="col-12 col-md-6">
                                <label for="apellido_paterno" class="form-label">Apellido paterno <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-user"></i></span>
                                    <input id="apellido_paterno" type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}"
                                           class="form-control @error('apellido_paterno') is-invalid @enderror" required placeholder="Ej. Pérez">
                                </div>
                                @error('apellido_paterno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Apellido materno --}}
                            <div class="col-12 col-md-6">
                                <label for="apellido_materno" class="form-label">Apellido materno</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-user-circle"></i></span>
                                    <input id="apellido_materno" type="text" name="apellido_materno" value="{{ old('apellido_materno') }}"
                                           class="form-control @error('apellido_materno') is-invalid @enderror" placeholder="(opcional)">
                                </div>
                                @error('apellido_materno') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label">Correo electrónico @* requerido si así lo manejas *@</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-mail"></i></span>
                                    <input id="email" type="email" name="email" value="{{ old('email') }}"
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
                                           value="{{ old('telefono') }}" class="form-control @error('telefono') is-invalid @enderror" placeholder="7771234567">
                                </div>
                                @error('telefono') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Domicilio --}}
                            <div class="col-12">
                                <label for="domicilio" class="form-label">Domicilio</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-home"></i></span>
                                    <input id="domicilio" type="text" name="domicilio" value="{{ old('domicilio') }}"
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
                                            <option value="{{ $val }}" @selected(old('estado_civil')===$val)>{{ $label }}</option>
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
                                    <input id="tipo_sangre" type="text" name="tipo_sangre" value="{{ old('tipo_sangre') }}" maxlength="5"
                                           class="form-control @error('tipo_sangre') is-invalid @enderror" placeholder="Ej. O+, A-">
                                </div>
                                <div class="form-hint">Formato corto (ej.: O+, A-, AB-).</div>
                                @error('tipo_sangre') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- CURP --}}
                            <div class="col-12 col-md-6">
                                <label for="curp" class="form-label">CURP</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-badge"></i></span>
                                    <input id="curp" type="text" name="curp" value="{{ old('curp') }}" maxlength="18"
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
                                    <input id="rfc" type="text" name="rfc" value="{{ old('rfc') }}" maxlength="13"
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
                                    <input id="contacto_emergencia_nombre" type="text" name="contacto_emergencia_nombre" value="{{ old('contacto_emergencia_nombre') }}"
                                           class="form-control @error('contacto_emergencia_nombre') is-invalid @enderror" placeholder="Ej. Juan Pérez">
                                </div>
                                @error('contacto_emergencia_nombre') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Parentesco --}}
                            <div class="col-12 col-md-6">
                                <label for="contacto_emergencia_parentesco" class="form-label">Parentesco</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-hearts"></i></span>
                                    <input id="contacto_emergencia_parentesco" type="text" name="contacto_emergencia_parentesco" value="{{ old('contacto_emergencia_parentesco') }}"
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
                                           value="{{ old('contacto_emergencia_tel') }}" class="form-control @error('contacto_emergencia_tel') is-invalid @enderror" placeholder="7770000000">
                                </div>
                                @error('contacto_emergencia_tel') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Ubicación emergencia --}}
                            <div class="col-12 col-md-6">
                                <label for="contacto_emergencia_ubicacion" class="form-label">Ubicación del contacto</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-map-pin"></i></span>
                                    <input id="contacto_emergencia_ubicacion" type="text" name="contacto_emergencia_ubicacion" value="{{ old('contacto_emergencia_ubicacion') }}"
                                           class="form-control @error('contacto_emergencia_ubicacion') is-invalid @enderror" placeholder="Cuernavaca, Morelos">
                                </div>
                                @error('contacto_emergencia_ubicacion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fotos (opcional) --}}
                    <div class="card-body border-top">
                        <h3 class="card-title d-flex align-items-center gap-2">
                            <i class="ti ti-photo"></i>
                            Fotografías
                        </h3>
                        <div class="form-hint mb-2">Máx 8MB c/u.</div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-upload"></i></span>
                            <input type="file" name="fotos[]" accept="image/*" multiple class="form-control @error('fotos') is-invalid @enderror @error('fotos.*') is-invalid @enderror">
                        </div>
                        @error('fotos')   <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @error('fotos.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Footer acciones --}}
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ url()->previous() ?: route('operadores.index') }}" class="btn btn-outline-dark">
                            <i class="ti ti-x me-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-device-floppy me-1"></i>
                            Guardar
                        </button>
                    </div>
                </div>
            </form>

            {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>
</x-app-layout>
