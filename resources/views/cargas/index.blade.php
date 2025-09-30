{{-- resources/views/cargas_combustible/index.blade.php
     Vista Index (Tabler + Bootstrap) para Cargas de Combustible
     - Offcanvas de filtros usando <x-filters-offcanvas> (sin backdrop)
     - Toolbar con búsqueda y exportación
     - Tabla con numeración por página
     - Formularios DELETE fuera de la tabla (evita anidación)
--}}

<x-app-layout>
    {{-- Si tu layout ya incluye Vite y app.js, puedes quitar esta línea --}}
    @vite(['resources/js/app.js'])

    @php
        /**
         * Cómputo de filtros activos (excluye búsqueda, orden y paginación).
         * Se usa para mostrar el badge con el número de filtros aplicados.
         */
        $ignored = ['search','page','sort_by','sort_dir'];

        $activeFilters = collect(request()->query())
            ->reject(fn($v, $k) => in_array($k, $ignored, true))
            ->filter(function ($v) {
                return is_array($v)
                    ? collect($v)->filter(fn($x) => $x !== null && $x !== '')->isNotEmpty()
                    : $v !== null && $v !== '';
            });

        $activeCount = $activeFilters->count();
        $exportHref = route('cargas.index', array_merge(request()->except('page'), ['export' => 'xlsx']));
    @endphp

    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title mb-0">Cargas de Combustible</h2>
                        <div class="text-secondary small mt-1">Consulta y analiza rendimientos.</div>
                    </div>

                    {{-- CTA principal --}}
                    <div class="col-auto ms-auto">
                        <a href="{{ route('cargas.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1" aria-hidden="true"></i>
                            <span>Agregar Nueva Carga</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- ============== FLASH DE ÉXITO ============== --}}
            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="ti ti-check me-2" aria-hidden="true"></i>{{ session('success') }}
                </div>
            @endif

            {{-- =====================================================
                 FORM (GET) GLOBAL: Búsqueda + Filtros + Ordenamiento
                 Nota: El formulario se cierra ANTES de la tabla para evitar nesting.
               ===================================================== --}}
            <form method="GET" action="{{ route('cargas.index') }}" aria-label="Búsqueda y filtros de cargas">
                {{-- ===== Toolbar: búsqueda + acciones rápidas ===== --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-center">

                            {{-- Búsqueda global --}}
                            <div class="col-12 col-xl">
                                <div class="input-group" role="search" aria-label="Buscar en cargas">
                                    <span class="input-group-text" id="icon-search">
                                        <i class="ti ti-search" aria-hidden="true"></i>
                                    </span>
                                    <input
                                        type="text"
                                        name="search"
                                        value="{{ request('search') }}"
                                        class="form-control"
                                        placeholder="Buscar…"
                                        aria-label="Término de búsqueda"
                                        aria-describedby="icon-search">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti ti-search me-1" aria-hidden="true"></i>Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Acciones rápidas --}}
                            <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">
                                {{-- Botón único: Exportar Excel --}}
                                <a href="{{ $exportHref }}"
                                   class="btn btn-success"
                                   title="Exportar a Excel">
                                    <i class="ti ti-file-spreadsheet me-1" aria-hidden="true"></i>
                                    Exportar
                                </a>

                                {{-- Botón: Filtros --}}
                                <button type="button"
                                        class="btn btn-outline-secondary position-relative"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#filtersOffcanvas"
                                        aria-controls="filtersOffcanvas"
                                        aria-label="Abrir filtros">
                                    <i class="ti ti-adjustments" aria-hidden="true"></i>
                                    <span class="ms-2">Filtros</span>
                                    @if($activeCount > 0)
                                        <span class="badge bg-primary ms-2" aria-label="Filtros activos">{{ $activeCount }}</span>
                                    @endif
                                </button>
                            </div>

                        </div>

                        {{-- Resumen de resultados (cuando hay búsqueda) --}}
                        @if(request('search'))
                            @php
                                $total    = $cargas->total();
                                $first    = $cargas->firstItem();
                                $last     = $cargas->lastItem();
                                $current  = $cargas->currentPage();
                                $lastPage = $cargas->lastPage();
                            @endphp

                            <div class="mt-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between" role="status">
                                <div class="small">
                                    <span class="badge bg-secondary text-uppercase">Filtro</span>
                                    <span class="ms-2">“{{ request('search') }}”</span>
                                </div>
                                <div class="text-secondary small mt-2 mt-sm-0">
                                    @if($total === 1)
                                        Resultado <strong>(1 de 1)</strong>
                                    @elseif($total > 1)
                                        Página <strong>{{ $current }}</strong> de <strong>{{ $lastPage }}</strong> —
                                        Mostrando <strong>{{ $first }}–{{ $last }}</strong> de <strong>{{ $total }}</strong>
                                    @else
                                        Sin resultados para la búsqueda.
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ================== Offcanvas Filtros (componente) ================== --}}
                <x-filters-offcanvas
                    id="filtersOffcanvas"
                    title="Filtros"
                    :backdrop="false"
                    :scroll="true"
                >
                    {{-- ====== Slot: filtros específicos de Cargas ====== --}}
                    <x-slot name="filters">
                        {{-- 🔹 Primer filtro: Estado (Aprobada/Pendiente) --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Estado</div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label" for="estado">Estado</label>
                                    <select id="estado" name="estado" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="Aprobada" @selected(request('estado') === 'Aprobada')>Aprobada</option>
                                        <option value="Pendiente" @selected(request('estado') === 'Pendiente')>Pendiente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Grupo: Principales --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Principales</div>
                            <div class="row g-2">
                                {{-- Vehículo --}}
                                <div class="col-12">
                                    <label class="form-label" for="vehiculo_id">Vehículo</label>
                                    <select id="vehiculo_id" name="vehiculo_id" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($vehiculos as $v)
                                            <option value="{{ $v->id }}" @selected((string)$v->id === request('vehiculo_id'))>
                                                {{ $v->unidad }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Operador --}}
                                <div class="col-12">
                                    <label class="form-label" for="operador_id">Operador</label>
                                    <select id="operador_id" name="operador_id" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($operadores as $o)
                                            @php
                                                $nombreCompleto = trim(
                                                    collect([$o->nombre ?? '', $o->apellido_paterno ?? '', $o->apellido_materno ?? ''])
                                                        ->filter(fn($x) => $x !== '')
                                                        ->implode(' ')
                                                );
                                            @endphp
                                            <option value="{{ $o->id }}" @selected((string)$o->id === request('operador_id'))>
                                                {{ $nombreCompleto !== '' ? $nombreCompleto : 'Operador' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Tipo de combustible --}}
                                <div class="col-12">
                                    <label class="form-label" for="tipo_combustible">Tipo de combustible</label>
                                    <select id="tipo_combustible" name="tipo_combustible" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($tipos as $t)
                                            <option value="{{ $t }}" @selected($t === request('tipo_combustible'))>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Grupo: Fecha y orden --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Fecha y orden</div>
                            <div class="row g-2">
                                {{-- Rango de fechas --}}
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="from">Desde</label>
                                    <input id="from" type="date" name="from" value="{{ request('from') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="to">Hasta</label>
                                    <input id="to" type="date" name="to" value="{{ request('to') }}" class="form-control">
                                </div>

                                {{-- Ordenamiento --}}
                                <div class="col-12 col-sm-6">
                                    @php
                                        $sortOptions = [
                                            'fecha'            => 'Fecha',
                                            'vehiculo'         => 'Vehículo',
                                            'placa'            => 'Placa',
                                            'operador'         => 'Operador',
                                            'tipo_combustible' => 'Tipo',
                                            'litros'           => 'Litros',
                                            'precio'           => 'Precio',
                                            'total'            => 'Total',
                                            'rendimiento'      => 'Rendimiento',
                                            'km_inicial'       => 'KM Inicial',
                                            'km_final'         => 'KM Final',
                                            'recorrido'        => 'KM Recorridos',
                                            'id'               => 'ID',
                                        ];
                                    @endphp
                                    <label class="form-label" for="sort_by">Ordenar por</label>
                                    <select id="sort_by" name="sort_by" class="form-select">
                                        @foreach($sortOptions as $val => $label)
                                            <option value="{{ $val }}" @selected(request('sort_by','fecha') === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="sort_dir">Dirección</label>
                                    <select id="sort_dir" name="sort_dir" class="form-select">
                                        <option value="asc"  @selected(request('sort_dir','desc') === 'asc')>Ascendente</option>
                                        <option value="desc" @selected(request('sort_dir','desc') === 'desc')>Descendente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Grupo: Métricas numéricas --}}
                        <div>
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Métricas</div>
                            <div class="row g-2">
                                {{-- Rangos de litros --}}
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="0.001" name="litros_min" value="{{ request('litros_min') }}" class="form-control" placeholder="Litros mín">
                                </div>
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="0.001" name="litros_max" value="{{ request('litros_max') }}" class="form-control" placeholder="Litros máx">
                                </div>

                                {{-- Rangos de precio --}}
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="0.01" name="precio_min" value="{{ request('precio_min') }}" class="form-control" placeholder="Precio mín">
                                </div>
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="0.01" name="precio_max" value="{{ request('precio_max') }}" class="form-control" placeholder="Precio máx">
                                </div>

                                {{-- Rangos de total --}}
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="0.01" name="total_min" value="{{ request('total_min') }}" class="form-control" placeholder="Total mín">
                                </div>
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="0.01" name="total_max" value="{{ request('total_max') }}" class="form-control" placeholder="Total máx">
                                </div>

                                {{-- Rangos de rendimiento --}}
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="0.01" name="rend_min" value="{{ request('rend_min') }}" class="form-control" placeholder="Rend mín">
                                </div>
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="0.01" name="rend_max" value="{{ request('rend_max') }}" class="form-control" placeholder="Rend máx">
                                </div>

                                {{-- Rangos de KM --}}
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="1" name="km_ini_min" value="{{ request('km_ini_min') }}" class="form-control" placeholder="KM inicial mín">
                                </div>
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="1" name="km_ini_max" value="{{ request('km_ini_max') }}" class="form-control" placeholder="KM inicial máx">
                                </div>

                                <div class="col-6 col-lg-4">
                                    <input type="number" step="1" name="km_fin_min" value="{{ request('km_fin_min') }}" class="form-control" placeholder="KM final mín">
                                </div>
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="1" name="km_fin_max" value="{{ request('km_fin_max') }}" class="form-control" placeholder="KM final máx">
                                </div>
                            </div>
                        </div>
                    </x-slot>

                    {{-- ====== Slot: footer con acciones ====== --}}
                    <x-slot name="footer">
                        <a href="{{ route('cargas.index') }}" class="btn btn-link">Limpiar filtros</a>
                        <div class="d-flex">
                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="offcanvas">Cerrar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1" aria-hidden="true"></i>Aplicar filtros
                            </button>
                        </div>
                    </x-slot>
                </x-filters-offcanvas>
                {{-- /Offcanvas (componente) --}}
            </form>
            {{-- /FORM GET --}}

            {{-- ================== TABLA (sin columna ID) ================== --}}
            <div class="card">
                {{-- 👇 Barra de scroll horizontal superior sincronizada --}}
                <div id="cargas-table-scroll-top"
                     class="table-scroll-top"
                     role="presentation"
                     aria-hidden="true">
                    <div id="cargas-table-scroll-spacer"></div>
                </div>

                <div class="table-responsive" id="cargas-table-wrap">
                    <table class="table table-vcenter table-striped table-hover" id="cargas-table">
                        <thead>
                            <tr class="text-uppercase text-secondary small">
                                <th class="text-center text-nowrap">#</th>
                                <th>Fecha</th>
                                <th>Vehículo</th>
                                <th>Operador</th>
                                <th>Tipo</th>
                                <th class="text-end">Litros</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Rendimiento</th>
                                <th class="text-end">KM Inicial</th>
                                <th class="text-end">KM Final</th>
                                <th class="text-end">KM Recorridos</th>
                                <th style="min-width:12rem;">Destino</th>
                                <th style="min-width:10rem;">Custodio</th>
                                <th style="min-width:16rem;">Observaciones</th>
                                <th class="text-nowrap">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cargas as $c)
                                @php
                                    $veh = $c->vehiculo;
                                    $ope = $c->operador;

                                    $nombreOperador = $ope
                                        ? trim(collect([$ope->nombre ?? '', $ope->apellido_paterno ?? '', $ope->apellido_materno ?? ''])
                                            ->filter(fn($x) => $x !== '')
                                            ->implode(' '))
                                        : '—';

                                    $kmRec = (is_numeric($c->km_final ?? null) && is_numeric($c->km_inicial ?? null))
                                        ? ((int)$c->km_final - (int)$c->km_inicial)
                                        : null;

                                    $obs = $c->observaciones ?? $c->comentarios ?? null;

                                    $fechaStr = $c->fecha
                                        ? \Illuminate\Support\Carbon::parse($c->fecha)->format('Y-m-d')
                                        : '—';

                                    $rowId = is_numeric($c->id ?? null) ? (int)$c->id : 0;

                                    $estado = $c->estado ?? 'Pendiente';
                                @endphp

                                <tr>
                                    {{-- Numeración por página --}}
                                    <td class="text-center text-nowrap">{{ ($cargas->firstItem() ?? 0) + $loop->index }}</td>

                                    {{-- Fecha --}}
                                    <td class="text-nowrap">{{ $fechaStr }}</td>

                                    {{-- Vehículo (sin placa) --}}
                                    <td class="text-nowrap">
                                        {{ $veh->unidad ?? '—' }}
                                    </td>

                                    {{-- Operador --}}
                                    <td class="text-nowrap">{{ $nombreOperador }}</td>

                                    {{-- Tipo combustible --}}
                                    <td class="text-nowrap">{{ $c->tipo_combustible ?? '—' }}</td>

                                    {{-- Métricas numéricas --}}
                                    <td class="text-end text-nowrap">{{ number_format((float)($c->litros ?? 0), 3) }}</td>
                                    <td class="text-end text-nowrap">${{ number_format((float)($c->precio ?? 0), 2) }}</td>
                                    <td class="text-end text-nowrap">${{ number_format((float)($c->total ?? 0), 2) }}</td>
                                    <td class="text-end text-nowrap">
                                        {{ !is_null($c->rendimiento) ? number_format((float)$c->rendimiento, 2) : '—' }}
                                    </td>

                                    {{-- KMs --}}
                                    <td class="text-end text-nowrap">{{ $c->km_inicial ?? '—' }}</td>
                                    <td class="text-end text-nowrap">{{ $c->km_final ?? '—' }}</td>
                                    <td class="text-end text-nowrap">{{ !is_null($kmRec) ? $kmRec : '—' }}</td>

                                    {{-- Campos largos con truncado --}}
                                    <td>
                                        <div class="text-truncate" title="{{ $c->destino }}">{{ $c->destino ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" title="{{ $c->custodio }}">{{ $c->custodio ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" title="{{ $obs }}">{{ $obs ?? '—' }}</div>
                                    </td>

                                    {{-- ESTADO --}}
                                    <td class="text-nowrap">
                                        @if($estado === 'Aprobada')
                                            <span class="badge bg-green-lt">Aprobada</span>
                                        @else
                                            <span class="badge bg-yellow-lt">Pendiente</span>
                                        @endif
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="text-end text-nowrap" style="min-width:15rem;">
                                        <div class="d-inline-flex gap-1">
                                            {{-- Ver --}}
                                            <a href="{{ route('cargas.edit', $c->id) }}"
                                               class="btn btn-outline-secondary btn-sm"
                                               title="Ver">
                                                <i class="ti ti-eye me-1" aria-hidden="true"></i>Ver
                                            </a>

                                            {{-- Editar --}}
                                            <a href="{{ route('cargas.edit', $c->id) }}"
                                               class="btn btn-outline-secondary btn-sm"
                                               title="Editar">
                                                <i class="ti ti-edit me-1" aria-hidden="true"></i>Editar
                                            </a>

                                            {{-- Eliminar --}}
                                            @if($rowId > 0)
                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    form="del-{{ $rowId }}"
                                                    onclick="event.stopPropagation(); return confirm('¿Seguro que quieres eliminar?');"
                                                    title="Eliminar">
                                                    <i class="ti ti-trash me-1" aria-hidden="true"></i>Eliminar
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                {{-- Estado vacío --}}
                                <tr>
                                    <td colspan="17" class="py-6">
                                        <div class="empty">
                                            <div class="empty-icon">
                                            <i class="ti ti-database-off" aria-hidden="true"></i>
                                            </div>
                                            <p class="empty-title">No hay datos</p>
                                            <p class="empty-subtitle text-secondary">
                                                @if(request()->hasAny([
                                                    'search','vehiculo_id','operador_id','tipo_combustible','from','to',
                                                    'litros_min','litros_max','precio_min','precio_max','total_min','total_max',
                                                    'rend_min','rend_max','km_ini_min','km_ini_max','km_fin_min','km_fin_max',
                                                    'destino','custodio','estado'
                                                ]))
                                                    No se encontraron resultados con los filtros aplicados.
                                                @else
                                                    Aún no has registrado cargas de combustible.
                                                @endif
                                            </p>
                                            <div class="empty-action">
                                                @if(request()->hasAny([
                                                    'search','vehiculo_id','operador_id','tipo_combustible','from','to',
                                                    'litros_min','litros_max','precio_min','precio_max','total_min','total_max',
                                                    'rend_min','rend_max','km_ini_min','km_ini_max','km_fin_min','km_fin_max',
                                                    'destino','custodio','estado'
                                                ]))
                                                    <a href="{{ route('cargas.index') }}" class="btn btn-outline-secondary">
                                                        Limpiar filtros
                                                    </a>
                                                @endif
                                                <a href="{{ route('cargas.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-2" aria-hidden="true"></i>Agregar Nueva Carga
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- =============== PAGINACIÓN + CONTADOR =============== --}}
            @if(method_exists($cargas, 'links'))
                @php
                    $totalAll    = $cargas->total();
                    $firstAll    = $cargas->firstItem();
                    $lastAll     = $cargas->lastItem();
                    $currentAll  = $cargas->currentPage();
                    $lastPageAll = $cargas->lastPage();
                @endphp

                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between mt-3">
                    <p class="text-secondary small mb-2 mb-sm-0">
                        @if($totalAll === 0)
                            Mostrando 0 resultados
                        @elseif($totalAll === 1)
                            Resultado <strong>(1 de 1)</strong>
                        @else
                            Página <strong>{{ $currentAll }}</strong> de <strong>{{ $lastPageAll }}</strong> —
                            Mostrando <strong>{{ $firstAll }}–{{ $lastAll }}</strong> de <strong>{{ $totalAll }}</strong> resultados
                        @endif
                    </p>

                    {{-- Conserva querystring al paginar --}}
                    <div>
                        {{ $cargas->appends(request()->only([
                            'search','vehiculo_id','operador_id','tipo_combustible',
                            'from','to','litros_min','litros_max','precio_min','precio_max',
                            'total_min','total_max','rend_min','rend_max','km_ini_min','km_ini_max',
                            'km_fin_min','km_fin_max','destino','custodio','sort_by','sort_dir','estado',
                        ]))->links() }}
                    </div>
                </div>
            @endif

            {{-- ====== Formularios DELETE ocultos (fuera de la tabla) ====== --}}
            @foreach($cargas as $cc)
                @php $rid = is_numeric($cc->id ?? null) ? (int)$cc->id : 0; @endphp
                @if($rid > 0)
                    <form id="del-{{ $rid }}"
                          action="{{ url('/cargas/'.$rid) }}"  {{-- fuerza ruta WEB (evita /api) --}}
                          method="POST"
                          class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            @endforeach

            {{-- ====== Footer ====== --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    {{-- ================= CSS/JS propio mínimo ================= --}}
    @once
        <style>
            /* Si en tu proyecto requieres desactivar backdrops globalmente */
            .offcanvas-backdrop,
            .modal-backdrop {
                display: none !important;
                opacity: 0 !important;
            }
            /* Eleva dropdown por si hay stacking contexts inesperados */
            .dropdown-menu { z-index: 1080; }

            /* ===== Barra de scroll horizontal superior (espejo) ===== */
            .table-scroll-top{
                overflow-x: auto;
                overflow-y: hidden;
                height: 14px;             /* sólo el scrollbar */
                margin: .25rem 0;
                background: transparent;
            }
            .table-scroll-top > #cargas-table-scroll-spacer{
                height: 1px;              /* elemento “fantasma” para generar ancho */
            }
            /* Ajuste opcional de scrollbar WebKit */
            .table-scroll-top::-webkit-scrollbar{ height: 12px; }
            .table-scroll-top::-webkit-scrollbar-thumb{ border-radius: 8px; }

            /* Dark theme friendly (opcional; hereda estilos globales) */
            .theme-dark .table-scroll-top,
            [data-bs-theme="dark"] .table-scroll-top{
                background: transparent;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Inicialización defensiva de Dropdown (por si no hay auto-init)
                if (window.bootstrap && window.bootstrap.Dropdown) {
                    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
                        new window.bootstrap.Dropdown(el);
                    });
                }

                // ====== Scroll superior sincronizado con el de la tabla ======
                const topScroll   = document.getElementById('cargas-table-scroll-top');
                const spacer      = document.getElementById('cargas-table-scroll-spacer');
                const tableWrap   = document.getElementById('cargas-table-wrap');

                if (topScroll && spacer && tableWrap) {
                    const updateWidths = () => {
                        // ancho del contenido real desplazable
                        const sw = tableWrap.scrollWidth;
                        spacer.style.width = sw + 'px';
                        // mostrar u ocultar el scroll top según necesidad
                        const need = sw > (tableWrap.clientWidth + 2);
                        topScroll.style.display = need ? 'block' : 'none';
                    };

                    // Evitar bucle de eventos al sincronizar
                    let syncingTop = false, syncingBottom = false;

                    topScroll.addEventListener('scroll', () => {
                        if (syncingTop) { syncingTop = false; return; }
                        syncingBottom = true;
                        tableWrap.scrollLeft = topScroll.scrollLeft;
                    }, { passive: true });

                    tableWrap.addEventListener('scroll', () => {
                        if (syncingBottom) { syncingBottom = false; return; }
                        syncingTop = true;
                        topScroll.scrollLeft = tableWrap.scrollLeft;
                    }, { passive: true });

                    // Recalcular en resize y al cargar
                    updateWidths();
                    window.addEventListener('resize', updateWidths);

                    // Por si las fuentes/íconos refluye (pequeño delay)
                    setTimeout(updateWidths, 150);
                    setTimeout(updateWidths, 500);
                }
            });
        </script>
    @endonce
</x-app-layout>
