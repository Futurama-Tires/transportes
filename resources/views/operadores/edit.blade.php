{{-- resources/views/operadores/edit.blade.php — versión Tabler (formulario con grid y tarjeta lateral) --}}
<x-app-layout>
    {{-- Si ya incluyes @vite en tu layout, puedes quitar esta línea --}}
    @vite(['resources/js/app.js'])

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <p class="text-secondary text-uppercase small mb-1">Operadores</p>
                        <h2 class="page-title mb-0">Editar Operador</h2>
                        <div class="text-secondary small mt-1">Actualiza la información básica del operador.</div>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('operadores.index') }}" class="btn btn-outline-secondary">
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
            {{-- FLASHES --}}
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="ti ti-check me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <i class="ti ti-alert-triangle me-2"></i>Revisa los campos marcados y vuelve a intentar.
                </div>
            @endif

            <div class="row row-deck row-cards">
                {{-- FORM PRINCIPAL --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Datos del operador</h3>
                                <div class="card-subtitle">Completa o corrige la información requerida.</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('operadores.update', $operador->id) }}" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Nombre --}}
                                    <div class="col-md-6">
                                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-signature"></i></span>
                                            <input
                                                id="nombre"
                                                name="nombre"
                                                type="text"
                                                autocomplete="given-name"
                                                class="form-control @error('nombre') is-invalid @enderror"
                                                value="{{ old('nombre', $operador->nombre) }}"
                                                required
                                                placeholder="Ej. Juan"
                                            >
                                            @error('nombre')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Apellido paterno --}}
                                    <div class="col-md-6">
                                        <label for="apellido_paterno" class="form-label">Apellido paterno <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-user"></i></span>
                                            <input
                                                id="apellido_paterno"
                                                name="apellido_paterno"
                                                type="text"
                                                autocomplete="family-name"
                                                class="form-control @error('apellido_paterno') is-invalid @enderror"
                                                value="{{ old('apellido_paterno', $operador->apellido_paterno) }}"
                                                required
                                                placeholder="Ej. Pérez"
                                            >
                                            @error('apellido_paterno')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Apellido materno --}}
                                    <div class="col-md-6">
                                        <label for="apellido_materno" class="form-label">Apellido materno</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-user-circle"></i></span>
                                            <input
                                                id="apellido_materno"
                                                name="apellido_materno"
                                                type="text"
                                                autocomplete="additional-name"
                                                class="form-control @error('apellido_materno') is-invalid @enderror"
                                                value="{{ old('apellido_materno', $operador->apellido_materno) }}"
                                                placeholder="(opcional)"
                                            >
                                            @error('apellido_materno')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Correo --}}
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                            <input
                                                id="email"
                                                name="email"
                                                type="email"
                                                autocomplete="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email', optional($operador->user)->email) }}"
                                                required
                                                placeholder="usuario@dominio.com"
                                                aria-describedby="email_help"
                                            >
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div id="email_help" class="form-hint">Usa un correo válido y único.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-between">
                                <a href="{{ route('operadores.index') }}" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i>Guardar cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- TARJETA LATERAL (resumen) --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <span class="avatar avatar-xl avatar-rounded bg-blue-lt mb-3">
                                <i class="ti ti-user"></i>
                            </span>
                            @php
                                $nombreCompleto = trim(($operador->nombre ?? '').' '.($operador->apellido_paterno ?? '').' '.($operador->apellido_materno ?? ''));
                                $correo = optional($operador->user)->email ?? '—';
                            @endphp
                            <div class="h3">{{ $nombreCompleto ?: 'Operador #'.$operador->id }}</div>
                            <div class="text-secondary">{{ $correo }}</div>

                            <div class="mt-3 text-start small text-secondary">
                                @if(optional($operador->created_at)->isValid())
                                    <div><i class="ti ti-calendar-stats me-1"></i> Registrado: <span class="fw-semibold">{{ optional($operador->created_at)->format('Y-m-d') }}</span></div>
                                @endif
                                @if(optional($operador->updated_at)->isValid())
                                    <div><i class="ti ti-history me-1"></i> Actualizado: <span class="fw-semibold">{{ optional($operador->updated_at)->diffForHumans() }}</span></div>
                                @endif
                            </div>

                            {{-- Acciones rápidas opcionales (deshabilitadas si no existen rutas) --}}
                            <div class="d-grid gap-2 mt-4">
                                <a href="{{ route('operadores.index') }}" class="btn btn-light">
                                    <i class="ti ti-list-details me-1"></i> Ir al listado
                                </a>
                            </div>
                        </div>
                    </div>

                </div>{{-- /col-md-4 --}}
            </div>{{-- /row --}}
            
            {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>
</x-app-layout>
