{{-- resources/views/verificaciones/index.blade.php --}}
<x-app-layout>
    {{-- Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                Gestión de Verificaciones
            </h2>
            <a href="{{ route('verificaciones.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                {{-- plus icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                </svg>
                Nueva Verificación
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900/40 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Barra de búsqueda / filtros --}}
            <form method="GET" action="{{ route('verificaciones.index') }}" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex w-full items-center rounded-full border border-slate-300 bg-white px-3 py-2 shadow-sm ring-indigo-300 focus-within:ring dark:border-slate-700 dark:bg-slate-800 sm:max-w-md">
                    {{-- search icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Buscar por estado, placa, propietario…"
                           class="w-full bg-transparent text-sm placeholder-slate-400 focus:outline-none dark:text-slate-100" />
                    @if(request('search'))
                        <a href="{{ route('verificaciones.index') }}"
                           class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                            Limpiar
                        </a>
                    @endif
                    <button type="submit"
                                class="ml-2 inline-flex items-center rounded-full bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            Buscar
                        </button>
                </div>

            </form>

            {{-- Tabla --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 dark:bg-slate-900/40 dark:text-slate-300">
                            <tr class="text-xs uppercase tracking-wide">
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Vehículo</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Estado</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Fecha</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Comentarios</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold text-right dark:border-slate-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($verificaciones as $verificacion)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-800 dark:text-slate-100">
                                        {{ $verificacion->vehiculo->unidad ?? 'N/A' }}
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $verificacion->vehiculo->propietario ?? '' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ $verificacion->estado }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ \Carbon\Carbon::parse($verificacion->fecha_verificacion)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ $verificacion->comentarios ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Editar --}}
                                            <a href="{{ route('verificaciones.edit', $verificacion) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                                                Editar
                                            </a>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('verificaciones.destroy', $verificacion) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('¿Eliminar esta verificación?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-300">
                                        @if(request('search') || request('from') || request('to'))
                                            No se encontraron resultados para los filtros aplicados.
                                            <a href="{{ route('verificaciones.index') }}" class="text-indigo-600 hover:text-indigo-800">Limpiar</a>
                                        @else
                                            No hay verificaciones registradas.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            @if($verificaciones->hasPages())
                <div class="mt-6 flex items-center justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        Mostrando
                        <span class="font-semibold">{{ $verificaciones->firstItem() }}–{{ $verificaciones->lastItem() }}</span>
                        de
                        <span class="font-semibold">{{ $verificaciones->total() }}</span>
                        resultados
                    </p>
                    {{ $verificaciones->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
