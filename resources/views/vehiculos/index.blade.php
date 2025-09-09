<x-app-layout>
    <style>[x-cloak]{display:none!important}</style>

    {{-- Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                Gestión de Vehículos
            </h2>
            <a href="{{ route('vehiculos.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                <span class="material-symbols-outlined"> add_box </span>
                Agregar Vehículo
            </a>
        </div>
    </x-slot>

    @php
        $ignored = ['search','page','sort_by','sort_dir'];
        $activeFilters = collect(request()->query())->filter(function($v,$k) use ($ignored){
            if (in_array($k,$ignored)) return false;
            if (is_array($v)) return collect($v)->filter(fn($x)=>$x!==null && $x!=='')->isNotEmpty();
            return $v !== null && $v !== '';
        });
        $activeCount = $activeFilters->count();
    @endphp

    <div class="py-8" x-data="vehiculosModal()" x-init="init()" x-cloak>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Flash éxito --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900/40 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Barra superior: búsqueda + filtros + exportaciones --}}
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                {{-- Buscador + Selects + Filtros (w-full en mobile, crece en desktop) --}}
                <form method="GET" action="{{ route('vehiculos.index') }}" class="w-full lg:w-3/4 xl:w-4/5" x-data="{open: {{ $activeCount > 0 ? 'true' : 'false' }} }">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        {{-- Buscador --}}
                        <div class="flex w-full sm:flex-1 items-center rounded-full bg-white px-4 py-2 shadow-md ring-1 ring-gray-200 focus-within:ring dark:bg-slate-800 dark:ring-slate-700">
                            <span class="material-symbols-outlined"> search </span>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Buscar por: ID, Unidad, Placa, Serie, Año, Propietario"
                                autocomplete="off"
                                class="ml-3 w-full flex-1 border-0 bg-transparent text-sm outline-none placeholder:text-gray-400 dark:placeholder:text-slate-400"
                            />
                        </div>

                        {{-- Botón Buscar --}}
                        <button type="submit"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <span class="material-symbols-outlined"> search </span>
                            Buscar
                        </button>

                        {{-- Selects & botón de Filtros --}}
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                            {{-- Ordenar por --}}
                            <div class="relative w-full sm:w-44">
                                <select
                                    name="sort_by"
                                    class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    onchange="this.form.submit()"
                                    title="Ordenar por"
                                >
                                    @php
                                        $columns = [
                                            'created_at' => 'Fecha',
                                            'id'         => 'ID',
                                            'placa'      => 'Placa',
                                            'serie'      => 'Serie',
                                            'unidad'     => 'Unidad',
                                            'marca'      => 'Marca',
                                            'anio'       => 'Año',
                                            'propietario'=> 'Propietario',
                                        ];
                                    @endphp
                                    @foreach($columns as $k => $label)
                                        <option value="{{ $k }}" @selected(request('sort_by','created_at')===$k)>{{ $label }}</option>
                                    @endforeach
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

                            {{-- Toggle Filtros --}}
                            <button type="button"
                                    @click="open = !open"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                                    title="Mostrar/Ocultar filtros">
                                <span class="material-symbols-outlined" :class="{'rotate-180': open}"> tune </span>
                                Filtros
                                @if($activeCount>0)
                                    <span class="ml-1 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
                                        {{ $activeCount }}
                                    </span>
                                @endif
                            </button>
                        </div>
                    </div>

                    {{-- Filtros avanzados (reducidos a los solicitados) --}}
                    <div x-show="open" x-transition
                         class="mt-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            {{-- ID --}}
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">ID</label>
                                <input type="number" name="id" value="{{ request('id') }}"
                                       class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>

                            {{-- Unidad --}}
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Unidad</label>
                                <input type="text" name="unidad" value="{{ request('unidad') }}"
                                       class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>

                            {{-- Placa --}}
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Placa</label>
                                <input type="text" name="placa" value="{{ request('placa') }}"
                                       class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>

                            {{-- Serie --}}
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Serie (VIN)</label>
                                <input type="text" name="serie" value="{{ request('serie') }}"
                                       class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>

                            {{-- Año (rango) --}}
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Año</label>
                                <div class="flex gap-2">
                                    <input type="number" name="anio_min" value="{{ request('anio_min') }}" placeholder="mín"
                                           class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                    <input type="number" name="anio_max" value="{{ request('anio_max') }}" placeholder="máx"
                                           class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                </div>
                            </div>

                            {{-- Propietario --}}
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Propietario</label>
                                <input type="text" name="propietario" value="{{ request('propietario') }}"
                                       class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>

                            {{-- Marca --}}
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Marca</label>
                                <select name="marca" class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                    <option value="">Todas</option>
                                    @foreach(($marcas ?? []) as $m)
                                        <option value="{{ $m }}" @selected(request('marca') == $m)>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-2">
                            <a href="{{ route('vehiculos.index') }}"
                               class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                                <span class="material-symbols-outlined"> layers_clear </span>
                                Limpiar
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <span class="material-symbols-outlined"> done_all </span>
                                Aplicar
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Botones de exportación (placeholders) --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Excel --}}
                    <a href="#"
                       class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                       title="Exportar a Excel">
                        <span class="material-symbols-outlined"> border_all </span>
                        Excel
                    </a>

                    {{-- PDF --}}
                    <a href="#"
                       class="inline-flex items-center gap-2 rounded-lg border border-rose-300 bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400"
                       title="Exportar a PDF">
                        <span class="material-symbols-outlined"> article </span>
                        PDF
                    </a>
                </div>
            </div>
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            </div>

            {{-- Resumen (cuando hay búsqueda) --}}
            @if(request('search'))
                <div class="mb-4 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-700 dark:text-slate-100">Filtro</span>
                        <span class="font-medium">“{{ request('search') }}”</span>
                    </div>

                    @php
                        $total = $vehiculos->total();
                        $first = $vehiculos->firstItem();
                        $last  = $vehiculos->lastItem();
                        $current = $vehiculos->currentPage();
                        $lastPage = $vehiculos->lastPage();
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
                                <th scope="col" class="sticky left-0 z-10 border-b border-slate-200 bg-slate-50 px-4 py-3 font-semibold dark:border-slate-700 dark:bg-slate-900/40">
                                    ID
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">
                                    Unidad
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">
                                    Placa
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">
                                    Serie
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">
                                    Año
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">
                                    Propietario
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold text-right dark:border-slate-700">
                                    <span class="sr-only">Acciones</span>Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($vehiculos as $v)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                    <td class="sticky left-0 z-[1] whitespace-nowrap border-r border-slate-100 bg-white px-4 py-3 font-medium text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                        {{ $v->id }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-800 dark:text-slate-100">{{ $v->unidad ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $v->placa ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $v->serie ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $v->anio ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $v->propietario ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Ver (abre slide-over) --}}
                                            <button type="button"
                                                    @click="showVehicle(@js($v->toArray()))"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                                                    title="Ver detalles">
                                                <span class="material-symbols-outlined"> visibility </span>
                                                Ver
                                            </button>

                                            {{-- Editar --}}
                                            <a href="{{ route('vehiculos.edit', $v) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                                               title="Editar vehículo">
                                                <span class="material-symbols-outlined"> edit </span>
                                                Editar
                                            </a>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('vehiculos.destroy', $v) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar este vehículo?')"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400"
                                                        title="Eliminar vehículo">
                                                    <span class="material-symbols-outlined"> delete </span>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500 dark:text-slate-300">
                                        @if(request('search'))
                                            No se encontraron resultados para <span class="font-semibold">“{{ request('search') }}”</span>.
                                            <a href="{{ route('vehiculos.index') }}" class="text-indigo-600 hover:text-indigo-800">Limpiar búsqueda</a>
                                        @else
                                            No hay vehículos registrados.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación + contador (siempre visible) --}}
            @if(method_exists($vehiculos, 'links'))
                <div class="mt-6 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                    @php
                        $totalAll = $vehiculos->total();
                        $firstAll = $vehiculos->firstItem();
                        $lastAll  = $vehiculos->lastItem();
                        $currentAll = $vehiculos->currentPage();
                        $lastPageAll = $vehiculos->lastPage();
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
                        {{ $vehiculos->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>

        {{-- ===== Slide-over redimensionable ===== --}}
        <div x-show="drawerOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50">
            <div
                class="absolute inset-y-0 right-0 z-50 bg-white shadow-xl dark:bg-slate-900 flex h-screen"
                :class="sizeClass()"
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                @click.away="close()"
            >
                <div class="flex h-full w-full flex-col">
                    {{-- Header slide-over --}}
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <div class="min-w-0">
                            <br><br><br>
                            <p class="truncate text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Detalles del Vehículo</p>
                            <h3 class="mt-0.5 truncate text-lg font-semibold text-slate-900 dark:text-slate-100"
                                x-text="selected?.unidad ? `Unidad: ${selected.unidad}` : `Vehículo #${selected?.id ?? ''}`"></h3>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400" x-text="selected?.placa ? `Placa: ${selected.placa}` : ''"></p>
                        </div>

                    </div>

                    {{-- Contenido scrollable --}}
                    <div class="flex-1 overflow-y-auto p-5">
                        {{-- Datos del vehículo --}}
                        <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-800">
                            <div class="border-b border-slate-200 px-5 py-3 dark:border-slate-700">
                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Datos generales</h4>
                            </div>
                            <div class="grid grid-cols-1 gap-4 px-5 py-4 md:grid-cols-2">
                                <div><p class="text-xs text-slate-500">Id</p><p class="font-medium" x-text="selected?.id ?? '—'"></p></div>
                                <div><p class="text-xs text-slate-500">Unidad</p><p class="font-medium" x-text="fmt(selected.unidad)"></p></div>
                                <div><p class="text-xs text-slate-500">Placa</p><p class="font-medium" x-text="fmt(selected.placa)"></p></div>
                                <div><p class="text-xs text-slate-500">Serie (VIN)</p><p class="font-medium" x-text="fmt(selected.serie)"></p></div>
                                <div><p class="text-xs text-slate-500">Marca</p><p class="font-medium" x-text="fmt(selected.marca)"></p></div>
                                <div><p class="text-xs text-slate-500">Año</p><p class="font-medium" x-text="fmt(selected.anio)"></p></div>
                                <div><p class="text-xs text-slate-500">Propietario</p><p class="font-medium" x-text="fmt(selected.propietario)"></p></div>
                                <div><p class="text-xs text-slate-500">Ubicación</p><p class="font-medium" x-text="fmt(selected.ubicacion)"></p></div>
                                <div><p class="text-xs text-slate-500">Estado</p><p class="font-medium" x-text="fmt(selected.estado)"></p></div>
                                <div><p class="text-xs text-slate-500">Motor</p><p class="font-medium" x-text="fmt(selected.motor)"></p></div>
                                <div><p class="text-xs text-slate-500">Tarjeta SiVale</p>
                                     <p class="font-medium" x-text="selected?.tarjeta_si_vale?.numero_tarjeta ?? (selected?.tarjeta_si_vale_id ?? '—')"></p>
                                </div>
                                <div><p class="text-xs text-slate-500">NIP</p><p class="font-medium" x-text="fmt(selected.nip)"></p></div>
                                <div><p class="text-xs text-slate-500">Venc. tarjeta</p><p class="font-medium" x-text="fmtDate(selected.fec_vencimiento)"></p></div>
                                <div><p class="text-xs text-slate-500">Venc. circ.</p><p class="font-medium" x-text="fmtDate(selected.vencimiento_t_circulacion)"></p></div>
                                <div><p class="text-xs text-slate-500">Cambio de placas</p><p class="font-medium" x-text="fmtDate(selected.cambio_placas)"></p></div>
                                <div class="md:col-span-2"><p class="text-xs text-slate-500">Póliza HDI</p><p class="font-medium" x-text="fmt(selected.poliza_hdi)"></p></div>
                            </div>
                        </div>

                        {{-- FOTOS DEL VEHÍCULO --}}
                        <div class="mt-5 rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-800" x-data>
                            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-slate-700">
                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Fotos del vehículo</h4>
                                <div class="flex items-center gap-2">
                                    <template x-if="selected?.fotos?.length">
                                        <button @click="openGallery(0)"
                                                class="inline-flex items-center gap-1 rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800">
                                            <span class="material-symbols-outlined"> slideshow </span>
                                            Ver galería
                                        </button>
                                    </template>
                                    <a :href="selected ? `{{ url('/vehiculos') }}/${selected.id}/fotos` : '#'"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                        <span class="material-symbols-outlined"> imagesmode </span>
                                        Gestionar fotos
                                    </a>
                                </div>
                            </div>

                            <div class="px-5 py-4">
                                <template x-if="!selected?.fotos || selected.fotos.length === 0">
                                    <p class="text-sm text-slate-500">Este vehículo no tiene fotos.</p>
                                </template>

                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4" x-show="selected?.fotos?.length">
                                    <template x-for="(f,i) in selected.fotos" :key="f.id">
                                        <button type="button"
                                                class="group relative rounded-lg border border-slate-200 p-1 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700/30"
                                                @click="openGallery(i)">
                                            <img :src="photoSrc(f)" alt="Foto del vehículo"
                                                 class="h-32 w-full rounded object-cover transition group-hover:opacity-90 cursor-zoom-in">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Tanques --}}
                        <div class="mt-5 rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-800">
                            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-slate-700">
                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Tanques de combustible</h4>
                                <a :href="selected ? `{{ url('/vehiculos') }}/${selected.id}/tanques/create` : '#'"
                                   class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                                    <span class="material-symbols-outlined"> add_circle </span>
                                    Agregar
                                </a>
                            </div>

                            <div class="overflow-x-auto px-5 py-4">
                                <table class="min-w-[700px] w-full text-sm">
                                    <thead class="text-left text-xs uppercase text-slate-500">
                                        <tr>
                                            <th class="py-2">#</th>
                                            <th class="py-2">Tipo</th>
                                            <th class="py-2">Capacidad (L)</th>
                                            <th class="py-2">Rend. (km/L)</th>
                                            <th class="py-2">Km recorre</th>
                                            <th class="py-2">Costo tanque lleno</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                        <template x-if="!selected?.tanques || selected.tanques.length === 0">
                                            <tr><td colspan="6" class="py-4 text-center text-slate-500">Este vehículo no tiene tanques.</td></tr>
                                        </template>
                                        <template x-for="t in selected?.tanques ?? []" :key="t.id">
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                                <td class="py-2" x-text="t.numero_tanque ?? '—'"></td>
                                                <td class="py-2" x-text="fmt(t.tipo_combustible)"></td>
                                                <td class="py-2" x-text="fmtNum(t.capacidad_litros)"></td>
                                                <td class="py-2" x-text="fmtNum(t.rendimiento_estimado)"></td>
                                                <td class="py-2" x-text="fmtNum(t.km_recorre)"></td>
                                                <td class="py-2" x-text="fmtMoney(t.costo_tanque_lleno)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Lightbox global para fotos ===== --}}
        <div
            x-cloak
            x-show="galleryOpen"
            x-transition.opacity
            class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 p-4"
            @click.self="galleryOpen=false"
            {{-- Importante: no bloqueamos escritura en inputs fuera del lightbox --}}
            @keydown.window="if (galleryOpen) {
                if ($event.key==='Escape') { galleryOpen=false; }
                else if ($event.key==='ArrowRight') { nextPhoto(); }
                else if ($event.key==='ArrowLeft') { prevPhoto(); }
                $event.preventDefault(); $event.stopPropagation();
            }"
            role="dialog" aria-modal="true"
        >
            <div class="relative max-h-[90vh] w-full max-w-6xl">
                {{-- Cerrar --}}
                <button
                    @click="galleryOpen=false"
                    class="absolute -top-10 right-0 inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1.5 text-sm font-medium text-slate-700 shadow hover:bg-white"
                    aria-label="Cerrar">
                    <span class="material-symbols-outlined"> close </span>
                    Cerrar
                </button>

                {{-- Prev --}}
                <button
                    @click.stop="prevPhoto()"
                    class="absolute left-0 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow hover:bg-white"
                    aria-label="Anterior">
                    <span class="material-symbols-outlined"> chevron_left </span>
                </button>

                {{-- Next --}}
                <button
                    @click.stop="nextPhoto()"
                    class="absolute right-0 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow hover:bg-white"
                    aria-label="Siguiente">
                    <span class="material-symbols-outlined"> chevron_right </span>
                </button>

                <div class="flex justify-center">
                    <img
                        :src="currentPhotoSrc()"
                        :alt="`Foto ${galleryIndex+1} de ${selected?.fotos?.length ?? 0}`"
                        class="max-h-[85vh] w-auto rounded-lg object-contain select-none"
                        draggable="false">
                </div>

                <div class="mt-3 text-center text-sm text-white/90">
                    <span x-text="(galleryIndex+1) + ' / ' + (selected?.fotos?.length ?? 0)"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine helpers --}}
    <script>
    function vehiculosModal() {
        return {
            open: false,
            drawerOpen: false,
            selected: null,

            // Lightbox
            galleryOpen: false,
            galleryIndex: 0,

            // Rutas base para servir imágenes privadas
            basePhotosUrl: "{{ url('/vehiculos/fotos') }}",

            // Tamaños disponibles del slide-over
            sizes: ['sm','md','lg','xl','full'],
            size: 'lg', // tamaño por defecto

            init() {},

            // Clases tailwind para cada tamaño
            sizeClass() {
                switch (this.size) {
                    case 'sm':   return 'w-full max-w-md';
                    case 'md':   return 'w-full max-w-2xl';
                    case 'lg':   return 'w-full max-w-3xl';
                    case 'xl':   return 'w-full max-w-5xl';
                    case 'full': return 'w-screen max-w-none';
                }
            },
            expand() {
                const i = this.sizes.indexOf(this.size);
                if (i < this.sizes.length - 1) this.size = this.sizes[i + 1];
            },
            shrink() {
                const i = this.sizes.indexOf(this.size);
                if (i > 0) this.size = this.sizes[i - 1];
            },
            toggleFull() {
                this.size = this.size === 'full' ? 'xl' : 'full';
            },

            fmt(v) { return (v ?? '') !== '' ? v : '—'; },
            fmtDate(v) {
                if(!v) return '—';
                const d = new Date(v);
                if (isNaN(d)) return v;
                return d.toLocaleDateString('es-MX', { year:'numeric', month:'2-digit', day:'2-digit' });
            },
            fmtNum(n) {
                if (n === null || n === undefined || n === '') return '—';
                const num = Number(n);
                if (isNaN(num)) return '—';
                return num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            fmtMoney(n) {
                if (n === null || n === undefined || n === '') return '—';
                const num = Number(n);
                if (isNaN(num)) return '—';
                return num.toLocaleString('es-MX', { style:'currency', currency:'MXN' });
            },

            // --- Fotos ---
            photoSrc(foto) {
                const id = (typeof foto === 'object') ? foto.id : foto;
                return `${this.basePhotosUrl}/${id}`;
            },
            openGallery(i = 0) {
                if (!this.selected?.fotos || this.selected.fotos.length === 0) return;
                this.galleryIndex = Math.max(0, Math.min(i, this.selected.fotos.length - 1));
                this.galleryOpen = true;
            },
            currentPhotoSrc() {
                if (!this.selected?.fotos || this.selected.fotos.length === 0) return '';
                const f = this.selected.fotos[this.galleryIndex];
                return this.photoSrc(f);
            },
            nextPhoto() {
                if (!this.selected?.fotos || this.selected.fotos.length === 0) return;
                this.galleryIndex = (this.galleryIndex + 1) % this.selected.fotos.length;
            },
            prevPhoto() {
                if (!this.selected?.fotos || this.selected.fotos.length === 0) return;
                this.galleryIndex = (this.galleryIndex - 1 + this.selected.fotos.length) % this.selected.fotos.length;
            },

            // --- Modal principal ---
            showVehicle(v) {
                this.selected = v || {};
                if (!Array.isArray(this.selected.fotos)) this.selected.fotos = [];
                if (!Array.isArray(this.selected.tanques)) this.selected.tanques = [];
                this.drawerOpen = true;
            },
            close() {
                this.drawerOpen = false;
                this.galleryOpen = false;
                this.selected = null;
            }
        }
    }
    </script>

    {{-- Footer --}}
    <footer class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
        </div>
    </footer>
</x-app-layout>
