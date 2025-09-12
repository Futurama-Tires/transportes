{{-- resources/views/operadores/index.blade.php — versión Tabler (acciones separadas, filtros en offcanvas y numeración sin mostrar ID) --}}
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
                        <h2 class="page-title mb-0">Gestión de Operadores</h2>
                        <div class="text-secondary small mt-1">Consulta, filtra y gestiona a tus operadores.</div>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('operadores.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>
                            Agregar Nuevo Operador
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

            {{-- FORM GLOBAL (GET) --}}
            <form method="GET" action="{{ route('operadores.index') }}">

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
                                           placeholder="Buscar por nombre, apellidos, correo…"
                                           aria-label="Búsqueda global">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti ti-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Acciones --}}
                            <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-download me-1"></i>Exportar
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a>
                                        <a class="dropdown-item" href="#"><i class="ti ti-file-description me-2"></i>PDF</a>
                                    </div>
                                </div>

                                {{-- Botón Filtros (abre Offcanvas) --}}
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
                                $total   = $operadores->total();
                                $first   = $operadores->firstItem();
                                $last    = $operadores->lastItem();
                                $current = $operadores->currentPage();
                                $lastPage= $operadores->lastPage();
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

                {{-- OFFCANVAS DE FILTROS (incluye ordenación) --}}
                <div class="offcanvas offcanvas-end" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel">
                    <div class="offcanvas-header">
                        <h2 class="offcanvas-title h4" id="filtersOffcanvasLabel">
                            <i class="ti ti-adjustments me-2"></i>Filtros
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                    </div>
                    <div class="offcanvas-body">
                        {{-- Orden y dirección --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Orden y vista</div>
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    @php
                                        $opts = [
                                            'nombre_completo' => 'Nombre completo',
                                            'email'           => 'Correo electrónico',
                                            'id'              => 'ID',
                                        ];
                                    @endphp
                                    <label class="form-label">Ordenar por</label>
                                    <select name="sort_by" class="form-select">
                                        @foreach($opts as $val => $label)
                                            <option value="{{ $val }}" @selected(request('sort_by','nombre_completo')===$val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Dirección</label>
                                    <select name="sort_dir" class="form-select">
                                        <option value="asc"  @selected(request('sort_dir','asc')==='asc')>Ascendente</option>
                                        <option value="desc" @selected(request('sort_dir','asc')==='desc')>Descendente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Espacio para futuros filtros (estatus, etc.) --}}
                        <div class="mb-2">
                            <div class="text-secondary small">Puedes añadir más filtros aquí cuando existan en el modelo.</div>
                        </div>
                    </div>

                    <div class="offcanvas-footer d-flex justify-content-between align-items-center p-3 border-top">
                        <a href="{{ route('operadores.index') }}" class="btn btn-link">Limpiar filtros</a>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="offcanvas">Cerrar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1"></i>Aplicar filtros
                            </button>
                        </div>
                    </div>
                </div>
                {{-- /OFFCANVAS --}}

                {{-- TABLA --}}
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
                                @forelse($operadores as $op)
                                    @php
                                        $nombreCompleto = trim(($op->nombre ?? '').' '.($op->apellido_paterno ?? '').' '.($op->apellido_materno ?? ''));
                                        $correo = optional($op->user)->email ?? '—';
                                    @endphp
                                    <tr>
                                        {{-- Numeración independiente del filtro/orden (reinicia por página) --}}
                                        <td class="text-center text-nowrap">{{ $loop->iteration }}</td>

                                        <td class="text-nowrap">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="avatar avatar-sm avatar-rounded bg-blue-lt">
                                                    <i class="ti ti-user"></i>
                                                </span>
                                                <div class="lh-1">
                                                    <div class="fw-semibold">{{ $nombreCompleto ?: 'Operador' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-nowrap">
                                            <div class="text-truncate" style="max-width: 280px" title="{{ $correo }}">{{ $correo }}</div>
                                        </td>

                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                {{-- Ver (placeholder hacia edit si no hay show) --}}
                                                <a href="{{ route('operadores.edit', $op->id) }}"
                                                   class="btn btn-outline-secondary btn-sm"
                                                   title="Ver">
                                                    <i class="ti ti-eye me-1"></i>Ver
                                                </a>

                                                {{-- Editar --}}
                                                <a href="{{ route('operadores.edit', $op->id) }}"
                                                   class="btn btn-outline-secondary btn-sm"
                                                   title="Editar">
                                                    <i class="ti ti-edit me-1"></i>Editar
                                                </a>

                                                {{-- Eliminar --}}
                                                <form action="{{ route('operadores.destroy', $op->id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Seguro que quieres eliminar a {{ $nombreCompleto ?: 'este operador' }}?');">
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
                                        <td colspan="4" class="py-6">
                                            <div class="empty">
                                                <div class="empty-icon">
                                                    <i class="ti ti-database-off"></i>
                                                </div>
                                                <p class="empty-title">No hay datos</p>
                                                <p class="empty-subtitle text-secondary">
                                                    @if(request()->hasAny(['search']))
                                                        No se encontraron resultados con los filtros aplicados.
                                                    @else
                                                        Aún no has registrado operadores.
                                                    @endif
                                                </p>
                                                <div class="empty-action">
                                                    @if(request()->hasAny(['search']))
                                                        <a href="{{ route('operadores.index') }}" class="btn btn-outline-secondary">
                                                            Limpiar filtros
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('operadores.create') }}" class="btn btn-primary">
                                                        <i class="ti ti-plus me-2"></i>Agregar Nuevo Operador
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
                @if(method_exists($operadores, 'links'))
                    @php
                        $totalAll   = $operadores->total();
                        $firstAll   = $operadores->firstItem();
                        $lastAll    = $operadores->lastItem();
                        $currentAll = $operadores->currentPage();
                        $lastPageAll= $operadores->lastPage();
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
                            {{ $operadores->appends(request()->only([
                                'search','sort_by','sort_dir',
                            ]))->links() }}
                        </div>
                    </div>
                @endif

            </form>

            {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>
</x-app-layout>
