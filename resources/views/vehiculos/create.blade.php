{{-- resources/views/vehiculos/create.blade.php — Versión Tabler (con íconos y estilo ejecutivo) --}}
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
                            Agregar Vehículo
                        </h2>
                        <div class="text-secondary small mt-1">
                            Completa la información del vehículo. Los campos con <span class="text-danger">*</span> son obligatorios.
                        </div>
                    </div>
                    <div class="col-auto ms-auto">
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
            <form method="POST" action="{{ route('vehiculos.store') }}" novalidate enctype="multipart/form-data">
                @csrf

                {{-- Alertas --}}
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>
                        Revisa los campos marcados y vuelve a intentar.
                    </div>
                @endif

                {{-- ===== Card: Datos del vehículo ===== --}}
                <div class="card">
                    <div class="card-header justify-content-between">
                        <h3 class="card-title d-flex align-items-center gap-2 mb-0">
                            <i class="ti ti-car"></i>
                            Datos del vehículo
                        </h3>
                        <span class="badge bg-primary-lt">
                            <i class="ti ti-file-description me-1"></i>
                            Alta de registro
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Ubicación --}}
                            <div class="col-12 col-md-6">
                                <label for="ubicacion" class="form-label">Ubicación <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-map-pin"></i>
                                    </span>
                                    <select id="ubicacion" name="ubicacion" class="form-select @error('ubicacion') is-invalid @enderror" required>
                                        <option value="">-- Selecciona ubicación --</option>
                                        <option value="CVC" @selected(old('ubicacion')=='CVC')>Cuernavaca</option>
                                        <option value="IXT" @selected(old('ubicacion')=='IXT')>Ixtapaluca</option>
                                        <option value="QRO" @selected(old('ubicacion')=='QRO')>Querétaro</option>
                                        <option value="VALL" @selected(old('ubicacion')=='VALL')>Vallejo</option>
                                        <option value="GDL" @selected(old('ubicacion')=='GDL')>Guadalajara</option>
                                    </select>
                                </div>
                                @error('ubicacion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Propietario --}}
                            <div class="col-12 col-md-6">
                                <label for="propietario" class="form-label">Propietario <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-user"></i>
                                    </span>
                                    <input id="propietario" type="text" name="propietario" value="{{ old('propietario') }}" class="form-control @error('propietario') is-invalid @enderror" required placeholder="Nombre del propietario">
                                </div>
                                @error('propietario') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Unidad --}}
                            <div class="col-12 col-md-6">
                                <label for="unidad" class="form-label">Unidad <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-truck"></i>
                                    </span>
                                    <input id="unidad" type="text" name="unidad" value="{{ old('unidad') }}" class="form-control @error('unidad') is-invalid @enderror" required placeholder="Ej. U-012">
                                </div>
                                @error('unidad') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Serie (VIN) --}}
                            <div class="col-12 col-md-6">
                                <label for="serie" class="form-label">Serie (VIN) <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-barcode"></i>
                                    </span>
                                    <input id="serie" type="text" name="serie" value="{{ old('serie') }}" class="form-control @error('serie') is-invalid @enderror" required aria-describedby="serie_help" placeholder="Número de serie completo">
                                </div>
                                <div id="serie_help" class="form-hint">Usa el número de serie completo registrado en la tarjeta.</div>
                                @error('serie') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Marca --}}
                            <div class="col-12 col-md-6">
                                <label for="marca" class="form-label">Marca</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-badge"></i>
                                    </span>
                                    <input id="marca" type="text" name="marca" value="{{ old('marca') }}" class="form-control @error('marca') is-invalid @enderror" placeholder="Ej. Nissan, Toyota…">
                                </div>
                                @error('marca') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Año --}}
                            <div class="col-12 col-md-6">
                                <label for="anio" class="form-label">Año</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-calendar-stats"></i>
                                    </span>
                                    <input id="anio" type="number" name="anio" min="1900" max="{{ date('Y') + 1 }}" value="{{ old('anio') }}" class="form-control @error('anio') is-invalid @enderror" placeholder="{{ date('Y') }}">
                                </div>
                                @error('anio') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Motor --}}
                            <div class="col-12 col-md-6">
                                <label for="motor" class="form-label">Motor</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-engine"></i>
                                    </span>
                                    <input id="motor" type="text" name="motor" value="{{ old('motor') }}" class="form-control @error('motor') is-invalid @enderror" placeholder="Ej. 2.5 L / Diesel">
                                </div>
                                @error('motor') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Placa --}}
                            <div class="col-12 col-md-6">
                                <label for="placa" class="form-label">Placa</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-license"></i>
                                    </span>
                                    <input id="placa" type="text" name="placa" value="{{ old('placa') }}" class="form-control @error('placa') is-invalid @enderror" placeholder="ABC-123-4">
                                </div>
                                @error('placa') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Estado --}}
                            <div class="col-12 col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-map"></i>
                                    </span>
                                    <input id="estado" type="text" name="estado" value="{{ old('estado') }}" class="form-control @error('estado') is-invalid @enderror" placeholder="Entidad federativa">
                                </div>
                                @error('estado') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Tarjeta SiVale --}}
                            <div class="col-12">
                                <label for="tarjeta_si_vale_id" class="form-label">Tarjeta SiVale</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-credit-card"></i>
                                    </span>
                                    <select id="tarjeta_si_vale_id" name="tarjeta_si_vale_id" class="form-select @error('tarjeta_si_vale_id') is-invalid @enderror" aria-describedby="sivale_help">
                                        <option value="">-- Sin tarjeta asignada --</option>
                                        @foreach($tarjetas as $tarjeta)
                                            <option value="{{ $tarjeta->id }}" @selected(old('tarjeta_si_vale_id') == $tarjeta->id)>{{ $tarjeta->numero_tarjeta }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="sivale_help" class="form-hint">Asigna una tarjeta válida en caso de corresponder.</div>
                                @error('tarjeta_si_vale_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Vencimiento tarjeta de circulación --}}
                            <div class="col-12 col-md-6">
                                <label for="vencimiento_t_circulacion" class="form-label">Vencimiento tarjeta de circulación</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-calendar-event"></i>
                                    </span>
                                    <input id="vencimiento_t_circulacion" type="date" name="vencimiento_t_circulacion" value="{{ old('vencimiento_t_circulacion') }}" class="form-control @error('vencimiento_t_circulacion') is-invalid @enderror">
                                </div>
                                @error('vencimiento_t_circulacion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Cambio de placas --}}
                            <div class="col-12 col-md-6">
                                <label for="cambio_placas" class="form-label">Cambio de placas</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-calendar-event"></i>
                                    </span>
                                    <input id="cambio_placas" type="date" name="cambio_placas" value="{{ old('cambio_placas') }}" class="form-control @error('cambio_placas') is-invalid @enderror">
                                </div>
                                @error('cambio_placas') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Póliza HDI --}}
                            <div class="col-12 col-md-6">
                                <label for="poliza_hdi" class="form-label">Póliza HDI</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-shield-check"></i>
                                    </span>
                                    <input id="poliza_hdi" type="text" name="poliza_hdi" value="{{ old('poliza_hdi') }}" class="form-control @error('poliza_hdi') is-invalid @enderror" placeholder="Número de póliza">
                                </div>
                                @error('poliza_hdi') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fotos (opcional) --}}
                    <div class="card-body border-top">
                        <h3 class="card-title d-flex align-items-center gap-2">
                            <i class="ti ti-photo"></i>
                            Fotos (opcional)
                        </h3>
                        <div class="form-hint mb-2">Puedes seleccionar varias. JPG, JPEG, PNG o WEBP. Máx 8MB c/u.</div>
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
                            <i class="ti ti-x me-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Guardar
                        </button>
                    </div>
                </div>
            </form>

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
</x-app-layout>
