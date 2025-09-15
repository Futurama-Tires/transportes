{{-- resources/views/cargas_combustible/index.blade.php — versión Tabler (sin oscurecimiento, acciones separadas, numeración y DELETE robusto) --}}
<x-app-layout>
    {{-- Si ya incluyes @vite en tu layout, puedes quitar esta línea --}}
    @vite(['resources/js/app.js'])

    @php
        // Cuenta de filtros activos (excluye búsqueda, orden y paginación)
        $ignored = ['search','page','sort_by','sort_dir'];
        $activeFilters = collect(request()->query())->filter(function($v,$k) use ($ignored){
            if (in_array($k,$ignored)) return false;
            if (is_array($v)) return collect($v)->filter(fn($x)=>$x!==null && $x!=='')->isNotEmpty();
            return $v !== null && $v !== '';
        });
        $activeCount = $activeFilters->count();
    @endphp

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title mb-0">Cargas de Combustible</h2>
                        <div class="text-secondary small mt-1">Consulta y analiza rendimientos.</div>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('cargas.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>
                            Agregar Nueva Carga
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- FLASH ÉXITO --}}
            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="ti ti-check me-2"></i>{{ session('success') }}
                </div>
            @endif

            {{-- =========================
                 FORM GLOBAL (GET) SOLO PARA BÚSQUEDA/FILTROS
                 IMPORTANTE: se cierra ANTES de la tabla para NO anidar formularios
               ========================= --}}
            <form method="GET" action="{{ route('cargas.index') }}">
                {{-- TOOLBAR: búsqueda + acciones rápidas --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-center">
                            {{-- Búsqueda global --}}
                            <div class="col-12 col-xl">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    <input type="text"
                                           name="search"
                                           value="{{ request('search') }}"
                                           class="form-control"
                                           placeholder="Buscar…"
                                           aria-label="Búsqueda global">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti ti-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Acciones --}}
                            <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">
                                {{-- Dropdown Exportar --}}
                                <div class="dropdown">
                                    <button id="btnExportar" type="button"
                                            class="btn btn-outline-secondary dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-download me-1"></i>Exportar
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        {{-- Cambia # por tu ruta real de exportación --}}
                                        <li>
                                            <a class="dropdown-item" href="#">
                                                <i class="ti ti-file-spreadsheet me-2"></i>Excel (.xlsx)
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                {{-- Botón Filtros (abre Offcanvas SIN backdrop) --}}
                                <button type="button"
                                        class="btn btn-outline-secondary position-relative"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#filtersOffcanvas"
                                        aria-controls="filtersOffcanvas">
                                    <i class="ti ti-adjustments"></i>
                                    <span class="ms-2">Filtros</span>
                                    @if($activeCount>0)
                                        <span class="badge bg-primary ms-2">{{ $activeCount }}</span>
                                    @endif
                                </button>
                            </div>
                        </div>

                        {{-- Resumen de resultados cuando hay búsqueda --}}
                        @if(request('search'))
                            @php
                                $total   = $cargas->total();
                                $first   = $cargas->firstItem();
                                $last    = $cargas->lastItem();
                                $current = $cargas->currentPage();
                                $lastPage= $cargas->lastPage();
                            @endphp
                            <div class="mt-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between">
                                <div class="small">
                                    <span class="badge bg-secondary text-uppercase">Filtro</span>
                                    <span class="ms-2">“{{ request('search') }}”</span>
                                </div>
                                <div class="text-secondary small mt-2 mt-sm-0">
                                    @if($total === 1)
                                        Resultado <strong>(1 de 1)</strong>
                                    @elseif($total > 1)
                                        Página <strong>{{ $current }}</strong> de <strong>{{ $lastPage }}</strong> — Mostrando <strong>{{ $first }}–{{ $last }}</strong> de <strong>{{ $total }}</strong>
                                    @else
                                        Sin resultados para la búsqueda.
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- OFFCANVAS DE FILTROS (sin oscurecimiento) --}}
                <div class="offcanvas offcanvas-end"
                     tabindex="-1"
                     id="filtersOffcanvas"
                     aria-labelledby="filtersOffcanvasLabel"
                     data-bs-backdrop="false"   {{-- 👈 sin overlay --}}
                     data-bs-scroll="true">     {{-- 👈 body scrolleable --}}
                    <div class="offcanvas-header">
                        <h2 class="offcanvas-title h4" id="filtersOffcanvasLabel">
                            <i class="ti ti-adjustments me-2"></i>Filtros
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                    </div>
                    <div class="offcanvas-body">
                        {{-- Principales --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Principales</div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Vehículo</label>
                                    <select name="vehiculo_id" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($vehiculos as $v)
                                            <option value="{{ $v->id }}" @selected((string)$v->id === request('vehiculo_id'))>
                                                {{ $v->unidad }} @if($v->placa) — {{ $v->placa }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Operador</label>
                                    <select name="operador_id" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($operadores as $o)
                                            @php
                                                $nombreCompleto = trim(($o->nombre ?? '').' '.($o->apellido_paterno ?? '').' '.($o->apellido_materno ?? ''));
                                            @endphp
                                            <option value="{{ $o->id }}" @selected((string)$o->id === request('operador_id'))>
                                                {{ $nombreCompleto ?: 'Operador' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Ubicación</label>
                                    <select name="ubicacion" class="form-select">
                                        <option value="">Todas</option>
                                        @foreach($ubicaciones as $u)
                                            <option value="{{ $u }}" @selected($u === request('ubicacion'))>{{ $u }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Tipo de combustible</label>
                                    <select name="tipo_combustible" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($tipos as $t)
                                            <option value="{{ $t }}" @selected($t === request('tipo_combustible'))>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Fecha y orden --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Fecha y orden</div>
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Desde</label>
                                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Hasta</label>
                                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
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
                                    <label class="form-label">Ordenar por</label>
                                    <select name="sort_by" class="form-select">
                                        @foreach($opts as $val => $label)
                                            <option value="{{ $val }}" @selected(request('sort_by','fecha')===$val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Dirección</label>
                                    <select name="sort_dir" class="form-select">
                                        <option value="asc"  @selected(request('sort_dir','desc')==='asc')>Ascendente</option>
                                        <option value="desc" @selected(request('sort_dir','desc')==='desc')>Descendente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Métricas numéricas --}}
                        <div>
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Métricas</div>
                            <div class="row g-2">
                                <div class="col-6 col-lg-4"><input type="number" step="0.001" name="litros_min" value="{{ request('litros_min') }}" class="form-control" placeholder="Litros mín"></div>
                                <div class="col-6 col-lg-4"><input type="number" step="0.001" name="litros_max" value="{{ request('litros_max') }}" class="form-control" placeholder="Litros máx"></div>

                                <div class="col-6 col-lg-4"><input type="number" step="0.01" name="precio_min" value="{{ request('precio_min') }}" class="form-control" placeholder="Precio mín"></div>
                                <div class="col-6 col-lg-4"><input type="number" step="0.01" name="precio_max" value="{{ request('precio_max') }}" class="form-control" placeholder="Precio máx"></div>

                                <div class="col-6 col-lg-4"><input type="number" step="0.01" name="total_min" value="{{ request('total_min') }}" class="form-control" placeholder="Total mín"></div>
                                <div class="col-6 col-lg-4"><input type="number" step="0.01" name="total_max" value="{{ request('total_max') }}" class="form-control" placeholder="Total máx"></div>

                                <div class="col-6 col-lg-4"><input type="number" step="0.01" name="rend_min" value="{{ request('rend_min') }}" class="form-control" placeholder="Rend mín"></div>
                                <div class="col-6 col-lg-4"><input type="number" step="0.01" name="rend_max" value="{{ request('rend_max') }}" class="form-control" placeholder="Rend máx"></div>

                                <div class="col-6 col-lg-4"><input type="number" step="1" name="km_ini_min" value="{{ request('km_ini_min') }}" class="form-control" placeholder="KM inicial mín"></div>
                                <div class="col-6 col-lg-4"><input type="number" step="1" name="km_ini_max" value="{{ request('km_ini_max') }}" class="form-control" placeholder="KM inicial máx"></div>

                                <div class="col-6 col-lg-4"><input type="number" step="1" name="km_fin_min" value="{{ request('km_fin_min') }}" class="form-control" placeholder="KM final mín"></div>
                                <div class="col-6 col-lg-4"><input type="number" step="1" name="km_fin_max" value="{{ request('km_fin_max') }}" class="form-control" placeholder="KM final máx"></div>
                            </div>
                        </div>
                    </div>

                    <div class="offcanvas-footer d-flex justify-content-between align-items-center p-3 border-top">
                        <a href="{{ route('cargas.index') }}" class="btn btn-link">Limpiar filtros</a>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="offcanvas">Cerrar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1"></i>Aplicar filtros
                            </button>
                        </div>
                    </div>
                </div>
                {{-- /OFFCANVAS --}}
            </form> {{-- 👈 CERRAMOS AQUÍ EL GET PARA NO ANIDAR FORMULARIOS --}}

            {{-- TABLA (numeración y sin columna ID) --}}
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped table-hover">
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
                                <th>Ubicación</th>
                                <th style="min-width: 12rem;">Destino</th>
                                <th style="min-width: 10rem;">Custodio</th>
                                <th style="min-width: 16rem;">Observaciones</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cargas as $c)
                                @php
                                    $veh = $c->vehiculo;
                                    $ope = $c->operador;
                                    $nombreOperador = $ope ? trim(($ope->nombre ?? '').' '.($ope->apellido_paterno ?? '').' '.($ope->apellido_materno ?? '')) : '—';
                                    $kmRec = (is_numeric($c->km_final ?? null) && is_numeric($c->km_inicial ?? null)) ? ((int)$c->km_final - (int)$c->km_inicial) : null;
                                    $obs = $c->observaciones ?? $c->comentarios ?? null;
                                    $fechaStr = \Illuminate\Support\Carbon::parse($c->fecha)->format('Y-m-d');
                                    $vehLabel = trim(($veh->unidad ?? '—').(($veh->placa ?? null) ? ' ('.$veh->placa.')' : ''));
                                    $rowId = is_numeric($c->id ?? null) ? (int)$c->id : 0;
                                @endphp
                                <tr>
                                    {{-- Numeración independiente (reinicia por página) --}}
                                    <td class="text-center text-nowrap">{{ $loop->iteration }}</td>

                                    <td class="text-nowrap">{{ $fechaStr }}</td>

                                    <td class="text-nowrap">
                                        {{ $veh->unidad ?? '—' }}
                                        @if(($veh->placa ?? null))
                                            <span class="text-secondary">({{ $veh->placa }})</span>
                                        @endif
                                    </td>

                                    <td class="text-nowrap">{{ $nombreOperador }}</td>
                                    <td class="text-nowrap">{{ $c->tipo_combustible }}</td>

                                    <td class="text-end text-nowrap">{{ number_format((float)($c->litros ?? 0), 3) }}</td>
                                    <td class="text-end text-nowrap">${{ number_format((float)($c->precio ?? 0), 2) }}</td>
                                    <td class="text-end text-nowrap">${{ number_format((float)($c->total ?? 0), 2) }}</td>
                                    <td class="text-end text-nowrap">
                                        @if(!is_null($c->rendimiento)) {{ number_format((float)$c->rendimiento, 2) }} @else — @endif
                                    </td>

                                    <td class="text-end text-nowrap">{{ $c->km_inicial ?? '—' }}</td>
                                    <td class="text-end text-nowrap">{{ $c->km_final ?? '—' }}</td>
                                    <td class="text-end text-nowrap">@if(!is_null($kmRec)) {{ $kmRec }} @else — @endif</td>

                                    <td class="text-nowrap">{{ $c->ubicacion ?? '—' }}</td>

                                    <td>
                                        <div class="text-truncate" title="{{ $c->destino }}">{{ $c->destino ?? '—' }}</div>
                                    </td>

                                    <td>
                                        <div class="text-truncate" title="{{ $c->custodio }}">{{ $c->custodio ?? '—' }}</div>
                                    </td>

                                    <td>
                                        <div class="text-truncate" title="{{ $obs }}">{{ $obs ?? '—' }}</div>
                                    </td>

                                    <td class="text-end">
                                        {{-- Tres botones separados: Ver, Editar, Eliminar --}}
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('cargas.edit', $c->id) }}"
                                               class="btn btn-outline-secondary btn-sm"
                                               title="Ver">
                                                <i class="ti ti-eye me-1"></i>Ver
                                            </a>

                                            <a href="{{ route('cargas.edit', $c->id) }}"
                                               class="btn btn-outline-secondary btn-sm"
                                               title="Editar">
                                                <i class="ti ti-edit me-1"></i>Editar
                                            </a>

                                            @if($rowId > 0)
                                                {{-- Botón suelto que dispara un formulario oculto fuera de la tabla (evita anidación) --}}
                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    form="del-{{ $rowId }}"
                                                    onclick="event.stopPropagation(); return confirm('¿Seguro que quieres eliminar?');"
                                                    title="Eliminar">
                                                    <i class="ti ti-trash me-1"></i>Eliminar
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="py-6">
                                        <div class="empty">
                                            <div class="empty-icon">
                                                <i class="ti ti-database-off"></i>
                                            </div>
                                            <p class="empty-title">No hay datos</p>
                                            <p class="empty-subtitle text-secondary">
                                                @if(request()->hasAny(['search','vehiculo_id','operador_id','ubicacion','tipo_combustible','from','to','litros_min','litros_max','precio_min','precio_max','total_min','total_max','rend_min','rend_max','km_ini_min','km_ini_max','km_fin_min','km_fin_max','destino','custodio']))
                                                    No se encontraron resultados con los filtros aplicados.
                                                @else
                                                    Aún no has registrado cargas de combustible.
                                                @endif
                                            </p>
                                            <div class="empty-action">
                                                @if(request()->hasAny(['search','vehiculo_id','operador_id','ubicacion','tipo_combustible','from','to','litros_min','litros_max','precio_min','precio_max','total_min','total_max','rend_min','rend_max','km_ini_min','km_ini_max','km_fin_min','km_fin_max','destino','custodio']))
                                                    <a href="{{ route('cargas.index') }}" class="btn btn-outline-secondary">
                                                        Limpiar filtros
                                                    </a>
                                                @endif
                                                <a href="{{ route('cargas.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-2"></i>Agregar Nueva Carga
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

            {{-- PAGINACIÓN + CONTADOR --}}
            @if(method_exists($cargas, 'links'))
                @php
                    $totalAll   = $cargas->total();
                    $firstAll   = $cargas->firstItem();
                    $lastAll    = $cargas->lastItem();
                    $currentAll = $cargas->currentPage();
                    $lastPageAll= $cargas->lastPage();
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
                    <div>
                        {{ $cargas->appends(request()->only([
                            'search','vehiculo_id','operador_id','ubicacion','tipo_combustible',
                            'from','to','litros_min','litros_max','precio_min','precio_max',
                            'total_min','total_max','rend_min','rend_max','km_ini_min','km_ini_max',
                            'km_fin_min','km_fin_max','destino','custodio','sort_by','sort_dir',
                        ]))->links() }}
                    </div>
                </div>
            @endif

            {{-- =========================
                 FORMULARIOS OCULTOS DELETE (fuera de la tabla y de cualquier <form GET>)
                 Cada botón "Eliminar" usa form="del-{id}" para enviar aquí
               ========================= --}}
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

            {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    {{-- Sin oscurecimiento: CSS defensivo + init de dropdowns --}}
    <style>
        /* Por si otra parte del sistema intenta crear backdrops */
        .offcanvas-backdrop,
        .modal-backdrop {
            display: none !important;
            opacity: 0 !important;
        }
        /* Eleva el dropdown por si hay stacking contexts raros */
        .dropdown-menu { z-index: 1080; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializa dropdowns de Bootstrap si no están auto-inicializados
            if (window.bootstrap && window.bootstrap.Dropdown) {
                document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
                    new window.bootstrap.Dropdown(el);
                });
            }
        });
    </script>
</x-app-layout>
