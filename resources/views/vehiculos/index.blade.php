{{-- resources/views/vehiculos/index.blade.php --}}
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                </svg>
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

    {{-- Alpine root --}}
    <div class="py-6" x-data="vehiculosModal()" x-init="init()" x-cloak>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Flash success --}}
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800 ring-1 ring-green-300">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Search + Sort + Filters --}}
            <form method="GET" action="{{ route('vehiculos.index') }}" x-data="{open: {{ $activeCount > 0 ? 'true' : 'false' }} }" x-cloak>
                <div class="mb-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    {{-- Búsqueda --}}
                    <div class="flex w-full items-center rounded-lg bg-white px-3 py-2 ring-1 ring-slate-200 focus-within:ring-indigo-400 dark:bg-slate-900 dark:ring-slate-700 md:max-w-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 3.5a7.5 7.5 0 0013.65 13.65z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Buscar: Placa, serie, unidad, propietario, marca, año, ubicación, etc."
                               class="block w-full bg-transparent text-sm outline-none placeholder:text-slate-400"/>
                    </div>

                    {{-- Orden --}}
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

                {{-- Filtros avanzados --}}
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

                        {{-- Venc. tarjeta (rango) --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Venc. tarjeta (de → hasta)</label>
                            <div class="flex gap-2">
                                <input type="date" name="fec_vencimiento_desde" value="{{ request('fec_vencimiento_desde') }}"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <input type="date" name="fec_vencimiento_hasta" value="{{ request('fec_vencimiento_hasta') }}"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>
                        </div>

                        {{-- Venc. circulación (rango) --}}
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-600 dark:text-slate-300">Venc. circulación (de → hasta)</label>
                            <div class="flex gap-2">
                                <input type="date" name="vtc_desde" value="{{ request('vtc_desde') }}"
                                    class="w-1/2 rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <input type="date" name="vtc_hasta" value="{{ request('vtc_hasta') }}"
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
                    <table class="min-w-[1000px] w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                            <tr>
                                <th class="px-4 py-3">Id</th>
                                <th class="px-4 py-3">Unidad</th>
                                <th class="px-4 py-3">Placa</th>
                                <th class="px-4 py-3">Serie</th>
                                <th class="px-4 py-3">Año</th>
                                <th class="px-4 py-3">Propietario</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm dark:divide-slate-700 dark:bg-slate-800 dark:text-slate-100">
                            @forelse($vehiculos as $v)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="px-4 py-3 font-medium">{{ $v->id }}</td>
                                    <td class="px-4 py-3">{{ $v->unidad ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->placa ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->serie ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->anio ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $v->propietario ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- VER -> abre modal con datos + tanques + fotos --}}
                                            <button type="button"
                                                    @click="showVehicle(@js($v->toArray()))"
                                                    class="inline-flex items-center rounded-full bg-slate-700 px-2.5 py-1 text-xs font-medium text-white shadow hover:bg-slate-800">
                                                Ver
                                            </button>

                                            {{-- EDITAR --}}
                                            <a href="{{ route('vehiculos.edit', $v) }}"
                                               class="inline-flex items-center rounded-full bg-indigo-600 px-2.5 py-1 text-xs font-medium text-white shadow hover:bg-indigo-700">
                                                Editar
                                            </a>

                                            {{-- ELIMINAR --}}
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
                                    <td colspan="7" class="px-4 py-6 text-center text-slate-500">No hay vehículos que coincidan con el criterio.</td>
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
                {{-- Estructura interna en columna: header fijo + contenido scrollable --}}
                <div class="flex h-full w-full flex-col">

                    {{-- Header slide-over --}}
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <div class="min-w-0">
                            <p class="truncate text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Detalle de Vehículo</p>
                            <h3 class="mt-0.5 truncate text-lg font-semibold text-slate-900 dark:text-slate-100"
                                x-text="selected?.unidad ? `Unidad: ${selected.unidad}` : `Vehículo #${selected?.id ?? ''}`"></h3>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400" x-text="selected?.placa ? `Placa: ${selected.placa}` : ''"></p>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Redimensionar --}}
                            <button @click="shrink()"
                                    class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                                    title="Más chico">−</button>
                            <button @click="expand()"
                                    class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                                    title="Más ancho">+</button>
                            <button @click="toggleFull()
                                    "
                                    class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                                    x-text="size === 'full' ? 'Salir pantalla completa' : 'Pantalla completa'"></button>

                            {{-- Acciones rápidas --}}
                            <a :href="selected ? `{{ url('/vehiculos') }}/${selected.id}/edit` : '#'"
                               class="hidden sm:inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                                Editar vehículo
                            </a>
                            <a :href="selected ? `{{ url('/vehiculos') }}/${selected.id}/tanques` : '#'"
                               class="hidden sm:inline-flex items-center rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600">
                                Editar tanques
                            </a>
                            <a :href="selected ? `{{ url('/vehiculos') }}/${selected.id}/fotos` : '#'"
                               class="hidden sm:inline-flex items-center rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800">
                                Gestor de fotos
                            </a>
                            <button @click="close()"
                                    class="inline-flex items-center rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                Cerrar
                            </button>
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
                        <div class="mt-5 rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-800"
                             x-data>
                            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-slate-700">
                                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Fotos del vehículo</h4>
                                <div class="flex items-center gap-2">
                                    <template x-if="selected?.fotos?.length">
                                        <button @click="openGallery(0)"
                                                class="inline-flex items-center rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800">
                                            Ver galería
                                        </button>
                                    </template>
                                    <a :href="selected ? `{{ url('/vehiculos') }}/${selected.id}/fotos` : '#'"
                                       class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
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
                                        <button type="button" class="group relative rounded-lg border border-slate-200 p-1 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700/30"
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
                                   class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                                    + Agregar
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

        {{-- ===== Lightbox global para fotos (sobre todo) ===== --}}
        <div
            x-cloak
            x-show="galleryOpen"
            x-transition.opacity
            class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 p-4"
            @click.self="galleryOpen=false"
            @keydown.window.prevent.stop="
                if(!galleryOpen) return;
                if($event.key==='Escape') galleryOpen=false;
                if($event.key==='ArrowRight') nextPhoto();
                if($event.key==='ArrowLeft')  prevPhoto();
            "
            role="dialog" aria-modal="true"
        >
            <div class="relative max-h-[90vh] w-full max-w-6xl">
                {{-- Cerrar --}}
                <button
                    @click="galleryOpen=false"
                    class="absolute -top-10 right-0 inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1.5 text-sm font-medium text-slate-700 shadow hover:bg-white"
                    aria-label="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                    Cerrar
                </button>

                {{-- Prev --}}
                <button
                    @click.stop="prevPhoto()"
                    class="absolute left-0 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow hover:bg-white"
                    aria-label="Anterior">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Next --}}
                <button
                    @click.stop="nextPhoto()"
                    class="absolute right-0 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow hover:bg-white"
                    aria-label="Siguiente">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                    </svg>
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
            // acepta objeto {id,...} o id numérico
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
            // v proviene de $v->toArray() e incluye relaciones cargadas con ->with(['tanques','fotos','tarjetaSiVale'])
            this.selected = v || {};
            if (!Array.isArray(this.selected.fotos)) this.selected.fotos = []; // fallback seguro
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

</x-app-layout>
