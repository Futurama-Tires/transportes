<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Registrar Nuevo Capturista
        </h2>
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
                    <div class="mb-4">
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
</x-app-layout>
