{{-- resources/views/capturistas/index.blade.php --}}
<x-app-layout>
    {{-- Encabezado de página (Tabler) --}}
    <div class="page-header d-print-none mb-3">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <i class="ti ti-users-group me-2"></i>
                        Gestión de Capturistas
                    </h2>
                    <div class="text-secondary small">
                        Busca, ordena y administra a tus capturistas.
                    </div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('capturistas.create') }}" class="btn btn-primary">
                            <i class="ti ti-user-plus me-1"></i>
                            Agregar nuevo capturista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cuerpo --}}
    <div class="page-body">
        <div class="container-xl">

            {{-- Flash éxito --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <i class="ti ti-checks me-2"></i>
                    {{ session('success') }}
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            {{-- Barra superior: búsqueda + exportaciones --}}
            <div class="row g-3 align-items-end mb-3">
                {{-- Buscador + Filtros --}}
                <div class="col-12 col-lg">
                    <form method="GET" action="{{ route('capturistas.index') }}">
                        <div class="row g-2">
                            {{-- Buscador --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label">Buscar</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-search"></i>
                                    </span>
                                    <input
                                        type="text"
                                        name="search"
                                        value="{{ request('search') }}"
                                        class="form-control"
                                        placeholder="Nombre, email, ID…"
                                        aria-label="Buscar capturistas"
                                    >
                                </div>
                            </div>

                            {{-- Ordenar por --}}
                            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                                <label class="form-label">Ordenar por</label>
                                <select
                                    name="sort_by"
                                    class="form-select"
                                    onchange="this.form.submit()"
                                    title="Ordenar por"
                                >
                                    <option value="nombre_completo" @selected(request('sort_by','nombre_completo')==='nombre_completo')>Nombre completo</option>
                                    <option value="email" @selected(request('sort_by')==='email')>Correo electrónico</option>
                                </select>
                            </div>

                            {{-- Dirección --}}
                            <div class="col-6 col-sm-4 col-md-2 col-lg-2">
                                <label class="form-label">Dirección</label>
                                <select
                                    name="sort_dir"
                                    class="form-select"
                                    onchange="this.form.submit()"
                                    title="Dirección"
                                >
                                    <option value="asc"  @selected(request('sort_dir','asc')==='asc')>Ascendente</option>
                                    <option value="desc" @selected(request('sort_dir')==='desc')>Descendente</option>
                                </select>
                            </div>

                            {{-- Botón Buscar --}}
                            <div class="col-6 col-sm-4 col-md-1 col-lg-1 d-grid">
                                <label class="form-label opacity-0">.</label>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search me-1"></i>
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Exportaciones (opcional) --}}
                <div class="col-12 col-lg-auto">
                    <div class="btn-list">
                        <a href="#" class="btn btn-success" title="Exportar a Excel">
                            <i class="ti ti-file-spreadsheet me-1"></i>
                            Excel
                        </a>
                        <a href="#" class="btn btn-danger" title="Exportar a PDF">
                            <i class="ti ti-file-type-pdf me-1"></i>
                            PDF
                        </a>
                    </div>
                </div>
            </div>

            {{-- Resumen (cuando hay búsqueda) --}}
            @if(request('search'))
                @php
                    $total = $capturistas->total();
                    $first = $capturistas->firstItem();
                    $last  = $capturistas->lastItem();
                    $current = $capturistas->currentPage();
                    $lastPage = $capturistas->lastPage();
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

            {{-- Tabla --}}
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Nombre completo</th>
                                    <th>Correo electrónico</th>
                                    <th class="w-1">ID</th>
                                    <th class="w-1 text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($capturistas as $capturista)
                                    <tr>
                                        <td class="text-reset">
                                            <div class="fw-medium">
                                                {{ $capturista->nombre }} {{ $capturista->apellido_paterno }} {{ $capturista->apellido_materno }}
                                            </div>
                                        </td>
                                        <td>{{ $capturista->user->email }}</td>
                                        <td>{{ $capturista->id }}</td>
                                        <td class="text-end">
                                            <div class="btn-list justify-content-end">
                                                <a href="{{ route('capturistas.edit', $capturista->id) }}"
                                                   class="btn btn-outline-secondary btn-sm"
                                                   aria-label="Editar {{ $capturista->nombre }}">
                                                    <i class="ti ti-edit me-1"></i>
                                                    Editar
                                                </a>

                                                <form action="{{ route('capturistas.destroy', $capturista->id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Seguro que quieres eliminar a {{ $capturista->nombre }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" aria-label="Eliminar {{ $capturista->nombre }}">
                                                        <i class="ti ti-trash me-1"></i>
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-5">
                                            <div class="empty">
                                                <div class="empty-img"><i class="ti ti-users-off" style="font-size:42px;"></i></div>
                                                <p class="empty-title">No hay capturistas</p>
                                                <p class="empty-subtitle text-secondary">
                                                    @if(request('search'))
                                                        No se encontraron resultados para <span class="fw-semibold">“{{ request('search') }}”</span>.
                                                    @else
                                                        Aún no has registrado capturistas.
                                                    @endif
                                                </p>
                                                <div class="empty-action">
                                                    @if(request('search'))
                                                        <a href="{{ route('capturistas.index') }}" class="btn btn-outline-primary">
                                                            <i class="ti ti-filter-off me-1"></i>
                                                            Limpiar búsqueda
                                                        </a>
                                                    @else
                                                        <a href="{{ route('capturistas.create') }}" class="btn btn-primary">
                                                            <i class="ti ti-user-plus me-1"></i>
                                                            Agregar nuevo
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
                @if(method_exists($capturistas, 'links'))
                    @php
                        $totalAll   = $capturistas->total();
                        $firstAll   = $capturistas->firstItem();
                        $lastAll    = $capturistas->lastItem();
                        $currentAll = $capturistas->currentPage();
                        $lastPageAll= $capturistas->lastPage();
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
                            {{ $capturistas->appends([
                                'search'   => request('search'),
                                'sort_by'  => request('sort_by'),
                                'sort_dir' => request('sort_dir'),
                            ])->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Footer (Tabler) --}}
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
