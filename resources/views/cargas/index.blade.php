{{-- resources/views/cargas_combustible/index.blade.php --}}
<x-app-layout>
    {{-- Evitar FOUC en Alpine --}}
    <style>[x-cloak]{display:none!important}</style>

    {{-- Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                Cargas de Combustible
            </h2>
            <a href="{{ route('cargas.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                {{-- plus icon --}}
                <span class="material-symbols-outlined"> add_box </span>
                Nueva Carga
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

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Flash éxito --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900/40 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('success') }}
                </div>
            @endif

            {{-- FORM ÚNICO: búsqueda + botones + panel de filtros (colapsable hacia ABAJO) --}}
            <form method="GET" action="{{ route('cargas.index') }}"
                  x-data="{ open: {{ $activeCount>0 ? 'true' : 'false' }} }" x-cloak>
                {{-- Barra superior --}}
                <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    {{-- IZQUIERDA: Búsqueda + Buscar --}}
                    <div class="flex w-full items-center gap-2">
                        {{-- Búsqueda global --}}
                        <div class="flex w-full items-center rounded-full bg-white px-4 py-2 shadow-md ring-1 ring-gray-200 focus-within:ring dark:bg-slate-800 dark:ring-slate-700">
                            <span class="material-symbols-outlined text-gray-400">search</span>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Buscar por ID, vehículo, operador, ubicación, tipo, destino, observaciones, fecha…"
                                class="ml-3 w-full flex-1 border-0 bg-transparent text-sm outline-none placeholder:text-gray-400 dark:placeholder:text-slate-400"
                                aria-label="Búsqueda global"
                            />
                        </div>

                        {{-- Botón Buscar --}}
                        <button type="submit"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <span class="material-symbols-outlined">search</span>
                            Buscar
                        </button>
                    </div>

                    {{-- DERECHA: Excel, PDF y botón Filtros --}}
                    <div class="flex flex-wrap items-center gap-2">
                        {{-- Excel --}}
                        <a href="#"
                           class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                           title="Exportar a Excel">
                            <span class="material-symbols-outlined">border_all</span>
                            Excel
                        </a>

                        {{-- PDF --}}
                        <a href="#"
                           class="inline-flex items-center gap-2 rounded-lg border border-rose-300 bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400"
                           title="Exportar a PDF">
                            <span class="material-symbols-outlined">article</span>
                            PDF
                        </a>

                        {{-- Botón filtros --}}
                        <button type="button"
                                @click="open = !open"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                            <span class="material-symbols-outlined" :class="open ? 'rotate-180 transition-transform' : ''">tune</span>
                            Filtros
                            @if($activeCount>0)
                                <span class="ml-1 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
                                    {{ $activeCount }}
                                </span>
                            @endif
                        </button>
                    </div>
                </div>

                {{-- Resumen (cuando hay búsqueda) --}}
                @if(request('search'))
                    @php
                        $total = $cargas->total();
                        $first = $cargas->firstItem();
                        $last  = $cargas->lastItem();
                        $current = $cargas->currentPage();
                        $lastPage = $cargas->lastPage();
                    @endphp
                    <div class="mb-4 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-700 dark:text-slate-100">Filtro</span>
                            <span class="font-medium">“{{ request('search') }}”</span>
                        </div>

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

                {{-- PANEL DE FILTROS (debajo) --}}
                <div x-show="open" x-transition
                     class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="space-y-4">
                        {{-- Grupo: Principales --}}
                        <div>
                            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Principales</h4>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                {{-- Vehículo --}}
                                <div class="relative">
                                    <select name="vehiculo_id"
                                            class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                            title="Vehículo">
                                        <option value="">Todos los vehículos</option>
                                        @foreach($vehiculos as $v)
                                            <option value="{{ $v->id }}" @selected((string)$v->id === request('vehiculo_id'))>
                                                {{ $v->unidad }} @if($v->placa) — {{ $v->placa }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </div>

                                {{-- Operador --}}
                                <div class="relative">
                                    <select name="operador_id"
                                            class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                            title="Operador">
                                        <option value="">Todos los operadores</option>
                                        @foreach($operadores as $o)
                                            @php
                                                $nombreCompleto = trim(($o->nombre ?? '').' '.($o->apellido_paterno ?? '').' '.($o->apellido_materno ?? ''));
                                            @endphp
                                            <option value="{{ $o->id }}" @selected((string)$o->id === request('operador_id'))>
                                                {{ $nombreCompleto ?: 'Operador #'.$o->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </div>

                                {{-- Ubicación --}}
                                <div class="relative">
                                    <select name="ubicacion"
                                            class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                            title="Ubicación">
                                        <option value="">Todas las ubicaciones</option>
                                        @foreach($ubicaciones as $u)
                                            <option value="{{ $u }}" @selected($u === request('ubicacion'))>{{ $u }}</option>
                                        @endforeach
                                    </select>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </div>

                                {{-- Tipo de combustible --}}
                                <div class="relative">
                                    <select name="tipo_combustible"
                                            class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                            title="Tipo de combustible">
                                        <option value="">Todos los tipos</option>
                                        @foreach($tipos as $t)
                                            <option value="{{ $t }}" @selected($t === request('tipo_combustible'))>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Grupo: Fecha y orden --}}
                        <div>
                            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Fecha y orden</h4>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                {{-- Desde / Hasta --}}
                                <input type="date" name="from" value="{{ request('from') }}"
                                       class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" title="Desde" />
                                <input type="date" name="to" value="{{ request('to') }}"
                                       class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" title="Hasta" />

                                {{-- Ordenar por --}}
                                <div class="relative">
                                    <select name="sort_by"
                                            class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                            title="Ordenar por">
                                        @php
                                            $opts = [
                                                'fecha' => 'Fecha',
                                                'vehiculo' => 'Vehículo',
                                                'placa' => 'Placa',
                                                'operador' => 'Operador',
                                                'ubicacion' => 'Ubicación',
                                                'tipo_combustible' => 'Tipo',
                                                'litros' => 'Litros',
                                                'precio' => 'Precio',
                                                'total' => 'Total',
                                                'rendimiento' => 'Rendimiento',
                                                'km_inicial' => 'KM Inicial',
                                                'km_final' => 'KM Final',
                                                'id' => 'ID',
                                            ];
                                        @endphp
                                        @foreach($opts as $val => $label)
                                            <option value="{{ $val }}" @selected(request('sort_by','fecha')===$val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </div>

                                {{-- Dirección --}}
                                <div class="relative">
                                    <select name="sort_dir"
                                            class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                            title="Dirección">
                                        <option value="asc"  @selected(request('sort_dir','desc')==='asc')>Asc</option>
                                        <option value="desc" @selected(request('sort_dir','desc')==='desc')>Desc</option>
                                    </select>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Grupo: Métricas numéricas --}}
                        <div>
                            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Métricas</h4>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                                <input type="number" step="0.001" name="litros_min" value="{{ request('litros_min') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Litros mín">
                                <input type="number" step="0.001" name="litros_max" value="{{ request('litros_max') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Litros máx">

                                <input type="number" step="0.01" name="precio_min" value="{{ request('precio_min') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Precio mín">
                                <input type="number" step="0.01" name="precio_max" value="{{ request('precio_max') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Precio máx">

                                <input type="number" step="0.01" name="total_min" value="{{ request('total_min') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Total mín">
                                <input type="number" step="0.01" name="total_max" value="{{ request('total_max') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Total máx">

                                <input type="number" step="0.01" name="rend_min" value="{{ request('rend_min') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Rend mín">
                                <input type="number" step="0.01" name="rend_max" value="{{ request('rend_max') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Rend máx">

                                <input type="number" step="1" name="km_ini_min" value="{{ request('km_ini_min') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="KM inicial mín">
                                <input type="number" step="1" name="km_ini_max" value="{{ request('km_ini_max') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="KM inicial máx">

                                <input type="number" step="1" name="km_fin_min" value="{{ request('km_fin_min') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="KM final mín">
                                <input type="number" step="1" name="km_fin_max" value="{{ request('km_fin_max') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="KM final máx">
                            </div>
                        </div>

                        {{-- Grupo: Texto libre --}}
                        <div>
                            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Campos de texto</h4>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                <input type="text" name="destino" value="{{ request('destino') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Destino (contiene)">
                                <input type="text" name="custodio" value="{{ request('custodio') }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Custodio (contiene)">
                            </div>
                        </div>

                        {{-- Acciones panel --}}
                        <div class="mt-2 flex items-center justify-between">
                            <a href="{{ route('cargas.index') }}"
                               class="text-sm text-slate-600 underline hover:text-slate-800 dark:text-slate-300 dark:hover:text-white">
                                Limpiar filtros
                            </a>
                            <button type="submit"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <span class="material-symbols-outlined">filter_alt</span>
                                Aplicar filtros
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Tabla (TODOS LOS CAMPOS) --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 dark:bg-slate-900/40 dark:text-slate-300">
                            <tr class="text-xs uppercase tracking-wide">
                                <th class="sticky left-0 z-10 border-b border-slate-200 bg-slate-50 px-4 py-3 font-semibold dark:border-slate-700 dark:bg-slate-900/40">Fecha</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">ID</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Vehículo</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Operador</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Tipo</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Litros</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Precio</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Total</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Rend.</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">KM Ini</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">KM Fin</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">KM Rec.</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Ubicación</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Destino</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Custodio</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Observaciones</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold text-right dark:border-slate-700"><span class="sr-only">Acciones</span>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($cargas as $c)
                                @php
                                    $veh = $c->vehiculo;
                                    $ope = $c->operador;
                                    $nombreOperador = $ope ? trim(($ope->nombre ?? '').' '.($ope->apellido_paterno ?? '').' '.($ope->apellido_materno ?? '')) : '—';
                                    $kmRec = (is_numeric($c->km_final ?? null) && is_numeric($c->km_inicial ?? null))
                                            ? ((int)$c->km_final - (int)$c->km_inicial) : null;
                                    $obs = $c->observaciones ?? $c->comentarios ?? null;
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                    <td class="sticky left-0 z-10 whitespace-nowrap bg-white px-4 py-3 text-slate-800 dark:bg-slate-800 dark:text-slate-100">
                                        {{ \Illuminate\Support\Carbon::parse($c->fecha)->format('Y-m-d') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">#{{ $c->id }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ $veh->unidad ?? '—' }} @if(($veh->placa ?? null)) <span class="text-slate-500">({{ $veh->placa }})</span> @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $nombreOperador }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $c->tipo_combustible }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ number_format((float)($c->litros ?? 0), 3) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">${{ number_format((float)($c->precio ?? 0), 2) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">${{ number_format((float)($c->total ?? 0), 2) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        @if(!is_null($c->rendimiento)) {{ number_format((float)$c->rendimiento, 2) }} @else — @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $c->km_inicial ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $c->km_final ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        @if(!is_null($kmRec)) {{ $kmRec }} @else — @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $c->ubicacion ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        <div class="max-w-[16rem] truncate" title="{{ $c->destino }}">{{ $c->destino ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        <div class="max-w-[12rem] truncate" title="{{ $c->custodio }}">{{ $c->custodio ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        <div class="max-w-[24rem] line-clamp-2" title="{{ $obs }}">{{ $obs ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Ver (si no hay show, apunta a edit) --}}
                                            <a href="{{ route('cargas.edit', $c->id) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                                               aria-label="Ver carga #{{ $c->id }}">
                                                <span class="material-symbols-outlined">visibility</span>
                                                Ver
                                            </a>

                                            {{-- Editar --}}
                                            <a href="{{ route('cargas.edit', $c->id) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                                               aria-label="Editar carga #{{ $c->id }}">
                                                <span class="material-symbols-outlined">edit</span>
                                                Editar
                                            </a>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('cargas.destroy', $c->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('¿Seguro que quieres eliminar la carga #{{ $c->id }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400"
                                                        aria-label="Eliminar carga #{{ $c->id }}">
                                                    <span class="material-symbols-outlined">delete</span>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="px-4 py-8 text-center text-slate-500 dark:text-slate-300">
                                        @if(request()->hasAny(['search','vehiculo_id','operador_id','ubicacion','tipo_combustible','from','to','litros_min','litros_max','precio_min','precio_max','total_min','total_max','rend_min','rend_max','km_ini_min','km_ini_max','km_fin_min','km_fin_max','destino','custodio']))
                                            No se encontraron resultados con los filtros aplicados.
                                            <a href="{{ route('cargas.index') }}" class="text-indigo-600 hover:text-indigo-800">Limpiar filtros</a>
                                        @else
                                            No hay cargas registradas.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación + contador --}}
            @if(method_exists($cargas, 'links'))
                <div class="mt-6 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                    @php
                        $totalAll = $cargas->total();
                        $firstAll = $cargas->firstItem();
                        $lastAll  = $cargas->lastItem();
                        $currentAll = $cargas->currentPage();
                        $lastPageAll = $cargas->lastPage();
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
                        {{ $cargas->appends(request()->only([
                            'search',
                            'vehiculo_id','operador_id',
                            'ubicacion','tipo_combustible',
                            'from','to',
                            'litros_min','litros_max',
                            'precio_min','precio_max',
                            'total_min','total_max',
                            'rend_min','rend_max',
                            'km_ini_min','km_ini_max',
                            'km_fin_min','km_fin_max',
                            'destino','custodio',
                            'sort_by','sort_dir',
                        ]))->links() }}
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
