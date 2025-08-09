<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Tarjetas SiVale
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="mb-4 text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Botón agregar --}}
            <a href="{{ route('tarjetas.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4 inline-block">
               + Nueva Tarjeta
            </a>

            {{-- Tabla --}}
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="border px-4 py-2">Número de Tarjeta</th>
                        <th class="border px-4 py-2">NIP</th>
                        <th class="border px-4 py-2">Fecha de Vencimiento</th>
                        <th class="border px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tarjetas as $tarjeta)
                        <tr>
                            <td class="border px-4 py-2">{{ $tarjeta->numero_tarjeta }}</td>
                            <td class="border px-4 py-2">{{ $tarjeta->nip ?? '—' }}</td>
                            <td class="border px-4 py-2">
                                {{ $tarjeta->fecha_vencimiento ? \Carbon\Carbon::parse($tarjeta->fecha_vencimiento)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="border px-4 py-2">
                                <a href="{{ route('tarjetas.edit', $tarjeta) }}" class="text-blue-500 hover:underline">
                                    Editar
                                </a>
                                <form action="{{ route('tarjetas.destroy', $tarjeta) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('¿Seguro que quieres eliminar esta tarjeta?')">
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
                            <td colspan="4" class="border px-4 py-2 text-center">
                                No hay tarjetas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $tarjetas->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
