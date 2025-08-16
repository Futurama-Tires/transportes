<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Operadores
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
                <a href="{{ route('operadores.create') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Agregar Operador
                </a>
            </div>

            {{-- Tabla --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nombre</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Correo</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($operadores as $operador)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $operador->nombre }} {{ $operador->apellido_paterno }} {{ $operador->apellido_materno }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $operador->user->email }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-sm font-medium">
                                        <a href="{{ route('operadores.edit', $operador->id) }}"
                                           class="text-yellow-500 hover:text-yellow-700">
                                            Editar
                                        </a>

                                        <form action="{{ route('operadores.destroy', $operador->id) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('¿Seguro que quieres eliminar este operador?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-red-500 hover:text-red-700">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-center text-gray-500 dark:text-gray-300">
                                        No hay operadores registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            @if(method_exists($operadores, 'links'))
                <div class="mt-4">
                    {{ $operadores->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
