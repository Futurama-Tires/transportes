<x-app-layout>
    {{-- Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                Gestión de Capturistas
            </h2>
            <a href="{{ route('capturistas.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                {{-- plus icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                </svg>
                Agregar nuevo capturista
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
                {{-- Buscador + Filtros --}}
                <form method="GET" action="{{ route('capturistas.index') }}" class="w-full lg:w-3/4 xl:w-4/5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        {{-- Buscador --}}
                        <div class="flex w-full sm:flex-1 items-center rounded-full bg-white px-4 py-2 shadow-md ring-1 ring-gray-200 focus-within:ring dark:bg-slate-800 dark:ring-slate-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"/>
                            </svg>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Buscar..."
                                class="ml-3 w-full flex-1 border-0 bg-transparent text-sm outline-none placeholder:text-gray-400 dark:placeholder:text-slate-400"
                            />
                        </div>

                        {{-- Selects --}}
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">

                            {{-- Botón Buscar --}}
                        <button type="submit"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"/>
                            </svg>
                            Buscar
                        </button>

                            {{-- Ordenar por --}}
                            <div class="relative w-full sm:w-44">
                                <select
                                    name="sort_by"
                                    class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    onchange="this.form.submit()"
                                    title="Ordenar por"
                                >
                                    <option value="nombre_completo" @selected(request('sort_by','nombre_completo')==='nombre_completo')>Nombre completo</option>
                                    <option value="email" @selected(request('sort_by')==='email')>Correo electrónico</option>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </div>

                            {{-- Dirección --}}
                            <div class="relative w-full sm:w-36">
                                <select
                                    name="sort_dir"
                                    class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    onchange="this.form.submit()"
                                    title="Dirección"
                                >
                                    <option value="asc"  @selected(request('sort_dir','asc')==='asc')>Ascendente</option>
                                    <option value="desc" @selected(request('sort_dir')==='desc')>Descendente</option>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        
                    </div>
                </form>

                {{-- Botones de exportación (opcional / placeholder) --}}
                <div class="flex flex-wrap items-center gap-2">
                    <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400" title="Exportar a Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 2H8a2 2 0 0 0-2 2v3h6a2 2 0 0 1 2 2v9h5a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Z"/><path d="M3 9h9a1 1 0 0 1 1 1v10H5a2 2 0 0 1-2-2V9Zm6.8 7.5-.9-1.4-.9 1.4H6.1l1.6-2.4L6.1 12.7h1.9l.9 1.4.9-1.4h1.9l-1.6 2.4 1.6 2.4H9.8Z"/></svg>
                        Excel
                    </a>
                    <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-rose-300 bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400" title="Exportar a PDF">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v14a4 4 0 0 0 4 4h8a2 2 0 0 0 2-2V6l-4-4Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4M7.5 15H9a1.5 1.5 0 0 0 0-3H7.5v3Zm0 0v2m5.5-5h-1v5h1m0-3h1a2 2 0 1 0 0-4h-1v2Z"/></svg>
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
                        $total = $capturistas->total();
                        $first = $capturistas->firstItem();
                        $last  = $capturistas->lastItem();
                        $current = $capturistas->currentPage();
                        $lastPage = $capturistas->lastPage();
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
                                    Nombre completo
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">
                                    Correo electrónico
                                </th>
                                <th scope="col" class="border-b border-slate-2 00 px-4 py-3 font-semibold dark:border-slate-700">
                                    ID
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold text-right dark:border-slate-700">
                                    <span class="sr-only">Acciones</span>Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($capturistas as $capturista)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-800 dark:text-slate-100">
                                        {{ $capturista->nombre }} {{ $capturista->apellido_paterno }} {{ $capturista->apellido_materno }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ $capturista->user->email }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ $capturista->id }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Editar --}}
                                            <a href="{{ route('capturistas.edit', $capturista->id) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                                               aria-label="Editar {{ $capturista->nombre }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232 18.768 8.768M4 20l4.586-1.146a2 2 0 0 0 .894-.514l9.94-9.94a2 2 0 0 0 0-2.828l-1.792-1.792a2 2 0 0 0-2.828 0l-9.94 9.94a2 2 0 0 0-.514.894L4 20z"/>
                                                </svg>
                                                Editar
                                            </a>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('capturistas.destroy', $capturista->id) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('¿Seguro que quieres eliminar a {{ $capturista->nombre }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400"
                                                        aria-label="Eliminar {{ $capturista->nombre }}">
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
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-300">
                                        @if(request('search'))
                                            No se encontraron resultados para <span class="font-semibold">“{{ request('search') }}”</span>.
                                            <a href="{{ route('capturistas.index') }}" class="text-indigo-600 hover:text-indigo-800">Limpiar búsqueda</a>
                                        @else
                                            No hay capturistas registrados.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación + contador --}}
            @if(method_exists($capturistas, 'links'))
                <div class="mt-6 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                    @php
                        $totalAll = $capturistas->total();
                        $firstAll = $capturistas->firstItem();
                        $lastAll  = $capturistas->lastItem();
                        $currentAll = $capturistas->currentPage();
                        $lastPageAll = $capturistas->lastPage();
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
                        {{-- Conserva todos los filtros en los enlaces --}}
                        {{ $capturistas->appends([
                            'search'   => request('search'),
                            'sort_by'  => request('sort_by'),
                            'sort_dir' => request('sort_dir'),
                        ])->links() }}
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
