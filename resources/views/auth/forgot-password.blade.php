{{-- resources/views/auth/forgot-password.blade.php --}}
<x-app-layout>
    <div class="container-xl">
        {{-- Mensajes --}}
        @if (session('status'))
            <div class="alert alert-success my-3">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger my-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="page-header d-print-none mb-3">
            <div class="row align-items-end">
                <div class="col">
                    <h2 class="page-title">¿Olvidaste tu contraseña?</h2>
                    <div class="text-secondary">
                        Escribe tu correo y te enviaremos un enlace para restablecerla.
                    </div>
                </div>
            </div>
        </div>

        {{-- Formulario --}}
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input id="email"
                               type="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               placeholder="tucorreo@dominio.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Te enviaremos un enlace para restablecer tu contraseña.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-dark">
                            Volver
                        </a>
                        <button type="submit" class="btn btn-danger">
                            Enviar enlace
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Enlace de regreso al login (opcional) --}}
        <div class="text-center text-secondary mt-3">
            <a href="{{ route('login') }}">Volver a iniciar sesión</a>
        </div>
    </div>
</x-app-layout>
