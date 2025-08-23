{{-- resources/views/welcome.blade.php (versión minimal) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de combustible</title>

    {{-- Tipografía + Tailwind CDN (si usas Vite, cambia por @vite) --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter','ui-sans-serif','system-ui'] } } }
        }
    </script>
    <meta name="color-scheme" content="light dark">
</head>
<body class="antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">

    {{-- Header: Logo + Log in --}}
    <header class="relative z-20">
        <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('images/logoOriginal.png') }}" alt="Futurama Tires" class="h-10 w-auto rounded-sm shadow-sm">
                <span class="font-semibold tracking-tight text-lg">Gestión de combustible</span>
            </a>

            @if (Route::has('login'))
                <a href="{{ route('login') }}"
                   class="inline-flex items-center rounded-xl border border-slate-300/60 dark:border-slate-700/60 px-4 py-2 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Log in
                </a>
            @endif
        </nav>
    </header>

    {{-- HERO: imagen grande + propósito + CTA --}}
    <section class="relative isolate">
        {{-- Coloca tu imagen en public/images/hero-fuel.jpg --}}
        <div class="absolute inset-0 -z-10">
            <img src="{{ asset('images/fondo.jpg') }}" alt="Control de combustible para flotillas" class="h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/60 via-slate-950/40 to-slate-950/70"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-28 pb-28 md:pt-40 md:pb-40">
            <div class="max-w-3xl">
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">
                    Gestión de combustible
                </h1>
                <p class="mt-4 text-slate-200/90 text-base md:text-lg">
                    Plataforma para controlar cargas y rendimiento de tu flotilla.
                </p>

                @if (Route::has('login'))
                    <div class="mt-8">
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-white/95 text-slate-900 px-5 py-3 text-sm font-semibold shadow hover:bg-white transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white/60">
                            Log in
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M17 8l4 4m0 0l-4 4m4-4H7"/>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
        </div>
    </footer>

</body>
</html>
