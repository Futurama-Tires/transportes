<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Vehículos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensajes de éxito --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Botón crear --}}
            <div class="mb-4">
                <a href="{{ route('vehiculos.create') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Agregar Vehículo
                </a>
            </div>

            {{-- Tabla --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Unidad</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Propietario</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Placa</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($vehiculos as $vehiculo)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $vehiculo->unidad }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $vehiculo->propietario }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $vehiculo->placa ?? 'Sin placa' }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $vehiculo->estado ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-sm font-medium">
                                        <a href="{{ route('vehiculos.show', $vehiculo) }}"
                                           class="text-green-500 hover:text-green-700">Ver</a>
                                        <a href="{{ route('vehiculos.edit', $vehiculo) }}"
                                           class="ml-2 text-yellow-500 hover:text-yellow-700">Editar</a>

                                        @role('administrador')
                                            <form action="{{ route('vehiculos.destroy', $vehiculo) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('¿Seguro que deseas eliminar este vehículo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ml-2 text-red-500 hover:text-red-700">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endrole
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-2 text-center text-gray-500 dark:text-gray-300">
                                        No hay vehículos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $vehiculos->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
