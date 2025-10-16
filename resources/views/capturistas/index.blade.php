{{-- resources/views/capturistas/index.blade.php
     Vista Index (Tabler + Bootstrap) para gestión de Capturistas
     - Toolbar con búsqueda, exportación y filtros (offcanvas sin overlay)
     - Tabla con numeración por página (sin mostrar ID)
     - Paginación preservando parámetros de consulta
--}}

<x-app-layout>
    @php
        $q        = request();
        $ignored  = ['search','page','sort_by','sort_dir'];

        $activeCount = collect($q->except($ignored))->filter(
            fn($v) => is_array($v)
                ? collect($v)->filter(fn($x) => $x !== null && $x !== '')->isNotEmpty()
                : $v !== null && $v !== ''
        )->count();

        $search  = $q->input('search', '');
        $sortBy  = $q->input('sort_by', 'nombre_completo');
        $sortDir = $q->input('sort_dir', 'asc');

        /** @var \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator|iterable $p */
        $p = $capturistas;

        $sortOptions = [
            'nombre_completo' => 'Nombre completo',
            'email'           => 'Correo electrónico',
            'id'              => 'ID',
        ];

        $total     = method_exists($p, 'total')       ? (int) $p->total() : (is_iterable($p) ? collect($p)->count() : 0);
        $firstItem = method_exists($p, 'firstItem')   ? $p->firstItem()   : null;
        $lastItem  = method_exists($p, 'lastItem')    ? $p->lastItem()    : null;
        $current   = method_exists($p, 'currentPage') ? $p->currentPage() : 1;
        $lastPage  = method_exists($p, 'lastPage')    ? $p->lastPage()    : 1;

        $keepParams = ['search','sort_by','sort_dir'];
    @endphp

    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a>Inicio</a></li>
                            <li class="breadcrumb-item"><a>Panel</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Gestión de Capturistas</li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title mb-0">Gestión de Capturistas</h2>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('capturistas.create') }}" class="btn btn-primary">
                            <i class="ti ti-user-plus me-1" aria-hidden="true"></i>
                            <span>Agregar Nuevo Capturista</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- Mensaje de operación exitosa --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <i class="ti ti-check me-2" aria-hidden="true"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            {{-- ================= FORM GLOBAL (GET) SOLO PARA FILTROS/BÚSQUEDA ================= --}}
            <form id="filtersForm" method="GET" action="{{ route('capturistas.index') }}" autocomplete="off" novalidate aria-label="Búsqueda y filtros de capturistas">

                {{-- ===== Toolbar: búsqueda + exportación + filtros ===== --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-center">
                            {{-- Búsqueda global --}}
                            <div class="col-12 col-xl">
                                <div class="input-group" role="search" aria-label="Buscar en capturistas">
                                    <span class="input-group-text" id="icon-search">
                                        <i class="ti ti-search" aria-hidden="true"></i>
                                    </span>
                                    <input
                                        type="text"
                                        name="search"
                                        value="{{ $search }}"
                                        class="form-control"
                                        placeholder="Buscar…"
                                        aria-label="Término de búsqueda"
                                        aria-describedby="icon-search"
                                    >
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti ti-search me-1" aria-hidden="true"></i>Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Acciones rápidas --}}
                            <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">

                                {{-- Exportar (genera URL con ?export=xlsx preservando filtros) --}}
                                <a href="{{ route('capturistas.index', array_merge(request()->except('page'), ['export' => 'xlsx'])) }}"
                                   class="btn btn-outline-success"
                                   title="Exportar a Excel">
                                    <i class="ti ti-brand-excel me-1" aria-hidden="true"></i>
                                    <span>Exportar</span>
                                </a>

                                {{-- Filtros (offcanvas sin overlay) --}}
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

                        {{-- Resumen contextual cuando hay término de búsqueda --}}
                        @if($search !== '')
                            <div class="mt-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between" role="status">
                                <div class="small">
                                    <span class="badge bg-secondary text-uppercase">Filtro</span>
                                    <span class="ms-2">“{{ $search }}”</span>
                                </div>
                                <div class="text-secondary small mt-2 mt-sm-0">
                                    @if($total === 0)
                                        Sin resultados para la búsqueda.
                                    @elseif($total === 1)
                                        Resultado <strong>(1 de 1)</strong>
                                    @else
                                        Página <strong>{{ $current }}</strong> de <strong>{{ $lastPage }}</strong> —
                                        Mostrando <strong>{{ $firstItem }}–{{ $lastItem }}</strong> de <strong>{{ $total }}</strong>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ================= OFFCANVAS DE FILTROS (DENTRO DEL MISMO FORM) ================= --}}
                <div
                    class="offcanvas offcanvas-end"
                    tabindex="-1"
                    id="filtersOffcanvas"
                    aria-labelledby="filtersOffcanvasLabel"
                    data-bs-backdrop="false"
                    data-bs-scroll="true">
                    <div class="offcanvas-header">
                        <h2 class="offcanvas-title h4" id="filtersOffcanvasLabel">
                            <i class="ti ti-adjustments me-2" aria-hidden="true"></i>Filtros
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                    </div>

                    <div class="offcanvas-body">
                        {{-- Orden y dirección --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Orden y vista</div>
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="sort_by">Ordenar por</label>
                                    <select id="sort_by" name="sort_by" class="form-select">
                                        @foreach($sortOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($sortBy === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="sort_dir">Dirección</label>
                                    <select id="sort_dir" name="sort_dir" class="form-select">
                                        <option value="asc"  @selected($sortDir === 'asc')>Ascendente</option>
                                        <option value="desc" @selected($sortDir === 'desc')>Descendente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Espacio para filtros futuros --}}
                        <div class="mb-2">
                            <div class="text-secondary small">Cuando existan más campos filtrables en el modelo, agréguelos aquí.</div>
                        </div>
                    </div>

                    <div class="offcanvas-footer d-flex justify-content-between align-items-center p-3 border-top">
                        <a href="{{ route('capturistas.index') }}" class="btn btn-link">Limpiar filtros</a>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="offcanvas">Cerrar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1" aria-hidden="true"></i>Aplicar filtros
                            </button>
                        </div>
                    </div>
                </div>
                {{-- /OFFCANVAS --}}
            </form>
            {{-- ===== FIN DEL FORM GET (IMPORTANTE: NO ENVUELVE LA TABLA) ===== --}}

            {{-- ================= TABLA ================= --}}
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped table-hover">
                        <thead>
                            <tr class="text-uppercase text-secondary small">
                                <th class="text-center text-nowrap">#</th>
                                <th>Nombre completo</th>
                                <th>Correo electrónico</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($p as $cap)
                                @php
                                    $nombre = $cap->nombre_completo
                                        ?? trim(collect([$cap->nombre ?? '', $cap->apellido_paterno ?? '', $cap->apellido_materno ?? ''])
                                            ->filter(fn($x) => $x !== '')
                                            ->implode(' '));

                                    $correo = data_get($cap, 'user.email', '—');
                                @endphp
                                <tr>
                                    {{-- Numeración por página --}}
                                    <td class="text-center text-nowrap">{{ $loop->iteration }}</td>

                                    {{-- Nombre --}}
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar avatar-sm avatar-rounded bg-azure-lt" aria-hidden="true">
                                                <i class="ti ti-user"></i>
                                            </span>
                                            <div class="lh-1">
                                                <div class="fw-semibold">{{ $nombre !== '' ? $nombre : 'Capturista' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Correo (truncado con title) --}}
                                    <td class="text-nowrap">
                                        <div class="text-truncate" style="max-width: 280px" title="{{ $correo }}">{{ $correo }}</div>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            {{-- Ver (usa edit como placeholder si no hay ruta show) --}}
                                            <a href="{{ route('capturistas.edit', $cap) }}"
                                               class="btn btn-outline-secondary btn-sm"
                                               title="Ver">
                                                <i class="ti ti-eye me-1" aria-hidden="true"></i>Ver
                                            </a>

                                            {{-- Editar --}}
                                            <a href="{{ route('capturistas.edit', $cap) }}"
                                               class="btn btn-outline-secondary btn-sm"
                                               title="Editar">
                                                <i class="ti ti-edit me-1" aria-hidden="true"></i>Editar
                                            </a>

                                            {{-- Eliminar (FORM INDEPENDIENTE, SIN NESTING) --}}
                                            <form
                                                action="{{ route('capturistas.destroy', $cap) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('¿Seguro que deseas eliminar a {{ $nombre !== '' ? $nombre : 'este capturista' }}?');">
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
                                {{-- Estado vacío con componente de Tabler --}}
                                <tr>
                                    <td colspan="4" class="py-6">
                                        <div class="empty">
                                            <div class="empty-icon">
                                                <i class="ti ti-users-off" aria-hidden="true"></i>
                                            </div>
                                            <p class="empty-title">No hay datos</p>
                                            <p class="empty-subtitle text-secondary">
                                                @if($search !== '')
                                                    No se encontraron resultados con los filtros aplicados.
                                                @else
                                                    Aún no has registrado capturistas.
                                                @endif
                                            </p>
                                            <div class="empty-action">
                                                @if($search !== '')
                                                    <a href="{{ route('capturistas.index') }}" class="btn btn-outline-secondary">
                                                        Limpiar filtros
                                                    </a>
                                                @endif
                                                <a href="{{ route('capturistas.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-user-plus me-2" aria-hidden="true"></i>Agregar Nuevo Capturista
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

            {{-- ================= PAGINACIÓN ================= --}}
            @if(method_exists($p, 'links'))
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between mt-3">
                    <p class="text-secondary small mb-2 mb-sm-0">
                        @if($total === 0)
                            Mostrando 0 resultados
                        @elseif($total === 1)
                            Resultado <strong>(1 de 1)</strong>
                        @else
                            Página <strong>{{ $current }}</strong> de <strong>{{ $lastPage }}</strong> —
                            Mostrando <strong>{{ $firstItem }}–{{ $lastItem }}</strong> de <strong>{{ $total }}</strong> resultados
                        @endif
                    </p>
                    <div>
                        {{ $p->appends($q->only($keepParams))->links() }}
                    </div>
                </div>
            @endif

            {{-- ================= FOOTER ================= --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>
</x-app-layout>
