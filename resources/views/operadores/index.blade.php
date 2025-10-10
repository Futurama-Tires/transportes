{{-- resources/views/operadores/index.blade.php — versión Tabler (acciones separadas, filtros en offcanvas y numeración continua) --}}
<x-app-layout>
    {{-- Si ya incluyes @vite en tu layout, puedes quitar esta línea --}}
    @vite(['resources/js/app.js'])

    @php
        // Parámetros y utilidades reutilizables en toda la vista
        $q        = request();
        $ignored  = ['search','page','sort_by','sort_dir'];
        $activeCount = collect($q->except($ignored))->filter(
            fn($v) => is_array($v)
                ? collect($v)->filter(fn($x)=>$x!==null && $x!=='')->isNotEmpty()
                : $v !== null && $v !== ''
        )->count();

        $search  = $q->input('search', '');
        $sortBy  = $q->input('sort_by', 'nombre_completo');
        $sortDir = $q->input('sort_dir', 'asc');

        /** @var \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator $p */
        $p = $operadores;

        // Opciones de ordenamiento (único lugar)
        $sortOptions = [
            'nombre_completo' => 'Nombre completo',
            'email'           => 'Correo electrónico',
            'id'              => 'ID',
        ];

        // Datos de paginación (seguros cuando hay resultados)
        $total     = method_exists($p, 'total') ? $p->total() : ($p->count() ?? 0);
        $firstItem = method_exists($p, 'firstItem') ? $p->firstItem() : null;
        $lastItem  = method_exists($p, 'lastItem')  ? $p->lastItem()  : null;
        $current   = method_exists($p, 'currentPage') ? $p->currentPage() : 1;
        $lastPage  = method_exists($p, 'lastPage')    ? $p->lastPage()    : 1;

        // Parámetros a mantener en los links de paginación/acciones
        $keepParams = ['search','sort_by','sort_dir'];

        /* ============================
         | Columnas dinámicas de la tabla "operadores"
         | Mostrar TODO excepto: id, user_id, created_at, updated_at, deleted_at
         | y ordenarlas de forma lógica
         *============================ */
        $excluded = ['id','user_id','created_at','updated_at','deleted_at'];

        $firstModel = $p->first();
        if ($firstModel) {
            // Unimos fillable + atributos reales por si hay columnas no fillable
            $columnsAll = array_unique(array_merge(
                $firstModel->getFillable(),
                array_keys($firstModel->getAttributes() ?? [])
            ));
        } else {
            // Sin datos: usamos fillable del modelo para renderizar encabezados
            $columnsAll = (new \App\Models\Operador)->getFillable();
        }
        $columnsAll = array_values(array_filter($columnsAll, fn($c) => !in_array($c, $excluded, true)));

        // Orden lógico propuesto:
        // 1) Identidad, 2) Contacto básico, 3) Datos personales,
        // 4) Identificadores oficiales, 5) Contacto de emergencia
        $preferredOrder = [
            // 1) Identidad
            'nombre','apellido_paterno','apellido_materno',
            // 2) Contacto básico
            'telefono','domicilio',
            // 3) Datos personales
            'estado_civil','tipo_sangre',
            // 4) Identificadores oficiales
            'curp','rfc',
            // 5) Contacto de emergencia
            'contacto_emergencia_nombre','contacto_emergencia_parentesco','contacto_emergencia_tel','contacto_emergencia_ubicacion',
        ];

        // Creamos la lista final respetando el orden preferido y añadiendo cualquier resto (alfabético)
        $columnsOrdered = [];
        foreach ($preferredOrder as $c) {
            if (in_array($c, $columnsAll, true)) $columnsOrdered[] = $c;
        }
        $remaining = array_values(array_diff($columnsAll, $columnsOrdered));
        sort($remaining); // por si existen columnas nuevas no contempladas
        $columns = array_merge($columnsOrdered, $remaining);

        // Etiquetas amigables por columna (opcionales)
        $labelMap = [
            'nombre'                         => 'Nombre',
            'apellido_paterno'               => 'Apellido paterno',
            'apellido_materno'               => 'Apellido materno',
            'telefono'                       => 'Teléfono',
            'domicilio'                      => 'Domicilio',
            'contacto_emergencia_nombre'     => 'Contacto de emergencia',
            'contacto_emergencia_tel'        => 'Tel. emergencia',
            'tipo_sangre'                    => 'Tipo de sangre',
            'estado_civil'                   => 'Estado civil',
            'curp'                           => 'CURP',
            'rfc'                            => 'RFC',
            'contacto_emergencia_parentesco' => 'Parentesco (emergencia)',
            'contacto_emergencia_ubicacion'  => 'Ubicación (emergencia)',
        ];

        $labelFor = function(string $col) use ($labelMap) {
            if (isset($labelMap[$col])) return $labelMap[$col];
            return \Illuminate\Support\Str::of($col)->replace('_',' ')->ucfirst();
        };
    @endphp

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a>Inicio</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a>Panel</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Gestión de operadores</li>
                            </ol>
                    <div class="col">
                        <h2 class="page-title mb-0">Gestión de Operadores</h2>
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
                <div class="alert alert-success alert-dismissible" role="alert">
                    <i class="ti ti-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            {{-- ===== FORM DE BÚSQUEDA/FILTROS (GET) =====
                 Importante: este formulario NO envuelve la tabla para evitar formularios anidados. --}}
            <form method="GET" action="{{ route('operadores.index') }}" autocomplete="off" novalidate>
                {{-- TOOLBAR: búsqueda + acciones rápidas --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-center">
                            {{-- Búsqueda global --}}
                            <div class="col-12 col-xl">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search" aria-hidden="true"></i></span>
                                    <input
                                        type="text"
                                        name="search"
                                        value="{{ $search }}"
                                        class="form-control"
                                        placeholder="Buscar…"
                                        aria-label="Búsqueda global"
                                    >
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti ti-search me-1" aria-hidden="true"></i>Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Acciones --}}
                            <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">
                                {{-- Botón único: Exportar Excel --}}
                                <a href="{{ route('operadores.index', array_merge(request()->only($keepParams), ['export' => 'xlsx'])) }}"
                                class="btn btn-outline-success"
                                title="Exportar a Excel">
                                    <i class="ti ti-brand-excel me-1" aria-hidden="true"></i>
                                    <span>Exportar</span>
                                </a>


                                {{-- Botón Filtros (abre Offcanvas) --}}
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary position-relative"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#filtersOffcanvas"
                                    aria-controls="filtersOffcanvas"
                                >
                                    <i class="ti ti-adjustments" aria-hidden="true"></i>
                                    <span class="ms-2">Filtros</span>
                                    @if($activeCount>0)
                                        <span class="badge bg-primary ms-2" aria-label="{{ $activeCount }} filtros activos">{{ $activeCount }}</span>
                                    @endif
                                </button>
                            </div>
                        </div>

                        {{-- Resumen cuando hay búsqueda --}}
                        @if($search !== '')
                            <div class="mt-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between">
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
                                        Página <strong>{{ $current }}</strong> de <strong>{{ $lastPage }}</strong> — Mostrando <strong>{{ $firstItem }}–{{ $lastItem }}</strong> de <strong>{{ $total }}</strong>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- OFFCANVAS DE FILTROS (incluye ordenación) --}}
                <div
                    class="offcanvas offcanvas-end"
                    tabindex="-1"
                    id="filtersOffcanvas"
                    aria-labelledby="filtersOffcanvasLabel"
                    data-bs-backdrop="false"   {{-- ← sin oscurecimiento --}}
                    data-bs-scroll="true"      {{-- ← permite scroll del contenido de fondo --}}
                >
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

                        {{-- Espacio para futuros filtros --}}
                        <div class="mb-2">
                            <div class="text-secondary small">Puedes añadir más filtros aquí cuando existan en el modelo.</div>
                        </div>
                    </div>

                    <div class="offcanvas-footer d-flex justify-content-between align-items-center p-3 border-top">
                        <a href="{{ route('operadores.index') }}" class="btn btn-link">Limpiar filtros</a>
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
            {{-- ===== /FORM DE BÚSQUEDA/FILTROS (GET) ===== --}}

            {{-- ===== TABLA (fuera del <form GET>) ===== --}}
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped table-hover">
                        <thead>
                            <tr class="text-uppercase text-secondary small">
                                <th class="text-center text-nowrap">#</th>
                                @foreach($columns as $col)
                                    <th class="text-nowrap">{{ $labelFor($col) }}</th>
                                @endforeach
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($p as $op)
                                <tr>
                                    {{-- Numeración continua por página --}}
                                    <td class="text-center text-nowrap">
                                        {{ ($firstItem ?? 0) + $loop->index }}
                                    </td>

                                    {{-- Celdas dinámicas por cada columna de la tabla (orden lógico) --}}
                                    @foreach($columns as $col)
                                        @php
                                            $val = data_get($op, $col);
                                            $display = $val;

                                            // Formateos ligeros por tipo de campo
                                            if ($col === 'estado_civil' && is_string($display)) {
                                                $display = ucfirst(strtolower($display));
                                            }
                                            if (in_array($col, ['curp','rfc'], true) && is_string($display)) {
                                                $display = strtoupper($display);
                                            }
                                        @endphp

                                        @if($col === 'tipo_sangre')
                                            <td class="text-nowrap">
                                                <span class="badge bg-red-lt">
                                                    <i class="ti ti-droplet me-1"></i>{{ $display ?: '—' }}
                                                </span>
                                            </td>
                                        @elseif($col === 'telefono' || $col === 'contacto_emergencia_tel')
                                            <td class="text-nowrap" title="{{ $display }}">
                                                {{ ($display !== null && $display !== '') ? $display : '—' }}
                                            </td>
                                        @else
                                            <td>
                                                <div class="text-truncate" style="max-width: 260px" title="{{ $display }}">
                                                    {{ ($display !== null && $display !== '') ? $display : '—' }}
                                                </div>
                                            </td>
                                        @endif
                                    @endforeach

                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            {{-- Editar --}}
                                            <a href="{{ route('operadores.edit', $op) }}"
                                               class="btn btn-outline-secondary btn-sm"
                                               title="Editar">
                                                <i class="ti ti-edit me-1" aria-hidden="true"></i>Editar
                                            </a>

                                            {{-- Eliminar (formulario POST DELETE FUERA de cualquier GET) --}}
                                            <form
                                                action="{{ route('operadores.destroy', $op) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('¿Seguro que quieres eliminar a {{ $op->nombre_completo ?: 'este operador' }}?');"
                                            >
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
                                    <td colspan="{{ 2 + count($columns) }}" class="py-6">
                                        <div class="empty">
                                            <div class="empty-icon">
                                                <i class="ti ti-database-off" aria-hidden="true"></i>
                                            </div>
                                            <p class="empty-title">No hay datos</p>
                                            <p class="empty-subtitle text-secondary">
                                                @if($search !== '')
                                                    No se encontraron resultados con los filtros aplicados.
                                                @else
                                                    Aún no has registrado operadores.
                                                @endif
                                            </p>
                                            <div class="empty-action">
                                                @if($search !== '')
                                                    <a href="{{ route('operadores.index') }}" class="btn btn-outline-secondary">
                                                        Limpiar filtros
                                                    </a>
                                                @endif
                                                <a href="{{ route('operadores.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-2" aria-hidden="true"></i>Agregar Nuevo Operador
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
            {{-- ===== /TABLA ===== --}}

            {{-- PAGINACIÓN + CONTADOR --}}
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

            {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>
</x-app-layout>
