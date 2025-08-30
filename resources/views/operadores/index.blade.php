<x-app-layout>
    {{-- Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                Gestión de Operadores
            </h2>
            <a href="{{ route('operadores.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                {{-- plus icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                </svg>
                Agregar Operador
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Flash éxito --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900/40 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Barra superior: búsqueda + exportaciones --}}
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                {{-- Buscador (w-full en mobile, w-1/2 en desktop) --}}
                <form method="GET" action="{{ route('operadores.index') }}" class="w-full lg:w-1/2">
                    <div class="flex w-full items-center rounded-full border border-slate-300 bg-white px-3 py-2 shadow-sm ring-indigo-300 focus-within:ring dark:border-slate-700 dark:bg-slate-800">
                        {{-- search icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Buscar por nombre o correo…"
                            class="w-full bg-transparent text-sm placeholder-slate-400 focus:outline-none dark:text-slate-100"
                            aria-label="Buscar operadores"
                        />
                        @if(request('search'))
                            <a href="{{ route('operadores.index') }}"
                               class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600"
                               title="Limpiar búsqueda">
                                Limpiar
                            </a>
                        @endif
                        <button type="submit"
                                class="ml-2 inline-flex items-center rounded-full bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            Buscar
                        </button>
                    </div>
                </form>

                {{-- Botones de exportación (placeholders) --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Excel --}}
                    <a href="#"
                       class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                       title="Exportar a Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M19 2H8a2 2 0 0 0-2 2v3h6a2 2 0 0 1 2 2v9h5a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Z"/>
                            <path d="M3 9h9a1 1 0 0 1 1 1v10H5a2 2 0 0 1-2-2V9Zm6.8 7.5-.9-1.4-.9 1.4H6.1l1.6-2.4L6.1 12.7h1.9l.9 1.4.9-1.4h1.9l-1.6 2.4 1.6 2.4H9.8Z"/>
                        </svg>
                        Excel
                    </a>

                    {{-- PDF --}}
                    <a href="#"
                       class="inline-flex items-center gap-2 rounded-lg border border-rose-300 bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400"
                       title="Exportar a PDF">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v14a4 4 0 0 0 4 4h8a2 2 0 0 0 2-2V6l-4-4Z"/>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4M7.5 15H9a1.5 1.5 0 0 0 0-3H7.5v3Zm0 0v2m5.5-5h-1v5h1m0-3h1a2 2 0 1 0 0-4h-1v2Z"/>
                        </svg>
                        PDF
                    </a>
                </div>
            </div>

            {{-- Resumen (cuando hay búsqueda) --}}
            @if(request('search'))
                <div class="mb-4 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-700 dark:text-slate-100">Filtro</span>
                        <span class="font-medium">“{{ request('search') }}”</span>
                    </div>

                    @php
                        $total = $operadores->total();
                        $first = $operadores->firstItem();
                        $last  = $operadores->lastItem();
                        $current = $operadores->currentPage();
                        $lastPage = $operadores->lastPage();
                    @endphp

                    <div class="text-sm text-slate-600 dark:text-slate-300">
                        @if($total === 1)
                            Resultado <span class="font-semibold">(1 de 1)</span>
                        @elseif($total > 1)
                            Página <span class="font-semibold">{{ $current }}</span> de <span class="font-semibold">{{ $lastPage }}</span> —
                            Mostrando <span class="font-semibold">{{ $first }}–{{ $last }}</span> de <span class="font-semibold">{{ $total }}</span> resultados
                        @else
                            Sin resultados para la búsqueda.
                        @endif
                    </div>
                </div>
            @endif

            {{-- Tabla --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 dark:bg-slate-900/40 dark:text-slate-300">
                            <tr class="text-xs uppercase tracking-wide">
                                <th scope="col" class="sticky left-0 z-10 border-b border-slate-200 px-4 py-3 font-semibold bg-slate-50 dark:bg-slate-900/40 dark:border-slate-700">
                                    Nombre
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">
                                    Correo
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold text-right dark:border-slate-700">
                                    <span class="sr-only">Acciones</span>Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($operadores as $operador)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-800 dark:text-slate-100">
                                        {{ $operador->nombre }} {{ $operador->apellido_paterno }} {{ $operador->apellido_materno }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ $operador->user->email }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Editar --}}
                                            <a href="{{ route('operadores.edit', $operador->id) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                                               aria-label="Editar {{ $operador->nombre }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232 18.768 8.768M4 20l4.586-1.146a2 2 0 0 0 .894-.514l9.94-9.94a2 2 0 0 0 0-2.828l-1.792-1.792a2 2 0 0 0-2.828 0l-9.94 9.94a2 2 0 0 0-.514.894L4 20z"/>
                                                </svg>
                                                Editar
                                            </a>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('operadores.destroy', $operador->id) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('¿Seguro que quieres eliminar a {{ $operador->nombre }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400"
                                                        aria-label="Eliminar {{ $operador->nombre }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-1-3H10a1 1 0 0 0-1 1v2h8V5a1 1 0 0 0-1-1z"/>
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-slate-500 dark:text-slate-300">
                                        @if(request('search'))
                                            No se encontraron resultados para <span class="font-semibold">“{{ request('search') }}”</span>.
                                            <a href="{{ route('operadores.index') }}" class="text-indigo-600 hover:text-indigo-800">Limpiar búsqueda</a>
                                        @else
                                            No hay operadores registrados.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación + contador (siempre visible) --}}
            @if(method_exists($operadores, 'links'))
                <div class="mt-6 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                    @php
                        $totalAll = $operadores->total();
                        $firstAll = $operadores->firstItem();
                        $lastAll  = $operadores->lastItem();
                        $currentAll = $operadores->currentPage();
                        $lastPageAll = $operadores->lastPage();
                    @endphp

                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        @if($totalAll === 0)
                            Mostrando 0 resultados
                        @elseif($totalAll === 1)
                            Resultado <span class="font-semibold">(1 de 1)</span>
                        @else
                            Página <span class="font-semibold">{{ $currentAll }}</span> de <span class="font-semibold">{{ $lastPageAll }}</span> —
                            Mostrando <span class="font-semibold">{{ $firstAll }}–{{ $lastAll }}</span> de <span class="font-semibold">{{ $totalAll }}</span> resultados
                        @endif
                    </p>

                    <div class="w-full sm:w-auto">
                        {{ $operadores->appends(['search' => request('search')])->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <footer class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
        </div>
    </footer>
</x-app-layout>
