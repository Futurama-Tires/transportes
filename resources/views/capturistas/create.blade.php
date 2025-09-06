<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Capturistas</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                    Registrar Nuevo Capturista
                </h2>
            </div>
            <a href="{{ route('capturistas.index') }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Mostrar errores --}}
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('capturistas.store') }}">
                    @csrf

                    {{-- Nombre --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Nombre *</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}"
                               class="w-full border-gray-300 rounded" required>
                        @error('nombre')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Apellido paterno --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Apellido paterno *</label>
                        <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}"
                               class="w-full border-gray-300 rounded" required>
                        @error('apellido_paterno')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Apellido materno --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Apellido materno</label>
                        <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}"
                               class="w-full border-gray-300 rounded">
                        @error('apellido_materno')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-6">
                        <label class="block text-gray-700 dark:text-gray-300">Email (@futuramatiresmx.com) *</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full border-gray-300 rounded" required>
                        @error('email')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end">
                        <a href="{{ route('capturistas.index') }}"
                           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Crear Capturista
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal flotante de confirmación (se muestra si hay datos en sesión) --}}
    @if(session('created') && session('email') && session('password'))
        <div x-data="{ open: true }"
             x-show="open"
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             aria-modal="true" role="dialog">

            <div x-transition
                 class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg dark:bg-slate-800">
                <div class="flex items-start justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Capturista creado exitosamente
                    </h3>
                    <button @click="open=false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" aria-label="Cerrar">
                        ✕
                    </button>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <p class="text-slate-600 dark:text-slate-300">
                        Guarda estas credenciales de acceso:
                    </p>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="font-medium">Correo:</span>
                            <code class="select-all">{{ session('email') }}</code>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-medium">Contraseña:</span>
                            <code id="gen-pass" class="select-all">{{ session('password') }}</code>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button"
                                onclick="navigator.clipboard.writeText(document.getElementById('gen-pass').innerText)"
                                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                            Copiar contraseña
                        </button>
                        <button type="button"
                                @click="open=false; window.location.href='{{ route('capturistas.index') }}';"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            Aceptar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Si tu proyecto NO carga AlpineJS por defecto, descomenta esto: --}}
        {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
    @endif
</x-app-layout>
