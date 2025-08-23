<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Capturistas
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">

            {{-- Mensajes de éxito --}}
            @if(session('success'))
                <div class="mb-4 text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Barra superior: búsqueda + botón crear --}}
            <div class="mb-4 flex justify-between items-center">
                <form method="GET" action="{{ route('capturistas.index') }}" class="flex gap-2">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Buscar por nombre o correo..."
                           class="border rounded px-3 py-2 text-sm w-64 focus:ring focus:ring-blue-300 focus:outline-none">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Buscar
                    </button>
                </form>

                <a href="{{ route('capturistas.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                   + Agregar Capturista
                </a>
            </div>

            {{-- Tabla --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
                        <thead>
                            <tr>
                                <th class="border px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nombre</th>
                                <th class="border px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Correo</th>
                                <th class="border px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($capturistas as $capturista)
                                <tr>
                                    <td class="border px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $capturista->nombre }} {{ $capturista->apellido_paterno }} {{ $capturista->apellido_materno }}
                                    </td>
                                    <td class="border px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $capturista->user->email }}
                                    </td>
                                    <td class="border px-4 py-2 text-sm">
                                        <a href="{{ route('capturistas.edit', $capturista->id) }}"
                                           class="text-blue-500 hover:underline">Editar</a>

                                        <form action="{{ route('capturistas.destroy', $capturista->id) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('¿Seguro que quieres eliminar este capturista?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:underline ml-2">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="border px-4 py-2 text-center text-gray-500 dark:text-gray-300">
                                        No hay capturistas registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            @if(method_exists($capturistas, 'links'))
                <div class="mt-4">
                    {{ $capturistas->appends(['search' => request('search')])->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
