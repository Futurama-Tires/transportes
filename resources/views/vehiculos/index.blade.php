<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Vehículos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
                <div class="mb-4 text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            
            <a href="{{ route('vehiculos.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4 inline-block">
                + Agregar Vehículo
            </a>
            

            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="border px-4 py-2">Unidad</th>
                        <th class="border px-4 py-2">Propietario</th>
                        <th class="border px-4 py-2">Placa</th>
                        <th class="border px-4 py-2">Estado</th>
                        <th class="border px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehiculos as $vehiculo)
                        <tr>
                            <td class="border px-4 py-2">{{ $vehiculo->unidad }}</td>
                            <td class="border px-4 py-2">{{ $vehiculo->propietario }}</td>
                            <td class="border px-4 py-2">{{ $vehiculo->placa ?? 'Sin placa' }}</td>
                            <td class="border px-4 py-2">{{ $vehiculo->estado ?? '-' }}</td>
                            <td class="border px-4 py-2">
                                <a href="{{ route('vehiculos.show', $vehiculo) }}"
                                   class="text-green-500 hover:underline">Ver</a>
                                <a href="{{ route('vehiculos.edit', $vehiculo) }}"
                                   class="text-blue-500 hover:underline ml-2">Editar</a>

                                @role('administrador')
                                    <form action="{{ route('vehiculos.destroy', $vehiculo) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este vehículo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:underline ml-2">
                                            Eliminar
                                        </button>
                                    </form>
                                @endrole
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border px-4 py-2 text-center">
                                No hay vehículos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $vehiculos->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
