{{-- resources/views/vehiculos/fotos/index.blade.php --}}
<x-app-layout>
    {{-- Encabezado de página tipo Tabler --}}
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Vehículos
                    </div>
                    <h2 class="page-title">
                        Fotos del vehículo: {{ $vehiculo->unidad ?? "#{$vehiculo->id}" }}
                    </h2>
                    <div class="text-muted mt-1">
                        <span class="me-3">
                            <i class="ti ti-license me-1"></i>
                            Placa: <span class="badge bg-blue-lt">{{ $vehiculo->placa ?? '—' }}</span>
                        </span>
                        <span>
                            <i class="ti ti-hash me-1"></i>
                            Serie: <span class="badge bg-azure-lt">{{ $vehiculo->serie ?? '—' }}</span>
                        </span>
                    </div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('vehiculos.index') }}" class="btn btn-outline-dark">
                            <i class="ti ti-arrow-left me-1"></i>
                            Volver al listado
                        </a>
                        <a href="{{ route('vehiculos.edit', $vehiculo) }}" class="btn btn-danger">
                            <i class="ti ti-edit me-1"></i>
                            Editar vehículo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido --}}
    <div class="page-body">
        <div class="container-xl">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>
                            <i class="ti ti-circle-check me-2"></i>
                            {{ session('success') }}
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="Close"></a>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>
                            <i class="ti ti-alert-circle me-2"></i>
                            Se encontraron algunos errores. Revisa el formulario.
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="Close"></a>
                    </div>
                </div>
            @endif

            {{-- Card: Subir fotos --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-upload me-2"></i> Subir nuevas fotos
                    </h3>
                    <div class="card-actions">
                        <span class="badge bg-green-lt">
                            <i class="ti ti-camera me-1"></i> JPG · PNG · WEBP · Máx. 8MB c/u
                        </span>
                    </div>
                </div>
                <form method="POST" action="{{ route('vehiculos.fotos.store', $vehiculo) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Selecciona una o varias imágenes</label>
                            <input class="form-control" type="file" name="fotos[]" accept="image/*" multiple>
                            @error('fotos')
                                <div class="form-help text-danger">{{ $message }}</div>
                            @enderror
                            @error('fotos.*')
                                <div class="form-help text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-cloud-upload me-1"></i>
                            Subir
                        </button>
                    </div>
                </form>
            </div>

            {{-- Card: Galería --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-photo me-2"></i>
                        Fotos actuales
                    </h3>
                    <div class="card-actions">
                        <span class="badge bg-indigo-lt">
                            <i class="ti ti-folder-image me-1"></i>
                            {{ $vehiculo->fotos->count() }} foto(s)
                        </span>
                    </div>
                </div>

                @if($vehiculo->fotos->isEmpty())
                    <div class="card-body">
                        <div class="empty">
                            <div class="empty-img">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="64" height="64" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M15 8h.01" />
                                    <path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1v-10a1 1 0 0 1 1 -1" />
                                    <path d="M4 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" />
                                    <path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l2 2" />
                                </svg>
                            </div>
                            <p class="empty-title">Aún no hay fotos</p>
                            <p class="empty-subtitle text-muted">
                                Sube imágenes del vehículo para documentar su estado, accesorios, placas, etc.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <div class="row row-cards">
                            @foreach($vehiculo->fotos as $foto)
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <div class="card card-sm">
                                        <a href="{{ route('vehiculos.fotos.show', $foto) }}" class="d-block" target="_blank" title="Ver en tamaño completo">
                                            <img src="{{ route('vehiculos.fotos.show', $foto) }}"
                                                 class="card-img-top" alt="Foto del vehículo">
                                        </a>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="text-secondary">
                                                        @if(!empty($foto->descripcion))
                                                            {{ $foto->descripcion }}
                                                        @else
                                                            Foto #{{ $loop->iteration }}
                                                        @endif
                                                    </div>
                                                    <div class="text-muted">
                                                        @if(!empty($foto->created_at))
                                                            <small>
                                                                <i class="ti ti-calendar-time me-1"></i>
                                                                {{ $foto->created_at->format('d M Y H:i') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="ms-auto">
                                                    <div class="btn-list">
                                                        <a href="{{ route('vehiculos.fotos.show', $foto) }}" target="_blank"
                                                           class="btn btn-outline-dark btn-icon"
                                                           data-bs-toggle="tooltip" data-bs-title="Abrir en nueva pestaña">
                                                            <i class="ti ti-eye"></i>
                                                        </a>

                                                        <form method="POST"
                                                              action="{{ route('vehiculos.fotos.destroy', [$vehiculo, $foto]) }}"
                                                              class="d-inline"
                                                              onsubmit="return confirm('¿Eliminar esta foto de forma permanente?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-danger btn-icon"
                                                                    data-bs-toggle="tooltip" data-bs-title="Eliminar">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> {{-- /card --}}
                                </div>
                            @endforeach
                        </div> {{-- /row --}}
                    </div> {{-- /card-body --}}
                @endif
            </div>

            <div class="mt-3 text-muted">
                <small>
                    <i class="ti ti-shield-lock me-1"></i>
                    Las fotos se almacenan de forma privada y se sirven bajo autenticación. No se exponen URLs públicas.
                </small>
            </div>
        </div>
    </div>

    {{-- Script mínimo para tooltips (Bootstrap 5 / Tabler) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        })
    </script>
</x-app-layout>
