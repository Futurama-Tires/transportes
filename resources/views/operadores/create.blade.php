{{-- resources/views/operadores/create.blade.php — versión Tabler (formulario + modal de credenciales) --}}
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
                        <h2 class="page-title mb-0">Registrar Nuevo Operador</h2>
                        <div class="text-secondary small mt-1">Crea un nuevo operador y asigna su correo institucional.</div>
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

            {{-- FLASH ÉXITO --}}
            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="ti ti-check me-2"></i>{{ session('success') }}
                </div>
            @endif

            {{-- ERRORES --}}
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <i class="ti ti-alert-triangle me-2"></i>Revisa los campos marcados y vuelve a intentar.
                    @if($errors->count() > 0)
                        <ul class="mt-2 mb-0 ps-4">
                            @foreach ($errors->all() as $error)
                                <li class="small">{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <div class="row row-deck row-cards">
                {{-- FORM PRINCIPAL --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Datos del operador</h3>
                                <div class="card-subtitle">Completa la información requerida para crear el registro.</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('operadores.store') }}" novalidate>
                            @csrf

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
                                                class="form-control @error('nombre') is-invalid @enderror"
                                                value="{{ old('nombre') }}"
                                                required
                                                placeholder="Ej. Juan"
                                                autocomplete="given-name"
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
                                                class="form-control @error('apellido_paterno') is-invalid @enderror"
                                                value="{{ old('apellido_paterno') }}"
                                                required
                                                placeholder="Ej. Pérez"
                                                autocomplete="family-name"
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
                                                class="form-control @error('apellido_materno') is-invalid @enderror"
                                                value="{{ old('apellido_materno') }}"
                                                placeholder="(opcional)"
                                                autocomplete="additional-name"
                                            >
                                            @error('apellido_materno')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Email --}}
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                            <input
                                                id="email"
                                                name="email"
                                                type="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email') }}"
                                                required
                                                placeholder="usuario@futuramatiresmx.com"
                                                autocomplete="email"
                                                aria-describedby="email_help"
                                            >
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div id="email_help" class="form-hint">
                                            Usa un correo válido del dominio <strong>@futuramatiresmx.com</strong>.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-between">
                                <a href="{{ route('operadores.index') }}" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-user-plus me-1"></i>Crear Operador
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

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
                            <i class="ti ti-check me-2 text-success"></i>Operador creado exitosamente
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
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="navigator.clipboard.writeText(document.getElementById('gen-pass').innerText)">
                            <i class="ti ti-copy me-1"></i>Copiar contraseña
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            Aceptar
                        </a>
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
</x-app-layout>
