{{-- resources/views/vehiculos/index.blade.php — Versión 100% Tabler (modales, offcanvas, tabla y galería estilados a lo Tabler) --}}
<x-app-layout>
    {{-- Si ya incluyes @vite en tu layout con Tabler (CSS/JS), puedes quitar esta línea --}}
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

    {{-- ===== HEADER ===== --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title mb-0">Gestión de Vehículos</h2>
                        <div class="text-secondary small mt-1">Consulta, filtra y administra tu flota.</div>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('vehiculos.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Agregar Vehículo
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

            {{-- ===== FORM GLOBAL (GET) ===== --}}
            <form method="GET" action="{{ route('vehiculos.index') }}">
                {{-- TOOLBAR --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-center">
                            {{-- Búsqueda --}}
                            <div class="col-12 col-xl">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    <input type="text"
                                           name="search"
                                           value="{{ request('search') }}"
                                           class="form-control"
                                           placeholder="Buscar por: ID, Unidad, Placa, Serie, Año, Propietario…"
                                           aria-label="Búsqueda global">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti ti-search me-1"></i> Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Acciones --}}
                            <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">
                                {{-- Exportar --}}
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-download me-1"></i> Exportar
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a>
                                        <a class="dropdown-item" href="#"><i class="ti ti-file-description me-2"></i>PDF</a>
                                    </div>
                                </div>

                                {{-- Botón Filtros (Offcanvas) --}}
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
                                $total   = $vehiculos->total();
                                $first   = $vehiculos->firstItem();
                                $last    = $vehiculos->lastItem();
                                $current = $vehiculos->currentPage();
                                $lastPage= $vehiculos->lastPage();
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

                {{-- ===== OFFCANVAS DE FILTROS (Tabler) ===== --}}
                <div class="offcanvas offcanvas-end" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel">
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
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">ID</label>
                                    <input type="number" name="id" value="{{ request('id') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Unidad</label>
                                    <input type="text" name="unidad" value="{{ request('unidad') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Placa</label>
                                    <input type="text" name="placa" value="{{ request('placa') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Serie (VIN)</label>
                                    <input type="text" name="serie" value="{{ request('serie') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Propietario</label>
                                    <input type="text" name="propietario" value="{{ request('propietario') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Marca</label>
                                    <select name="marca" class="form-select">
                                        <option value="">Todas</option>
                                        @foreach(($marcas ?? []) as $m)
                                            <option value="{{ $m }}" @selected(request('marca') == $m)>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Año (rango) --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Año</div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="anio_min" value="{{ request('anio_min') }}" class="form-control" placeholder="mín">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="anio_max" value="{{ request('anio_max') }}" class="form-control" placeholder="máx">
                                </div>
                            </div>
                        </div>

                        {{-- Ordenar --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Orden</div>
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <select name="sort_by" class="form-select" title="Ordenar por">
                                        @foreach($columns as $k => $label)
                                            <option value="{{ $k }}" @selected(request('sort_by','created_at')===$k)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <select name="sort_dir" class="form-select" title="Dirección">
                                        <option value="asc"  @selected(request('sort_dir','asc')==='asc')>Ascendente</option>
                                        <option value="desc" @selected(request('sort_dir')==='desc')>Descendente</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="offcanvas-footer d-flex justify-content-between align-items-center p-3 border-top">
                        <a href="{{ route('vehiculos.index') }}" class="btn btn-link">Limpiar filtros</a>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="offcanvas">Cerrar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1"></i>Aplicar filtros
                            </button>
                        </div>
                    </div>
                </div>
                {{-- /OFFCANVAS --}}
            </form>

            {{-- ===== TABLA ===== --}}
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped table-hover">
                        <thead>
                            <tr class="text-uppercase text-secondary small">
                                <th class="text-nowrap">ID</th>
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
                                        'tarjeta_si_vale_id' => $v->tarjeta_si_vale_id ?? null,
                                        'tarjeta_si_vale'    => isset($v->tarjetaSiVale) ? ['numero_tarjeta' => $v->tarjetaSiVale->numero_tarjeta] : null,
                                        'nip'         => $v->nip ?? null,
                                        'fec_vencimiento'              => $v->fec_vencimiento ?? null,
                                        'vencimiento_t_circulacion'    => $v->vencimiento_t_circulacion ?? null,
                                        'cambio_placas'                => $v->cambio_placas ?? null,
                                        'poliza_hdi'                   => $v->poliza_hdi ?? null,
                                        'fotos'   => isset($v->fotos)   ? $v->fotos->map(fn($f)=>['id'=>$f->id])->values() : [],
                                        'tanques' => isset($v->tanques) ? $v->tanques->map(fn($t)=>[
                                                            'id' => $t->id,
                                                            'numero_tanque' => $t->numero_tanque,
                                                            'tipo_combustible' => $t->tipo_combustible,
                                                            'capacidad_litros' => $t->capacidad_litros,
                                                            'rendimiento_estimado' => $t->rendimiento_estimado,
                                                            'km_recorre' => $t->km_recorre,
                                                            'costo_tanque_lleno' => $t->costo_tanque_lleno,
                                                        ])->values() : [],
                                    ];
                                @endphp
                                <tr>
                                    <td class="text-nowrap">#{{ $v->id }}</td>
                                    <td class="text-nowrap">{{ $v->unidad ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $v->placa ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $v->serie ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $v->anio ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $v->propietario ?? '—' }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            {{-- Ver --}}
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm btn-view-veh"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#vehicleModal"
                                                    data-veh='@json($vehData, JSON_UNESCAPED_UNICODE)'
                                                    title="Ver detalles">
                                                <i class="ti ti-eye me-1"></i>Ver
                                            </button>

                                            {{-- Editar --}}
                                            <a href="{{ route('vehiculos.edit', $v) }}"
                                               class="btn btn-outline-secondary btn-sm"
                                               title="Editar">
                                                <i class="ti ti-edit me-1"></i>Editar
                                            </a>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('vehiculos.destroy', $v) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar el vehículo #{{ $v->id }}?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                    <i class="ti ti-trash me-1"></i>Eliminar
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
                                                <i class="ti ti-database-off"></i>
                                            </div>
                                            <p class="empty-title">No hay datos</p>
                                            <p class="empty-subtitle text-secondary">
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
                                                    <i class="ti ti-plus me-2"></i>Agregar Vehículo
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
            @if(method_exists($vehiculos, 'links'))
                @php
                    $totalAll   = $vehiculos->total();
                    $firstAll   = $vehiculos->firstItem();
                    $lastAll    = $vehiculos->lastItem();
                    $currentAll = $vehiculos->currentPage();
                    $lastPageAll= $vehiculos->lastPage();
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

    {{-- ===== MODAL DETALLE (Tabler modal-blur) ===== --}}
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
                        <div class="card-header"><h4 class="card-title mb-0">Datos generales</h4></div>
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
                                <div class="col-6 col-md-4"><div class="text-secondary small">Tarjeta SiVale</div><div class="fw-semibold" data-v="tarjeta">—</div></div>
                                <div class="col-6 col-md-4"><div class="text-secondary small">NIP</div><div class="fw-semibold" data-v="nip">—</div></div>
                                <div class="col-6 col-md-4"><div class="text-secondary small">Venc. tarjeta</div><div class="fw-semibold" data-v="fec_vencimiento">—</div></div>
                                <div class="col-6 col-md-4"><div class="text-secondary small">Venc. circ.</div><div class="fw-semibold" data-v="vencimiento_t_circulacion">—</div></div>
                                <div class="col-6 col-md-4"><div class="text-secondary small">Cambio de placas</div><div class="fw-semibold" data-v="cambio_placas">—</div></div>
                                <div class="col-12"><div class="text-secondary small">Póliza HDI</div><div class="fw-semibold" data-v="poliza_hdi">—</div></div>
                            </div>
                        </div>
                    </div>

                    {{-- Fotos --}}
                    <div class="card mb-3">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Fotos del vehículo</h4>
                            <div class="d-flex gap-2">
                                <a id="managePhotosLink" href="#" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-photo-plus me-1"></i>Gestionar fotos
                                </a>
                                <button id="openGalleryBtn" type="button" class="btn btn-dark btn-sm d-none">
                                    <i class="ti ti-slideshow me-1"></i>Ver galería
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="photosEmpty" class="text-secondary small">Este vehículo no tiene fotos.</div>
                            <div id="photosGrid" class="row g-2"></div>
                        </div>
                    </div>

                    {{-- Tanques --}}
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Tanques de combustible</h4>
                            <a id="addTankLink" href="#" class="btn btn-success btn-sm">
                                <i class="ti ti-square-rounded-plus me-1"></i>Agregar
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tipo</th>
                                            <th>Capacidad (L)</th>
                                            <th>Rend. (km/L)</th>
                                            <th>Km recorre</th>
                                            <th>Costo tanque lleno</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tanksTbody">
                                        <tr><td colspan="6" class="text-secondary small">Este vehículo no tiene tanques.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <a id="editVehicleLink" href="#" class="btn btn-outline-secondary">
                        <i class="ti ti-edit me-1"></i>Editar vehículo
                    </a>
                    <button class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL GALERÍA (Tabler look & feel) ===== --}}
    <style>
        /* Lightbox estilo Tabler: contenido contenido, sin desbordes, con miniaturas */
        #galleryModal .modal-dialog { max-width: min(96vw, 1200px); }
        #galleryModal .modal-content { background: #0b0b0b; color: #fff; border: 0; }
        #galleryModal .modal-header { border: 0; background: transparent; }
        #galleryModal .btn-close { filter: invert(1); opacity: .9; }
        #galleryModal .modal-body { padding: 0; background: #000; }
        #galleryModal .carousel,
        #galleryModal .carousel-inner { height: 82vh; }
        @media (max-width: 768px){
            #galleryModal .carousel,
            #galleryModal .carousel-inner { height: 75vh; }
        }
        #galleryModal .carousel-item { 
  height: 100%;              /* mantenemos la altura, sin display */
}

/* Solo las slides visibles durante/tras la animación usan flex para centrar */
#galleryModal .carousel-item.active,
#galleryModal .carousel-item-next,
#galleryModal .carousel-item-prev,
#galleryModal .carousel-item-start,
#galleryModal .carousel-item-end {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* El resto de tu CSS puede quedarse igual */
#galleryModal .carousel,
#galleryModal .carousel-inner { height: 82vh; }
@media (max-width: 768px){
  #galleryModal .carousel,
  #galleryModal .carousel-inner { height: 75vh; }
}
#galleryModal .lightbox-img {
  max-height: 80vh; width: auto; max-width: 100%;
  object-fit: contain; user-select: none;
}
#galleryModal .carousel-control-prev,
#galleryModal .carousel-control-next { filter: drop-shadow(0 0 6px rgba(0,0,0,.6)); }
#galleryModal .carousel-control-prev-icon,
#galleryModal .carousel-control-next-icon { width: 3rem; height: 3rem; }
#galleryModal .thumbs { display:flex; gap:.5rem; overflow-x:auto; scrollbar-width:thin; padding:.75rem 1rem 1rem; background:#0b0b0b; }
#galleryModal .thumb { flex:0 0 auto; width:76px; height:56px; border-radius:.5rem; overflow:hidden; border:2px solid transparent; cursor:pointer; opacity:.9; }
#galleryModal .thumb:hover { opacity:1; }
#galleryModal .thumb img { width:100%; height:100%; object-fit:cover; display:block; }
#galleryModal .thumb.active { border-color:#5b9cff; }


        
    </style>

    <div class="modal modal-blur fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title h4" id="galleryModalLabel">
                        <i class="ti ti-photo me-2"></i>Galería de fotos
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

    {{-- ===== SCRIPTS (Bootstrap via Tabler) ===== --}}
    <script>
    // Tabler ya incluye Bootstrap 5; si además cargas bootstrap.bundle por Vite,
    // asegúrate de no duplicar (preferimos la instancia global existente).
    document.addEventListener('DOMContentLoaded', () => {
        const basePhotoUrl = "{{ url('/vehiculos/fotos') }}";
        const baseVehUrl   = "{{ url('/vehiculos') }}";

        // ====== SELECTORES (modal detalle) ======
        const vehicleModalEl   = document.getElementById('vehicleModal');
        const titleEl          = vehicleModalEl.querySelector('#vehicleModalLabel');
        const subtitleEl       = vehicleModalEl.querySelector('#vehicleModalSubtitle');
        const managePhotosLink = vehicleModalEl.querySelector('#managePhotosLink');
        const addTankLink      = vehicleModalEl.querySelector('#addTankLink');
        const editVehicleLink  = vehicleModalEl.querySelector('#editVehicleLink');
        const photosGrid       = vehicleModalEl.querySelector('#photosGrid');
        const photosEmpty      = vehicleModalEl.querySelector('#photosEmpty');
        const openGalleryBtn   = vehicleModalEl.querySelector('#openGalleryBtn');
        const tanksTbody       = vehicleModalEl.querySelector('#tanksTbody');

        // ====== SELECTORES (galería) ======
        const galleryModalEl    = document.getElementById('galleryModal');
        const galleryCarouselEl = document.getElementById('galleryCarousel');
        const galleryInner      = document.getElementById('galleryInner');
        const galleryThumbs     = document.getElementById('galleryThumbs');

        // Helpers
        const fmt = v => (v ?? '') !== '' ? v : '—';
        const fmtDate = v => { if(!v) return '—'; const d = new Date(v); return isNaN(d) ? v : d.toLocaleDateString('es-MX',{year:'numeric',month:'2-digit',day:'2-digit'}); };
        const fmtNum = n => (n===''||n==null) ? '—' : (isNaN(+n)?'—':(+n).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2}));
        const fmtMoney = n => (n===''||n==null) ? '—' : (isNaN(+n)?'—':(+n).toLocaleString('es-MX',{style:'currency',currency:'MXN'}));

        // Toma la clase Carousel desde el entorno (Tabler/Bootstrap)
        const getCarouselCtor = () => (window.bootstrap?.Carousel) || (window.Carousel) || null;
        const getModalCtor    = () => (window.bootstrap?.Modal) || (window.Modal) || null;

        let currentVeh = null;

        function fillModal(veh){
            currentVeh = veh || {};

            titleEl.textContent    = veh.unidad ? `Unidad: ${veh.unidad}` : `Vehículo #${veh.id ?? ''}`;
            subtitleEl.textContent = veh.placa ? `Placa: ${veh.placa}` : '';

            const fields = {
                id: fmt(veh.id), unidad: fmt(veh.unidad), placa: fmt(veh.placa),
                serie: fmt(veh.serie), marca: fmt(veh.marca), anio: fmt(veh.anio),
                propietario: fmt(veh.propietario), ubicacion: fmt(veh.ubicacion),
                estado: fmt(veh.estado), motor: fmt(veh.motor),
                tarjeta: fmt(veh?.tarjeta_si_vale?.numero_tarjeta ?? veh?.tarjeta_si_vale_id),
                nip: fmt(veh.nip),
                fec_vencimiento: fmtDate(veh.fec_vencimiento),
                vencimiento_t_circulacion: fmtDate(veh.vencimiento_t_circulacion),
                cambio_placas: fmtDate(veh.cambio_placas),
                poliza_hdi: fmt(veh.poliza_hdi),
            };
            Object.entries(fields).forEach(([k,v])=>{
                const el = vehicleModalEl.querySelector(`[data-v="${k}"]`);
                if(el) el.textContent = v;
            });

            const id = veh.id;
            managePhotosLink.href = `${baseVehUrl}/${id}/fotos`;
            addTankLink.href      = `${baseVehUrl}/${id}/tanques/create`;
            editVehicleLink.href  = `${baseVehUrl}/${id}/edit`;

            // Miniaturas en el modal de detalle
            photosGrid.innerHTML = '';
            const fotos = Array.isArray(veh.fotos) ? veh.fotos : [];
            if(!fotos.length){
                photosEmpty.classList.remove('d-none');
                openGalleryBtn.classList.add('d-none');
            } else {
                photosEmpty.classList.add('d-none');
                openGalleryBtn.classList.remove('d-none');
                fotos.forEach((f, idx)=>{
                    const col = document.createElement('div');
                    col.className = 'col-6 col-sm-4 col-md-3';
                    col.innerHTML = `
                        <a href="#" class="card card-link" data-gallery-index="${idx}">
                            <div class="img-responsive img-responsive-4x3 card-img-top"
                                 style="background-image: url('${basePhotoUrl}/${f.id}')"></div>
                        </a>`;
                    photosGrid.appendChild(col);
                });
            }

            // Tanques
            tanksTbody.innerHTML = '';
            const tanques = Array.isArray(veh.tanques) ? veh.tanques : [];
            if(!tanques.length){
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="6" class="text-secondary small">Este vehículo no tiene tanques.</td>`;
                tanksTbody.appendChild(tr);
            } else {
                tanques.forEach(t=>{
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${fmt(t.numero_tanque)}</td>
                        <td>${fmt(t.tipo_combustible)}</td>
                        <td>${fmtNum(t.capacidad_litros)}</td>
                        <td>${fmtNum(t.rendimiento_estimado)}</td>
                        <td>${fmtNum(t.km_recorre)}</td>
                        <td>${fmtMoney(t.costo_tanque_lleno)}</td>`;
                    tanksTbody.appendChild(tr);
                });
            }
        }

        // ====== LIGHTBOX Tabler-like ======
        function updateThumbsActive(i){
            [...galleryThumbs.querySelectorAll('.thumb')].forEach((el,idx)=>{
                el.classList.toggle('active', idx === i);
            });
        }

        function buildCarouselSlides(startIndex = 0){
            galleryInner.innerHTML = '';
            galleryThumbs.innerHTML = '';

            const fotos = Array.isArray(currentVeh?.fotos) ? currentVeh.fotos : [];
            fotos.forEach((f,i)=>{
                const item = document.createElement('div');
                item.className = 'carousel-item' + (i===startIndex ? ' active' : '');
                item.dataset.index = i;
                item.innerHTML = `<img src="${basePhotoUrl}/${f.id}" class="lightbox-img" alt="Foto ${i+1}">`;
                galleryInner.appendChild(item);

                const th = document.createElement('button');
                th.type = 'button';
                th.className = 'thumb' + (i===startIndex ? ' active' : '');
                th.dataset.index = i;
                th.innerHTML = `<img src="${basePhotoUrl}/${f.id}" alt="Miniatura ${i+1}">`;
                th.addEventListener('click', () => {
                    const Carousel = getCarouselCtor();
                    if (!Carousel) return;
                    const car = Carousel.getInstance(galleryCarouselEl);
                    car?.to(i);
                    updateThumbsActive(i);
                });
                galleryThumbs.appendChild(th);
            });

            const Carousel = getCarouselCtor();
            if (Carousel) {
                const existing = Carousel.getInstance(galleryCarouselEl);
                existing?.dispose();
                const carousel = new Carousel(galleryCarouselEl, {
                    interval: false, ride: false, wrap: true, keyboard: true, touch: true
                });

                galleryCarouselEl.addEventListener('slid.bs.carousel', () => {
                    const idx = [...galleryInner.children].findIndex(el => el.classList.contains('active'));
                    updateThumbsActive(idx);
                }, { passive: true });

                if (startIndex > 0) carousel.to(startIndex);
            }
        }

        function openGallery(startIndex = 0){
            if(!currentVeh || !currentVeh.fotos || !currentVeh.fotos.length) return;
            buildCarouselSlides(startIndex);
            const Modal = getModalCtor();
            if (!Modal) return;
            const modal = new Modal(galleryModalEl);
            modal.show();
        }

        // Clic en miniatura del modal de detalle
        photosGrid.addEventListener('click', (e) => {
            const a = e.target.closest('[data-gallery-index]');
            if (!a) return;
            e.preventDefault();
            const idx = parseInt(a.getAttribute('data-gallery-index'), 10) || 0;
            openGallery(idx);
        });

        // Botón "Ver galería"
        openGalleryBtn.addEventListener('click', () => openGallery(0));

        // Delegación: botón "Ver" en la tabla => inyecta data en el modal antes de abrirlo
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-view-veh');
            if (!btn) return;
            try {
                const payload = JSON.parse(btn.getAttribute('data-veh') || '{}');
                fillModal(payload);
            } catch (err) {
                console.error('JSON inválido en data-veh:', err);
            }
        });
    });
    </script>
</x-app-layout>
