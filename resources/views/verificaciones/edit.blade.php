{{-- resources/views/verificaciones/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Editar Verificación #{{ $verificacion->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
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

            {{-- Mensaje de éxito opcional --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('verificaciones.update', $verificacion->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- Vehículo --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Vehículo *</label>
                        <select name="vehiculo_id" class="w-full border-gray-300 rounded" required>
                            <option value="">-- Selecciona un vehículo --</option>
                            @foreach ($vehiculos as $vehiculo)
                                <option value="{{ $vehiculo->id }}"
                                    {{ old('vehiculo_id', $verificacion->vehiculo_id) == $vehiculo->id ? 'selected' : '' }}>
                                    {{ $vehiculo->unidad }} - {{ $vehiculo->propietario }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehiculo_id')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Estado --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Estado *</label>
                        <input
                            type="text"
                            name="estado"
                            value="{{ old('estado', $verificacion->estado) }}"
                            class="w-full border-gray-300 rounded"
                            required
                        >
                        @error('estado')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Fecha de verificación --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Fecha de verificación *</label>
                        <input
                            type="date"
                            name="fecha_verificacion"
                            value="{{ old('fecha_verificacion', \Illuminate\Support\Carbon::parse($verificacion->fecha_verificacion)->format('Y-m-d')) }}"
                            class="w-full border-gray-300 rounded"
                            required
                        >
                        @error('fecha_verificacion')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Comentarios --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300">Comentarios</label>
                        <textarea
                            name="comentarios"
                            rows="3"
                            class="w-full border-gray-300 rounded"
                        >{{ old('comentarios', $verificacion->comentarios) }}</textarea>
                        @error('comentarios')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-between">
                        <a href="{{ route('verificaciones.index') }}"
                           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>

            {{-- (Opcional) Mostrar sólo-lectura el ID --}}
            <div class="text-sm text-gray-500 mt-4">
                ID de verificación: {{ $verificacion->id }}
            </div>
        </div>
    </div>
</x-app-layout>
