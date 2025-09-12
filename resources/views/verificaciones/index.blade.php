{{-- resources/views/verificaciones/index.blade.php --}}
<x-app-layout>
    {{-- Encabezado de página (Tabler) --}}
    <div class="page-header d-print-none mb-3">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-clipboard-check me-2"></i>
                        Gestión de Verificaciones
                    </h2>
                    <div class="text-secondary small">
                        Administra búsquedas, filtros, exportaciones y acciones rápidas.
                    </div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('verificaciones.create') }}" class="btn btn-primary">
                            <i class="ti ti-square-plus me-1"></i>
                            Nueva Verificación
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido --}}
    <div class="page-body">
        <div class="container-xl">

            {{-- Flash éxito (Tabler alert) --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <i class="ti ti-checks me-2"></i>
                    {{ session('success') }}
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            {{-- Barra superior: búsqueda + filtros + exportaciones --}}
            <div class="row g-3 align-items-end mb-3">
                {{-- Formulario de búsqueda y filtros --}}
                <div class="col-12 col-lg">
                    <form method="GET" action="{{ route('verificaciones.index') }}">
                        <div class="row g-2">
                            {{-- Buscador --}}
                            <div class="col-12 col-md-6">
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-search"></i>
                                    </span>
                                    <input
                                        type="text"
                                        name="search"
                                        value="{{ request('search') }}"
                                        class="form-control"
                                        placeholder="Buscar por estado, placa, propietario, comentarios, ID…"
                                        aria-label="Buscar verificaciones"
                                    >
                                </div>
                            </div>

                            {{-- Vehículo --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label">Vehículo</label>
                                <select name="vehiculo_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Todos los vehículos</option>
                                    @foreach($vehiculos as $v)
                                        <option value="{{ $v->id }}" @selected((string)$v->id === request('vehiculo_id'))>
                                            {{ $v->unidad }} @if($v->placa) — {{ $v->placa }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Estado --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select" onchange="this.form.submit()">
                                    <option value="">Todos los estados</option>
                                    @foreach($estados as $e)
                                        <option value="{{ $e }}" @selected($e === request('estado'))>{{ $e }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Desde --}}
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label">Desde</label>
                                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                            </div>

                            {{-- Hasta --}}
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                            </div>

                            {{-- Ordenar por --}}
                            <div class="col-12 col-md-4 col-lg-3">
                                <label class="form-label">Ordenar por</label>
                                <select name="sort_by" class="form-select">
                                    <option value="fecha_verificacion" @selected(request('sort_by','fecha_verificacion')==='fecha_verificacion')>Fecha</option>
                                    <option value="vehiculo" @selected(request('sort_by')==='vehiculo')>Vehículo</option>
                                    <option value="estado" @selected(request('sort_by')==='estado')>Estado</option>
                                    <option value="placa" @selected(request('sort_by')==='placa')>Placa</option>
                                    <option value="propietario" @selected(request('sort_by')==='propietario')>Propietario</option>
                                    <option value="id" @selected(request('sort_by')==='id')>ID</option>
                                </select>
                            </div>

                            {{-- Dirección --}}
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label">Dirección</label>
                                <select name="sort_dir" class="form-select">
                                    <option value="asc"  @selected(request('sort_dir','desc')==='asc')>Asc</option>
                                    <option value="desc" @selected(request('sort_dir','desc')==='desc')>Desc</option>
                                </select>
                            </div>

                            {{-- Botón Buscar --}}
                            <div class="col-6 col-md-4 col-lg-2 d-grid">
                                <label class="form-label opacity-0">.</label>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search me-1"></i>
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Exportaciones --}}
                <div class="col-12 col-lg-auto">
                    <div class="btn-list">
                        <a href="#"
                           class="btn btn-success">
                            <i class="ti ti-file-spreadsheet me-1"></i>
                            Excel
                        </a>
                        <a href="#"
                           class="btn btn-danger">
                            <i class="ti ti-file-type-pdf me-1"></i>
                            PDF
                        </a>
                    </div>
                </div>
            </div>

            {{-- Resumen cuando hay búsqueda --}}
            @if(request('search'))
                @php
                    $total = $verificaciones->total();
                    $first = $verificaciones->firstItem();
                    $last  = $verificaciones->lastItem();
                    $current = $verificaciones->currentPage();
                    $lastPage = $verificaciones->lastPage();
                @endphp
                <div class="card mb-3">
                    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-inline-flex align-items-center">
                            <span class="badge bg-azure-lt me-2">
                                <i class="ti ti-filter me-1"></i> Filtro
                            </span>
                            <span class="fw-medium">“{{ request('search') }}”</span>
                        </div>
                        <div class="text-secondary">
                            @if($total === 1)
                                Resultado <span class="fw-semibold">(1 de 1)</span>
                            @elseif($total > 1)
                                Página <span class="fw-semibold">{{ $current }}</span> de <span class="fw-semibold">{{ $lastPage }}</span> —
                                Mostrando <span class="fw-semibold">{{ $first }}–{{ $last }}</span> de <span class="fw-semibold">{{ $total }}</span> resultados
                            @else
                                Sin resultados para la búsqueda.
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tabla principal --}}
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Vehículo</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Comentarios</th>
                                    <th class="w-1 text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($verificaciones as $verificacion)
                                    @php
                                        $unidad = $verificacion->vehiculo->unidad ?? '—';
                                        $placa  = $verificacion->vehiculo->placa ?? null;
                                        $estado = (string)$verificacion->estado;
                                        // Color de badge por estado (ajusta a tus propios valores)
                                        $estadoColor = 'bg-secondary-lt';
                                        if (str_contains(strtolower($estado), 'vigente')) $estadoColor = 'bg-green-lt';
                                        elseif (str_contains(strtolower($estado), 'vencid')) $estadoColor = 'bg-red-lt';
                                        elseif (str_contains(strtolower($estado), 'próxim') || str_contains(strtolower($estado), 'proxim')) $estadoColor = 'bg-yellow-lt';
                                    @endphp
                                    <tr>
                                        <td class="text-reset">
                                            <div class="fw-medium">{{ $unidad }} @if($placa) <span class="text-secondary">({{ $placa }})</span> @endif</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $estadoColor }}">
                                                <i class="ti ti-shield-check me-1"></i> {{ $estado }}
                                            </span>
                                        </td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($verificacion->fecha_verificacion)->format('Y-m-d') }}</td>
                                        <td class="text-wrap">{{ $verificacion->comentarios }}</td>
                                        <td class="text-end">
                                            <div class="btn-list justify-content-end">
                                                <a href="{{ route('verificaciones.edit', $verificacion->id) }}"
                                                   class="btn btn-outline-secondary btn-sm"
                                                   aria-label="Editar verificación #{{ $verificacion->id }}">
                                                    <i class="ti ti-edit me-1"></i>
                                                    Editar
                                                </a>

                                                <form action="{{ route('verificaciones.destroy', $verificacion->id) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Seguro que quieres eliminar la verificación #{{ $verificacion->id }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="ti ti-trash me-1"></i>
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-5">
                                            <div class="empty">
                                                <div class="empty-img"><i class="ti ti-folder-off" style="font-size:42px;"></i></div>
                                                <p class="empty-title">No hay verificaciones</p>
                                                <p class="empty-subtitle text-secondary">
                                                    @if(request()->hasAny(['search','vehiculo_id','estado','from','to']))
                                                        No se encontraron resultados con los filtros aplicados.
                                                    @else
                                                        Aún no has registrado verificaciones.
                                                    @endif
                                                </p>
                                                <div class="empty-action">
                                                    @if(request()->hasAny(['search','vehiculo_id','estado','from','to']))
                                                        <a href="{{ route('verificaciones.index') }}" class="btn btn-outline-primary">
                                                            <i class="ti ti-filter-off me-1"></i>
                                                            Limpiar filtros
                                                        </a>
                                                    @else
                                                        <a href="{{ route('verificaciones.create') }}" class="btn btn-primary">
                                                            <i class="ti ti-square-plus me-1"></i>
                                                            Nueva Verificación
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Paginación + contador --}}
                @if(method_exists($verificaciones, 'links'))
                    @php
                        $totalAll   = $verificaciones->total();
                        $firstAll   = $verificaciones->firstItem();
                        $lastAll    = $verificaciones->lastItem();
                        $currentAll = $verificaciones->currentPage();
                        $lastPageAll= $verificaciones->lastPage();
                    @endphp
                    <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
                        <div class="text-secondary">
                            @if($totalAll === 0)
                                Mostrando 0 resultados
                            @elseif($totalAll === 1)
                                Resultado <span class="fw-semibold">(1 de 1)</span>
                            @else
                                Página <span class="fw-semibold">{{ $currentAll }}</span> de <span class="fw-semibold">{{ $lastPageAll }}</span> —
                                Mostrando <span class="fw-semibold">{{ $firstAll }}–{{ $lastAll }}</span> de <span class="fw-semibold">{{ $totalAll }}</span> resultados
                            @endif
                        </div>
                        <div>
                            {{ $verificaciones->appends([
                                'search'     => request('search'),
                                'vehiculo_id'=> request('vehiculo_id'),
                                'estado'     => request('estado'),
                                'from'       => request('from'),
                                'to'         => request('to'),
                                'sort_by'    => request('sort_by'),
                                'sort_dir'   => request('sort_dir'),
                            ])->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Footer estilo Tabler --}}
    <footer class="footer footer-transparent d-print-none">
        <div class="container-xl">
            <div class="row text-center align-items-center flex-row-reverse">
                <div class="col-12">
                    <p class="mb-0 text-secondary small">
                        © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
                    </p>
                </div>
            </div>
        </div>
    </footer>
</x-app-layout>
