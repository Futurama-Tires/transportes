<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Capturistas
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
                <div class="mb-4 text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            <a href="{{ route('capturistas.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4 inline-block">
               + Agregar Capturista
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
                    @forelse($capturistas as $capturista)
                        <tr>
                            <td class="border px-4 py-2">
                                {{ $capturista->nombre }} {{ $capturista->apellido_paterno }} {{ $capturista->apellido_materno }}
                            </td>
                            <td class="border px-4 py-2">
                                {{ $capturista->user->email }}
                            </td>
                            <td class="border px-4 py-2">
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
                            <td colspan="3" class="border px-4 py-2 text-center">
                                No hay capturistas registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
