{{-- resources/views/vehiculos/index.blade.php --}}
<x-app-layout>
    {{-- Optional: ocultar FOUC en Alpine --}}
    <style>[x-cloak]{display:none!important}</style>

    {{-- Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                Gestión de Vehículos
            </h2>
            <a href="{{ route('vehiculos.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                {{-- plus icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                </svg>
                Agregar Vehículo
            </a>
        </div>
    </x-slot>

    @php
        // Contar filtros activos (excluyendo búsqueda, orden y paginación)
        $ignored = ['search','page','sort_by','sort_dir'];
        $activeFilters = collect(request()->query())->filter(function($v,$k) use ($ignored){
            if (in_array($k,$ignored)) return false;
            if (is_array($v)) return collect($v)->filter(fn($x)=>$x!==null && $x!=='')->isNotEmpty();
            return $v !== null && $v !== '';
        });
        $activeCount = $activeFilters->count();
    @endphp

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800 ring-1 ring-green-300">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Form único: búsqueda + orden + filtros (colapsables) --}}
            <form method="GET" action="{{ route('vehiculos.index') }}"
                  x-data="{open: {{ $activeCount > 0 ? 'true' : 'false' }} }" x-cloak>
                {{-- Barra superior compacta --}}
                <div class="mb-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    {{-- Búsqueda global --}}
                    <div class="flex w-full items-center rounded-lg bg-white px-3 py-2 ring-1 ring-slate-200 focus-within:ring-indigo-400 dark:bg-slate-900 dark:ring-slate-700 md:max-w-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 3.5a7.5 7.5 0 0013.65 13.65z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Buscar: Placa, serie, unidad, propietario, marca, año, ubicación, etc."
                               class="block w-full bg-transparent text-sm outline-none placeholder:text-slate-400"/>
                    </div>

                    {{-- Orden compacto --}}
                    <div class="flex items-center gap-2">
                        <select name="sort_by" class="rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            @php
                                $columns = [
                                    'created_at' => 'Fecha',
                                    'placa' => 'Placa',
                                    'serie' => 'Serie',
                                    'unidad' => 'Unidad',
                                    'marca' => 'Marca',
                                    'anio' => 'Año',
                                    'propietario' => 'Propietario',
                                    'ubicacion' => 'Ubicación',
                                    'estado' => 'Estado',
                                    'rend' => 'Rendimiento',
                                ];
                            @endphp
                            @foreach($columns as $k => $label)
                                <option value="{{ $k }}" @selected(request('sort_by', 'created_at') == $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="sort_dir"
                                class="rounded-lg border border-slate-200 bg-white p-2 pr-6 text-sm dark:border-slate-700 dark:bg-slate-900">
                            <option value="desc" @selected(request('sort_dir','desc')=='desc')>Desc</option>
                            <option value="asc"  @selected(request('sort_dir')=='asc')>Asc</option>
                        </select>


                        {{-- Botón para mostrar/ocultar filtros avanzados --}}
                        <button type="button"
                                @click="open = !open"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" :class="{'rotate-180': open}" class="h-4 w-4 transition-transform"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11l3.71-3.77a.75.75 0 111.08 1.04l-4.25 4.33a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                            Filtros
                            @if($activeCount>0)
                                <span class="ml-1 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
                                    {{ $activeCount }}
                                </span>
                            @endif
                        </button>

                        {{-- Acciones rápidas --}}
                        <a href="{{ route('vehiculos.index') }}"
                           class="hidden md:inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                            Limpiar
                        </a>
                        <button type="submit"
                                class="hidden md:inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            Aplicar
                        </button>
                    </div>
                </div>

                {{-- Panel colapsable de filtros avanzados --}}
                <div x-show="open" x-transition
                     class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        {{-- Ubicación (multi) --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Ubicación</label>
                            <select name="ubicacion[]" multiple
                                    class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                @foreach(($ubicaciones ?? []) as $u)
                                    <option value="{{ $u }}" @if(collect(request('ubicacion'))->contains($u)) selected @endif>{{ $u }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-400">Ctrl/Cmd + click para seleccionar varias</p>
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

                        {{-- Estado --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Estado</label>
                            <select name="estado" class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <option value="">Todos</option>
                                @foreach(($estados ?? []) as $e)
                                    <option value="{{ $e }}" @selected(request('estado') == $e)>{{ $e }}</option>
                                @endforeach
                            </select>
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

                        {{-- Tarjeta SiVale --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Tarjeta SiVale</label>
                            <select name="tarjeta" class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <option value="">Todas</option>
                                <option value="con" @selected(request('tarjeta')==='con')>Con tarjeta</option>
                                <option value="sin" @selected(request('tarjeta')==='sin')>Sin tarjeta</option>
                            </select>
                        </div>

                        {{-- Póliza HDI --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Póliza HDI</label>
                            <select name="poliza" class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <option value="">Todas</option>
                                <option value="con" @selected(request('poliza')==='con')>Con póliza</option>
                                <option value="sin" @selected(request('poliza')==='sin')>Sin póliza</option>
                            </select>
                        </div>

                    {{-- Vencimientos --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Venc. tarjeta (de → hasta)</label>
                            <div class="flex gap-2">
                                <input type="date" name="fec_vencimiento_desde" value="{{ request('fec_vencimiento_desde') }}"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <input type="date" name="fec_vencimiento_hasta" value="{{ request('fec_vencimiento_hasta') }}"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Venc. circulación (de → hasta)</label>
                            <div class="flex gap-2">
                                <input type="date" name="vtc_desde" value="{{ request('vtc_desde') }}"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <input type="date" name="vtc_hasta" value="{{ request('vtc_hasta') }}"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>
                        </div>

                        {{-- Rendimiento --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Rendimiento (km/L)</label>
                            <div class="flex gap-2">
                                <input type="number" step="0.01" name="rend_min" value="{{ request('rend_min') }}" placeholder="mín"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <input type="number" step="0.01" name="rend_max" value="{{ request('rend_max') }}" placeholder="máx"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>
                        </div>

                        {{-- Fechas de creación --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Creado (de → hasta)</label>
                            <div class="flex gap-2">
                                <input type="date" name="created_from" value="{{ request('created_from') }}"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <input type="date" name="created_to" value="{{ request('created_to') }}"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2">
                        <a href="{{ route('vehiculos.index') }}"
                           class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                            Limpiar
                        </a>
                        <button type="submit"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            Aplicar
                        </button>
                    </div>
                </div>
            </form>

            {{-- Tabla --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-[1200px] w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                            <tr>
                                <th class="px-4 py-3">Unidad</th>
                                <th class="px-4 py-3">Placa</th>
                                <th class="px-4 py-3">Serie</th>
                                <th class="px-4 py-3">Marca</th>
                                <th class="px-4 py-3">Año</th>
                                <th class="px-4 py-3">Propietario</th>
                                <th class="px-4 py-3">Ubicación</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3">Tarjeta</th>
                                <th class="px-4 py-3">Venc. Tarjeta</th>
                                <th class="px-4 py-3">Rend (km/L)</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm dark:divide-slate-700 dark:bg-slate-800 dark:text-slate-100">
                            @forelse($vehiculos as $v)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="px-4 py-3 font-medium">{{ $v->unidad ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->placa ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->serie ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->marca ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->anio ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->propietario ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->ubicacion ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->estado ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($v->tarjeta_si_vale_id)
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">Asignada #{{ $v->tarjeta_si_vale_id }}</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300">Sin tarjeta</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $v->fec_vencimiento ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->rend !== null ? number_format($v->rend, 2) : '—' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('vehiculos.edit', $v) }}"
                                               class="inline-flex items-center rounded-full bg-indigo-600 px-2.5 py-1 text-xs font-medium text-white shadow hover:bg-indigo-700">
                                                Editar
                                            </a>
                                            <form action="{{ route('vehiculos.destroy', $v) }}" method="POST" onsubmit="return confirm('¿Eliminar este vehículo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center rounded-full bg-rose-600 px-2.5 py-1 text-xs font-medium text-white shadow hover:bg-rose-700">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="px-4 py-6 text-center text-slate-500">No hay vehículos que coincidan con el criterio.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            <div class="mt-6 flex justify-center">
                {{ $vehiculos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
