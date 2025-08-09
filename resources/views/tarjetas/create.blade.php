<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Nueva Tarjeta SiVale
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- Mostrar errores --}}
                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('tarjetas.store') }}">
                    @csrf

                    {{-- Número de tarjeta --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Número de Tarjeta (16 dígitos) *</label>
                        <input type="text" name="numero_tarjeta"
                               value="{{ old('numero_tarjeta') }}"
                               maxlength="16" minlength="16"
                               pattern="[0-9]{16}"
                               title="Debe contener exactamente 16 números"
                               class="w-full border-gray-300 rounded"
                               required>
                        @error('numero_tarjeta')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- NIP --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">NIP (4 dígitos)</label>
                        <input type="text" name="nip"
                               value="{{ old('nip') }}"
                               maxlength="4" minlength="4"
                               pattern="[0-9]{4}"
                               title="Debe contener exactamente 4 números"
                               class="w-full border-gray-300 rounded">
                        @error('nip')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Fecha de vencimiento --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Fecha de Vencimiento *</label>
                        <input type="month" name="fecha_vencimiento"
                               value="{{ old('fecha_vencimiento') }}"
                               class="w-full border-gray-300 rounded"
                               required>
                        @error('fecha_vencimiento')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                        <small class="text-gray-500">Ejemplo: Febrero 2026</small>
                    </div>

                    {{-- Botón guardar --}}
                    <div>
                        <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Guardar
                        </button>
                        <a href="{{ route('tarjetas.index') }}"
                           class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
