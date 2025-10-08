{{-- resources/views/vehiculos/index.blade.php
     Vista Index (Tabler + Bootstrap) para la gestión de Vehículos
--}}
<x-app-layout>
    @vite(['resources/js/app.js', 'resources/js/vehiculos/index.js', 'resources/css/gallery.css'])

    <div id="vehiculos-app"
         data-base-photo="{{ url('/vehiculos/fotos') }}"
         data-base-veh="{{ url('/vehiculos') }}">

        @php
            // Filtros activos (para badge del botón)
            $ignored = ['search','page','sort_by','sort_dir'];
            $activeFilters = collect(request()->query())
                ->reject(fn($v, $k) => in_array($k, $ignored, true))
                ->filter(fn($v) => is_array($v)
                    ? collect($v)->filter(fn($x) => $x !== null && $x !== '')->isNotEmpty()
                    : $v !== null && $v !== ''
                );
            $activeCount = $activeFilters->count();

            // Columnas de orden
            $columns = [
                'created_at'  => 'Fecha',
                'id'          => 'ID',
                'placa'       => 'Placa',
                'serie'       => 'Serie',
                'unidad'      => 'Unidad',
                'marca'       => 'Marca',
                'anio'        => 'Año',
                'propietario' => 'Propietario',
            ];

            // URL de exportación a Excel con filtros actuales (excepto la página)
            $exportHref = route('vehiculos.index', array_merge(request()->except('page'), ['export' => 'xlsx']));
        @endphp

        {{-- HEADER --}}
        <x-slot name="header">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title mb-0">Gestión de Vehículos</h2>
                        </div>
                        <div class="col-auto ms-auto">
                            <a href="{{ route('vehiculos.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                                <span>Agregar Vehículo</span>
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
                        <i class="ti ti-check me-2" aria-hidden="true"></i>{{ session('success') }}
                    </div>
                @endif

                {{-- FORM GLOBAL (GET) --}}
                <form method="GET" action="{{ route('vehiculos.index') }}" aria-label="Búsqueda y filtros de vehículos">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-2 align-items-center">
                                {{-- Búsqueda --}}
                                <div class="col-12 col-xl">
                                    <div class="input-group" role="search" aria-label="Buscar en vehículos">
                                        <span class="input-group-text" id="icon-search">
                                            <i class="ti ti-search" aria-hidden="true"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="search"
                                            value="{{ request('search') }}"
                                            class="form-control"
                                            placeholder="Buscar..."
                                            aria-label="Término de búsqueda"
                                            aria-describedby="icon-search"
                                        >
                                        <button class="btn btn-primary" type="submit">
                                            <i class="ti ti-search me-1" aria-hidden="true"></i>Buscar
                                        </button>
                                    </div>
                                </div>

                                {{-- Acciones --}}
                                <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">
                                    {{-- Botón único: Exportar Excel --}}
                                    <a href="{{ $exportHref }}"
                                       class="btn btn-success"
                                       title="Exportar a Excel">
                                        <i class="ti ti-file-spreadsheet me-1" aria-hidden="true"></i>
                                        Exportar
                                    </a>

                                    {{-- Botón Filtros --}}
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary position-relative"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#filtersOffcanvas"
                                        aria-controls="filtersOffcanvas"
                                        aria-label="Abrir filtros">
                                        <i class="ti ti-adjustments" aria-hidden="true"></i>
                                        <span class="ms-2">Filtros</span>
                                        @if($activeCount > 0)
                                            <span class="badge bg-primary ms-2" aria-label="{{ $activeCount }} filtros activos">{{ $activeCount }}</span>
                                        @endif
                                    </button>
                                </div>

                            </div>

                            {{-- Resumen cuando hay búsqueda --}}
                            @if(request('search'))
                                @php
                                    $total    = $vehiculos->total();
                                    $first    = $vehiculos->firstItem();
                                    $last     = $vehiculos->lastItem();
                                    $current  = $vehiculos->currentPage();
                                    $lastPage = $vehiculos->lastPage();
                                @endphp
                                <div class="mt-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between" role="status">
                                    <div class="small">
                                        <span class="badge bg-secondary text-uppercase">Filtro</span>
                                        <span class="ms-2">“{{ request('search') }}”</span>
                                    </div>
                                    <div class="text-secondary small mt-2 mt-sm-0">
                                        @if($total === 0)
                                            Sin resultados para la búsqueda.
                                        @elseif($total === 1)
                                            Resultado <strong>(1 de 1)</strong>
                                        @else
                                            Página <strong>{{ $current }}</strong> de <strong>{{ $lastPage }}</strong> —
                                            Mostrando <strong>{{ $first }}–{{ $last }}</strong> de <strong>{{ $total }}</strong>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- OFFCANVAS (componente) --}}
                    <x-filters-offcanvas
                        id="filtersOffcanvas"
                        title="Filtros"
                        :clear-url="route('vehiculos.index')"
                        :backdrop="false"
                        :scroll="false"
                    >
                        <x-slot name="filters">
                            {{-- Principales --}}
                            <div class="mb-4">
                                <div class="text-secondary text-uppercase fw-semibold small mb-2">Principales</div>
                                <div class="row g-2">
                                    <x-filter.input name="id"        type="number" label="ID"          class="col-12 col-sm-6" />
                                    <x-filter.input name="unidad"    label="Unidad"                    class="col-12 col-sm-6" />
                                    <x-filter.input name="placa"     label="Placa"                     class="col-12 col-sm-6" />
                                    <x-filter.input name="serie"     label="Serie (VIN)"               class="col-12 col-sm-6" />
                                    <x-filter.input name="propietario" label="Propietario"            class="col-12 col-sm-6" />
                                    <x-filter.select name="marca"    label="Marca" :options="$marcas ?? []" empty="Todas" class="col-12 col-sm-6" />
                                </div>
                            </div>

                            {{-- Año --}}
                            <x-filter.number-range
                                nameMin="anio_min"
                                nameMax="anio_max"
                                label="Año"
                                class="mb-4"
                            />
                        </x-slot>

                        <x-slot name="order">
                            {{-- Orden --}}
                            <div class="mb-1">
                                <div class="text-secondary text-uppercase fw-semibold small mb-2">Orden</div>
                                <div class="row g-2">
                                    <x-filter.select
                                        name="sort_by"
                                        label="Ordenar por"
                                        :options="$columns"
                                        :value="request('sort_by','created_at')"
                                        class="col-12 col-sm-6"
                                    />
                                    <x-filter.select
                                        name="sort_dir"
                                        label="Dirección"
                                        :options="['asc' => 'Ascendente','desc' => 'Descendente']"
                                        :value="request('sort_dir','asc')"
                                        class="col-12 col-sm-6"
                                    />
                                </div>
                            </div>
                        </x-slot>
                    </x-filters-offcanvas>
                    {{-- /OFFCANVAS --}}
                </form>

                {{-- TABLA --}}
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped table-hover">
                            <thead>
                                <tr class="text-uppercase text-secondary small">
                                    <th class="text-nowrap text-center">#</th>
                                    <th>Unidad</th>
                                    <th>Placa</th>
                                    <th>Serie</th>
                                    <th>Año</th>
                                    <th>Propietario</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehiculos as $v)
                                    @php
                                        // === RELACIÓN TARJETA ===
                                        $tarjeta       = optional($v->tarjetaSiVale);
                                        $tarjetaNumero = $tarjeta->numero_tarjeta;
                                        $tarjetaNip    = $tarjeta->nip;

                                        // Normaliza fecha de vencimiento (string YYYY-MM-DD)
                                        $fechaTarjeta = $tarjeta->fecha_vencimiento;
                                        if ($fechaTarjeta instanceof \Carbon\Carbon) {
                                            $fechaTarjeta = $fechaTarjeta->toDateString();
                                        }

                                        $tarjetaLabel  = $tarjetaNumero ?: ($v->tarjeta_si_vale_id ? ('ID '.$v->tarjeta_si_vale_id) : null);

                                        // Tanque 1–1
                                        $t = optional($v->tanque);

                                        $vehData = [
                                            'id'          => $v->id,
                                            'unidad'      => $v->unidad,
                                            'placa'       => $v->placa,
                                            'serie'       => $v->serie,
                                            'marca'       => $v->marca,
                                            'anio'        => $v->anio,
                                            'propietario' => $v->propietario,
                                            'ubicacion'   => $v->ubicacion ?? null,
                                            'estado'      => $v->estado ?? null,
                                            'motor'       => $v->motor ?? null,
                                            'kilometros'  => $v->kilometros ?? null,

                                            // Tarjeta (texto plano para mostrar)
                                            'tarjeta'     => $tarjetaLabel,
                                            'tarjeta_si_vale_id' => $v->tarjeta_si_vale_id ?? null,

                                            // Datos de tarjeta (objeto)
                                            'tarjeta_si_vale' => [
                                                'numero_tarjeta'    => $tarjetaNumero,
                                                'nip'               => $tarjetaNip,          // <- NIP correcto desde la relación
                                                'fecha_vencimiento' => $fechaTarjeta,
                                            ],

                                            // Campos para el modal (nivel raíz)
                                            'nip'                       => $tarjetaNip,      // <- disponible directo
                                            'fec_vencimiento'           => $fechaTarjeta,    // <- desde tarjeta
                                            'vencimiento_t_circulacion' => $v->vencimiento_t_circulacion ?? null,
                                            'cambio_placas'             => $v->cambio_placas ?? null,
                                            'poliza_hdi'      => $v->poliza_hdi ?? null,
                                            'poliza_latino'   => $v->poliza_latino ?? null,
                                            'poliza_qualitas' => $v->poliza_qualitas ?? null,

                                            'fotos'   => isset($v->fotos) ? $v->fotos->map(fn($f) => ['id' => $f->id])->values() : [],

                                            // Tanque 1–1 (objeto)
                                            'tanque' => $t->id ? [
                                                'id'                   => $t->id,
                                                'cantidad_tanques'     => $t->cantidad_tanques,
                                                'tipo_combustible'     => $t->tipo_combustible,
                                                'capacidad_litros'     => $t->capacidad_litros,
                                                'rendimiento_estimado' => $t->rendimiento_estimado,
                                                'km_recorre'           => $t->km_recorre,
                                                'costo_tanque_lleno'   => $t->costo_tanque_lleno,
                                            ] : null,

                                            // Back-compat: arreglo con 0/1 elemento
                                            'tanques' => $t->id ? [[
                                                'id'                   => $t->id,
                                                'cantidad_tanques'     => $t->cantidad_tanques,
                                                'tipo_combustible'     => $t->tipo_combustible,
                                                'capacidad_litros'     => $t->capacidad_litros,
                                                'rendimiento_estimado' => $t->rendimiento_estimado,
                                                'km_recorre'           => $t->km_recorre,
                                                'costo_tanque_lleno'   => $t->costo_tanque_lleno,
                                            ]] : [],

                                            // URLs útiles para el modal
                                            'urls' => [
                                                'create_tanque' => route('vehiculos.tanques.create', $v),
                                                'edit_tanque'   => $t->id ? route('vehiculos.tanques.edit', [$v, $t->id]) : null,
                                                'edit_vehicle'  => route('vehiculos.edit', $v),
                                            ],
                                        ];
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap text-center">{{ $vehiculos->firstItem() + $loop->index }}</td>
                                        <td class="text-nowrap">{{ $v->unidad ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $v->placa ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $v->serie ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $v->anio ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $v->propietario ?? '—' }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button"
                                                        class="btn btn-outline-secondary btn-sm btn-view-veh"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#vehicleModal"
                                                        data-veh='@json($vehData, JSON_UNESCAPED_UNICODE)'
                                                        title="Ver detalles">
                                                    <i class="ti ti-eye me-1" aria-hidden="true"></i>Ver
                                                </button>

                                                <a href="{{ route('vehiculos.edit', $v) }}"
                                                   class="btn btn-outline-secondary btn-sm"
                                                   title="Editar">
                                                    <i class="ti ti-edit me-1" aria-hidden="true"></i>Editar
                                                </a>

                                                <form action="{{ route('vehiculos.destroy', $v) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Eliminar el vehículo #{{ $v->id }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                        <i class="ti ti-trash me-1" aria-hidden="true"></i>Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty">
                                                <div class="empty-icon">
                                                    <i class="ti ti-database-off" aria-hidden="true"></i>
                                                </div>
                                                <p class="empty-title">No hay datos</p>
                                                <p class="text-secondary empty-subtitle">
                                                    @if(request()->hasAny(['search','id','unidad','placa','serie','anio_min','anio_max','propietario','marca']))
                                                        No se encontraron resultados con los filtros aplicados.
                                                    @else
                                                        Aún no has registrado vehículos.
                                                    @endif
                                                </p>
                                                <div class="empty-action">
                                                    @if(request()->hasAny(['search','id','unidad','placa','serie','anio_min','anio_max','propietario','marca']))
                                                        <a href="{{ route('vehiculos.index') }}" class="btn btn-outline-secondary">
                                                            Limpiar filtros
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('vehiculos.create') }}" class="btn btn-primary">
                                                        <i class="ti ti-plus me-2" aria-hidden="true"></i>Agregar Vehículo
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

                {{-- PAGINACIÓN --}}
                @if(method_exists($vehiculos, 'links'))
                    @php
                        $totalAll    = $vehiculos->total();
                        $firstAll    = $vehiculos->firstItem();
                        $lastAll     = $vehiculos->lastItem();
                        $currentAll  = $vehiculos->currentPage();
                        $lastPageAll = $vehiculos->lastPage();
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
                            {{ $vehiculos->appends(request()->only([
                                'search','id','unidad','placa','serie','anio_min','anio_max','propietario','marca','sort_by','sort_dir',
                            ]))->links() }}
                        </div>
                    </div>
                @endif

                {{-- FOOTER --}}
                <div class="text-center text-secondary small py-4">
                    © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
                </div>
            </div>
        </div>

        {{-- MODAL DETALLE (completo) --}}
        <div class="modal modal-blur fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <p class="text-secondary text-uppercase small mb-1">Detalles del Vehículo</p>
                            <h3 class="modal-title h4" id="vehicleModalLabel">Vehículo</h3>
                            <div class="text-secondary small" id="vehicleModalSubtitle"></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        {{-- Datos generales --}}
                        <div class="card mb-3">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Datos generales</h4>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6 col-md-4"><div class="text-secondary small">ID</div><div class="fw-semibold" data-v="id">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Unidad</div><div class="fw-semibold" data-v="unidad">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Placa</div><div class="fw-semibold" data-v="placa">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Serie (VIN)</div><div class="fw-semibold" data-v="serie">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Marca</div><div class="fw-semibold" data-v="marca">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Año</div><div class="fw-semibold" data-v="anio">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Propietario</div><div class="fw-semibold" data-v="propietario">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Ubicación</div><div class="fw-semibold" data-v="ubicacion">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Estado</div><div class="fw-semibold" data-v="estado">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Motor</div><div class="fw-semibold" data-v="motor">—</div></div>

                                    {{-- Odómetro --}}
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Kilometraje (km)</div><div class="fw-semibold" data-v="kilometros">—</div></div>

                                    {{-- Tarjeta / documentos --}}
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Tarjeta SiVale</div><div class="fw-semibold" data-v="tarjeta">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">NIP</div><div class="fw-semibold" data-v="nip">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Venc. tarjeta</div><div class="fw-semibold" data-v="fec_vencimiento">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Venc. circ.</div><div class="fw-semibold" data-v="vencimiento_t_circulacion">—</div></div>
                                    <div class="col-6 col-md-4"><div class="text-secondary small">Cambio de placas</div><div class="fw-semibold" data-v="cambio_placas">—</div></div>

                                    {{-- Pólizas --}}
                                    <div class="col-12"><div class="text-secondary small">Póliza HDI</div><div class="fw-semibold" data-v="poliza_hdi">—</div></div>
                                    <div class="col-12"><div class="text-secondary small">Póliza Latino</div><div class="fw-semibold" data-v="poliza_latino">—</div></div>
                                    <div class="col-12"><div class="text-secondary small">Póliza Qualitas</div><div class="fw-semibold" data-v="poliza_qualitas">—</div></div>
                                </div>
                            </div>
                        </div>

                        {{-- Fotos --}}
                        <div class="card mb-3">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title mb-0">Fotos del vehículo</h4>
                                <div class="d-flex gap-2">
                                    <a id="managePhotosLink" href="#" class="btn btn-outline-secondary btn-sm">
                                        <i class="ti ti-photo-plus me-1" aria-hidden="true"></i>Gestionar fotos
                                    </a>
                                    <button id="openGalleryBtn" type="button" class="btn btn-dark btn-sm d-none">
                                        <i class="ti ti-slideshow me-1" aria-hidden="true"></i>Ver galería
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="photosEmpty" class="text-secondary small">Este vehículo no tiene fotos.</div>
                                <div id="photosGrid" class="row g-2"></div>
                            </div>
                        </div>

                        {{-- Tanque --}}
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title mb-0">Tanque de combustible</h4>
                                <a id="addTankLink" href="#" class="btn btn-success btn-sm">
                                    <i class="ti ti-square-rounded-plus me-1" aria-hidden="true"></i><span id="addTankText">Agregar</span>
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Cantidad</th>
                                                <th>Tipo</th>
                                                <th>Capacidad (L)</th>
                                                <th>Rend. (km/L)</th>
                                                <th>Km recorre</th>
                                                <th>Costo tanque lleno</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tanksTbody">
                                            <tr><td colspan="6" class="text-secondary small">Este vehículo no tiene tanque.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <a id="editVehicleLink" href="#" class="btn btn-outline-secondary">
                            <i class="ti ti-edit me-1" aria-hidden="true"></i>Editar vehículo
                        </a>
                        <button class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL GALERÍA (estilos en gallery.css) --}}
        <div class="modal modal-blur fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title h4" id="galleryModalLabel">
                            <i class="ti ti-photo me-2" aria-hidden="true"></i>Galería de fotos
                        </h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div id="galleryCarousel" class="carousel slide" data-bs-interval="false" data-bs-touch="true">
                            <div class="carousel-inner" id="galleryInner"></div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>
                    </div>

                    <div class="thumbs" id="galleryThumbs"></div>
                </div>
            </div>
        </div>

    </div>

    {{-- ====== SCRIPT: pinta datos (incluye NIP) y tanque en el modal ====== --}}
    <script>
    (() => {
      const appEl = document.getElementById('vehiculos-app');
      const baseVeh = appEl?.getAttribute('data-base-veh') || '/vehiculos';

      function nf(n) {
        if (n === null || n === undefined || n === '') return '—';
        const num = Number(n);
        return isNaN(num) ? '—' : num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }

      function setTextByDataV(key, value) {
        const el = document.querySelector(`[data-v="${key}"]`);
        if (el) el.textContent = (value ?? '—');
      }

      function setBasics(veh) {
        const subtitleEl = document.getElementById('vehicleModalSubtitle');
        const titleEl = document.getElementById('vehicleModalLabel');
        if (titleEl) titleEl.textContent = 'Vehículo';
        if (subtitleEl) {
          const unidad = veh?.unidad ?? '—';
          const placa  = veh?.placa ?? '—';
          subtitleEl.textContent = `${unidad} · ${placa}`;
        }

        const mapping = ['id','unidad','placa','serie','marca','anio','propietario','ubicacion','estado','motor','kilometros'];
        mapping.forEach(k => setTextByDataV(k, veh?.[k] ?? '—'));

        const tarjeta = veh?.tarjeta ?? (veh?.tarjeta_si_vale && veh.tarjeta_si_vale.numero_tarjeta) ?? '—';
        setTextByDataV('tarjeta', tarjeta);

        const nip = veh?.nip ?? (veh?.tarjeta_si_vale && veh.tarjeta_si_vale.nip) ?? '—';
        setTextByDataV('nip', nip);

        const fv = veh?.fec_vencimiento ?? (veh?.tarjeta_si_vale && veh.tarjeta_si_vale.fecha_vencimiento) ?? '—';
        setTextByDataV('fec_vencimiento', fv);

        setTextByDataV('vencimiento_t_circulacion', veh?.vencimiento_t_circulacion ?? '—');
        setTextByDataV('cambio_placas', veh?.cambio_placas ?? '—');
        setTextByDataV('poliza_hdi', veh?.poliza_hdi ?? '—');
        setTextByDataV('poliza_latino', veh?.poliza_latino ?? '—');
        setTextByDataV('poliza_qualitas', veh?.poliza_qualitas ?? '—');
      }

      document.querySelectorAll('.btn-view-veh').forEach(btn => {
        btn.addEventListener('click', () => {
          const veh = JSON.parse(btn.getAttribute('data-veh') || '{}');

          // Links generales
          const editVehicleLink   = document.getElementById('editVehicleLink');
          const managePhotosLink  = document.getElementById('managePhotosLink');
          if (editVehicleLink && veh?.urls?.edit_vehicle)  editVehicleLink.href  = veh.urls.edit_vehicle;
          if (managePhotosLink && veh?.urls?.edit_vehicle) managePhotosLink.href = veh.urls.edit_vehicle + '#fotos';

          // === Pinta datos básicos, tarjeta, NIP y vencimientos ===
          setBasics(veh);

          // Render tanque 1–1
          const tbody        = document.getElementById('tanksTbody');
          const addTankLink  = document.getElementById('addTankLink');
          const addTankText  = document.getElementById('addTankText');
          const t = veh?.tanque || (veh?.tanques && veh.tanques[0]) || null;

          if (tbody) {
            if (t) {
              tbody.innerHTML = `
                <tr>
                  <td>${t.cantidad_tanques ?? '—'}</td>
                  <td>${t.tipo_combustible ?? '—'}</td>
                  <td>${nf(t.capacidad_litros)}</td>
                  <td>${nf(t.rendimiento_estimado)}</td>
                  <td>${nf(t.km_recorre)}</td>
                  <td>${t.costo_tanque_lleno != null ? '$' + nf(t.costo_tanque_lleno) : '—'}</td>
                </tr>
              `;
            } else {
              tbody.innerHTML = `<tr><td colspan="6" class="text-secondary small">Este vehículo no tiene tanque.</td></tr>`;
            }
          }

          // Botón Agregar/Editar
          if (addTankLink && addTankText) {
            if (t) {
              addTankText.textContent = 'Editar';
              addTankLink.classList.remove('btn-success');
              addTankLink.classList.add('btn-warning');
              addTankLink.href = veh?.urls?.edit_tanque || `${baseVeh}/${veh.id}/tanques/${t.id}/edit`;
            } else {
              addTankText.textContent = 'Agregar';
              addTankLink.classList.remove('btn-warning');
              addTankLink.classList.add('btn-success');
              addTankLink.href = veh?.urls?.create_tanque || `${baseVeh}/${veh.id}/tanques/create`;
            }
          }
        });
      });
    })();
    </script>
</x-app-layout>
