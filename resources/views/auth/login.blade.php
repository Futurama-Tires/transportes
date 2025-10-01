{{-- resources/views/auth/login.blade.php (split: imagen izquierda + logo arriba del formulario + inputs más grandes) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de combustible — Iniciar sesión</title>

    {{-- Tipografía + Tailwind CDN (con plugin de forms) --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter','ui-sans-serif','system-ui'] } } }
        }
    </script>
    <meta name="color-scheme" content="light dark">
</head>
<body class="antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 min-h-screen grid grid-cols-1 md:grid-cols-2">

    {{-- Columna izquierda: imagen a pantalla completa (solo desktop) --}}
    <aside class="relative hidden md:block">
        <img
            src="{{ asset('images/fondo_login2.png') }}"
            alt="Control de combustible para flotillas"
            class="absolute inset-0 h-full w-full object-cover"
        >
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/40 to-slate-900/60"></div>
    </aside>

    {{-- Columna derecha: logo arriba + formulario --}}
    <main class="flex items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-md">

            {{-- Logo arriba del formulario (siempre visible) --}}
            <div class="mb-8">
                <a href="{{ url('/') }}" class="w-full flex flex-col items-center">
                    <img src="{{ asset('images/logoOriginal.png') }}" alt="Futurama Tires" class="h-14 w-auto rounded-sm shadow-sm">
                    <span class="mt-3 text-base font-semibold tracking-tight">Transportes</span>
                </a>
            </div>

            <h1 class="text-2xl font-bold tracking-tight">Iniciar sesión</h1>
            <br>

            <!-- Session Status -->
            <x-auth-session-status class="mt-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Correo electrónico')" />
                    <x-text-input
                        id="email"
                        class="mt-2 block w-full text-base px-4 py-3 rounded-xl"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Contraseña')" />
                    <x-text-input
                        id="password"
                        class="mt-2 block w-full text-base px-4 py-3 rounded-xl"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me + Forgot -->
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input
                            id="remember_me"
                            type="checkbox"
                            class="rounded border-gray-300 dark:border-gray-700 text-slate-900 dark:text-slate-100 shadow-sm focus:ring-slate-600 dark:focus:ring-slate-500 dark:focus:ring-offset-gray-800"
                            name="remember"
                        >
                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Recordarme') }}
                        </span>
                    </label>

                    @if (Route::has('password.request'))
                        <a
                            class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 rounded"
                            href="{{ route('password.request') }}"
                        >
                            {{ __('Olvidé mi contraseña') }}
                        </a>
                    @endif
                </div>

                <!-- Submit -->
                <div>
                    <x-primary-button class="w-full justify-center h-12 text-base">
                        {{ __('ACCEDER') }}
                    </x-primary-button>
                </div>
            </form>

            {{-- Pie mini (opcional) --}}
            <p class="mt-8 text-[11px] leading-tight text-slate-500 text-center">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </p>
        </div>
    </main>

</body>
</html>
