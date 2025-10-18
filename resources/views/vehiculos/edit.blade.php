{{-- resources/views/vehiculos/edit.blade.php --}}
<x-app-layout>
    {{-- ===== HEADER ===== --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title mb-0 d-flex align-items-center gap-2">
                            Editar Vehículo
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-flex gap-2">
                        <a href="{{ route('vehiculos.tanques.index', $vehiculo) }}" class="btn btn-warning">
                            <i class="ti ti-gas-station me-1"></i>
                            Tanques de combustible
                        </a>
                        <a href="{{ route('vehiculos.index') }}" class="btn btn-outline-dark">
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
                 NOTA: Este formulario ahora también envuelve la sección de "Fotografías actuales"
                 para que los checkboxes de eliminación viajen en el mismo submit. --}}
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
                @error('general')
                    <div class="alert alert-danger" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>{{ $message }}
                    </div>
                @enderror

                <div class="card">
                    <div class="card-header justify-content-between">
                        <h3 class="card-title d-flex align-items-center gap-2 mb-0">
                            <i class="ti ti-car"></i>
                            Datos del vehículo
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Ubicación --}}
                            <div class="col-12 col-md-6">
                                <label for="ubicacion" class="form-label">Ubicación <span class="text-danger">*</span></label>
                                <div class="input-icon">
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

                            {{-- Pólizas --}}
                            <div class="col-12 col-md-6">
                                <label for="poliza_hdi" class="form-label">Póliza HDI</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-shield-check"></i></span>
                                    <input id="poliza_hdi" type="text" name="poliza_hdi" value="{{ old('poliza_hdi', $vehiculo->poliza_hdi ?? '') }}" class="form-control @error('poliza_hdi') is-invalid @enderror" placeholder="Número de póliza">
                                </div>
                                @error('poliza_hdi') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="poliza_latino" class="form-label">Póliza Latino</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon"><i class="ti ti-shield-plus"></i></span>
                                    <input id="poliza_latino" type="text" name="poliza_latino" value="{{ old('poliza_latino', $vehiculo->poliza_latino ?? '') }}" class="form-control @error('poliza_latino') is-invalid @enderror" placeholder="Número de póliza">
                                </div>
                                @error('poliza_latino') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

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
                                    {{ $vehiculo->fotos->count() }} foto(s)
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

                        @if($vehiculo->fotos->isEmpty())
                            <div class="empty">
                                <div class="empty-icon"><i class="ti ti-photo-off"></i></div>
                                <p class="empty-title">Este vehículo aún no tiene fotografías</p>
                            </div>
                        @else
                            @php
                                $oldEliminar = collect(old('fotos_eliminar', []))->map(fn($v)=> (int)$v)->all();
                            @endphp

                            <div class="row g-2">
                                @foreach($vehiculo->fotos as $foto)
                                    @php $checked = in_array($foto->id, $oldEliminar, true); @endphp
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <div class="card photo-card h-100 position-relative {{ $checked ? 'is-selected' : '' }}">
                                            <a href="{{ route('vehiculos.fotos.show', $foto) }}"
                                               target="_blank" rel="noopener noreferrer" title="Abrir en nueva pestaña" class="d-block">
                                                <div class="img-responsive img-responsive-4x3 card-img-top"
                                                     style="background-image: url('{{ route('vehiculos.fotos.show', $foto) }}')"></div>
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
                        <a href="{{ url()->previous() ?: route('vehiculos.index') }}" class="btn btn-outline-dark">
                            <i class="ti ti-x me-1"></i> Cancelar
                        </a>
                        <button type="submit" form="vehiculo-form" class="btn btn-danger">
                            <i class="ti ti-device-floppy me-1"></i> Guardar cambios
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

    <style>
        /* Tarjeta de foto con overlay cuando está seleccionada */
        .photo-card {
            transition: transform .12s ease, box-shadow .12s ease;
        }
        .photo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,.08);
        }
        .photo-card.is-selected {
            outline: 2px solid var(--tblr-red, #f03e3e);
            outline-offset: 0;
        }
        .photo-overlay-check {
            position: absolute;
            inset: .5rem .5rem auto auto;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 50%;
            background: rgba(240,62,62,.95); /* rojo */
            color: #fff;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }
        .photo-card.is-selected .photo-overlay-check {
            display: flex;
        }
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
                // estado inicial (por si viene del old())
                syncCardState(ch);
                ch.addEventListener('change', () => {
                    syncCardState(ch);
                    refreshBadge();
                });
            });

            if (btnAll) {
                btnAll.addEventListener('click', () => {
                    checks.forEach(ch => ch.checked = true);
                    checks.forEach(syncCardState);
                    refreshBadge();
                });
            }
            if (btnClr) {
                btnClr.addEventListener('click', () => {
                    checks.forEach(ch => ch.checked = false);
                    checks.forEach(syncCardState);
                    refreshBadge();
                });
            }

            // pinta badge al cargar
            refreshBadge();
        })();
    </script>
</x-app-layout>
