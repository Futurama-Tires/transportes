{{-- resources/views/tarjetas_comodin/edit.blade.php --}}
<x-app-layout>
    {{-- Encabezado (Tabler) --}}
    <div class="page-header d-print-none mb-3">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <br>
                    <h2 class="page-title">
                        Editar Tarjeta Comodín
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <br>
                    <div class="btn-list">
                        
                        <a href="{{ route('tarjetas-comodin.index') }}" class="btn btn-outline-dark">
                            <i class="ti ti-arrow-left me-1"></i>
                            Volver al listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cuerpo --}}
    <div class="page-body">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">

                    {{-- Errores de validación --}}
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <div class="d-flex">
                                <div>
                                    <i class="ti ti-alert-triangle me-2"></i>
                                </div>
                                <div>
                                    <h4 class="alert-title mb-1">Revisa los campos</h4>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Card del formulario --}}
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Datos de la tarjeta</h3>
                        </div>

                        <form method="POST" action="{{ route('tarjetas-comodin.update', $tarjeta) }}">
                            @csrf
                            @method('PUT')

                            <div class="card-body">

                                {{-- Número de tarjeta --}}
                                <div class="mb-3">
                                    <label class="form-label">Número de Tarjeta (4–16 dígitos) <span class="text-danger">*</span></label>
                                    <div class="input-icon">
                                        <span class="input-icon-addon">
                                            <i class="ti ti-credit-card"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="numero_tarjeta"
                                            value="{{ old('numero_tarjeta', $tarjeta->numero_tarjeta) }}"
                                            maxlength="16" minlength="4"
                                            pattern="[0-9]{4,16}"
                                            title="Debe contener entre 4 y 16 números"
                                            class="form-control @error('numero_tarjeta') is-invalid @enderror"
                                            placeholder="Ej. 1234567890123456"
                                            inputmode="numeric"
                                            required
                                        >
                                    </div>
                                    @error('numero_tarjeta')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- NIP --}}
                                <div class="mb-3">
                                    <label class="form-label">NIP (4 dígitos)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="ti ti-lock"></i>
                                        </span>
                                        <input
                                            type="password"
                                            id="nip-input"
                                            name="nip"
                                            value=""
                                            maxlength="4" minlength="4"
                                            pattern="[0-9]{4}"
                                            title="Debe contener exactamente 4 números"
                                            class="form-control @error('nip') is-invalid @enderror"
                                            placeholder="••••"
                                            inputmode="numeric"
                                        >
                                        <button type="button" id="toggle-nip" class="btn btn-outline-dark" aria-label="Mostrar/ocultar NIP">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    </div>
                                    @error('nip')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Fecha de vencimiento --}}
                                <div class="mb-3">
                                    <label class="form-label">Fecha de Vencimiento</label>
                                    <div class="input-icon">
                                        <span class="input-icon-addon">
                                            <i class="ti ti-calendar"></i>
                                        </span>
                                        <input
                                            type="month"
                                            name="fecha_vencimiento"
                                            value="{{ old('fecha_vencimiento', optional($tarjeta->fecha_vencimiento)->format('Y-m')) }}"
                                            class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                                        >
                                    </div>
                                    @error('fecha_vencimiento')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Descripción (opcional) --}}
                                <div class="mb-3">
                                    <label class="form-label">Descripción (opcional)</label>
                                    <textarea name="descripcion" rows="3" maxlength="1000" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $tarjeta->descripcion) }}</textarea>
                                    @error('descripcion')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <div class="card-footer d-flex gap-2">
                                <button type="submit" class="btn btn-danger">
                                    <i class="ti ti-device-floppy me-1"></i>
                                    Guardar cambios
                                </button>
                                <a href="{{ route('tarjetas-comodin.index') }}" class="btn btn-link">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Script para mostrar/ocultar NIP --}}
    <script>
        (function () {
            const btn = document.getElementById('toggle-nip');
            const input = document.getElementById('nip-input');
            if (btn && input) {
                btn.addEventListener('click', function () {
                    const isPwd = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPwd ? 'text' : 'password');
                    this.innerHTML = isPwd ? '<i class="ti ti-eye-off"></i>' : '<i class="ti ti-eye"></i>';
                });
            }
        })();
    </script>

    {{-- Footer (Tabler) --}}
    <footer class="footer footer-transparent d-print-none">
        <div class="container-xl">
            <div class="row text-center align-items-center flex-row-reverse">
                <div class="col-12">
                    <p class="mb-0 text-secondary small">
                        © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
                    </p>
                </div>
            </div>
        </div>
    </footer>
</x-app-layout>
