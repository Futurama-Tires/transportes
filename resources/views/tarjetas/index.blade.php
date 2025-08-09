<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Tarjetas SiVale
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Botón crear --}}
            <div class="mb-4">
                <a href="{{ route('tarjetas.create') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Nueva Tarjeta
                </a>
            </div>

            {{-- Mensajes de éxito --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabla --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full border border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="px-4 py-2 border">Número de Tarjeta</th>
                            <th class="px-4 py-2 border">NIP</th>
                            <th class="px-4 py-2 border">Fecha de Vencimiento</th>
                            <th class="px-4 py-2 border">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tarjetas as $tarjeta)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-2 border">{{ $tarjeta->numero_tarjeta }}</td>
                                <td class="px-4 py-2 border">{{ $tarjeta->nip ?? '—' }}</td>
                                <td class="px-4 py-2 border">
                                    {{ $tarjeta->fecha_vencimiento ? \Carbon\Carbon::parse($tarjeta->fecha_vencimiento)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-2 border text-center">
                                    <a href="{{ route('tarjetas.edit', $tarjeta) }}"
                                       class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                        Editar
                                    </a>
                                    <form action="{{ route('tarjetas.destroy', $tarjeta) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('¿Seguro que quieres eliminar esta tarjeta?')"
                                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-2 border text-center text-gray-500">
                                    No hay tarjetas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $tarjetas->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
