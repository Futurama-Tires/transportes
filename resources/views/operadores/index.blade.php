<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Operadores
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
                <div class="mb-4 text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            <a href="{{ route('operadores.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4 inline-block">
               + Agregar Operador
            </a>

            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="border px-4 py-2">Nombre</th>
                        <th class="border px-4 py-2">Correo</th>
                        <th class="border px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operadores as $operador)
                        <tr>
                            <td class="border px-4 py-2">
                                {{ $operador->nombre }} {{ $operador->apellido_paterno }} {{ $operador->apellido_materno }}
                            </td>
                            <td class="border px-4 py-2">
                                {{ $operador->user->email }}
                            </td>
                            <td class="border px-4 py-2">
                                <a href="{{ route('operadores.edit', $operador->id) }}"
                                   class="text-blue-500 hover:underline">Editar</a>

                                <form action="{{ route('operadores.destroy', $operador->id) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('¿Seguro que quieres eliminar este operador?')">
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
                            <td colspan="3" class="border px-4 py-2 text-center">
                                No hay operadores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
