{{-- resources/views/comodin_gastos/index.blade.php --}}
<x-app-layout>
    @php
        // Cuenta filtros activos (excluye búsqueda, orden y paginación)
        $ignored = ['search','page','sort_by','sort_dir'];
        $activeFilters = collect(request()->query())->filter(function($v,$k) use ($ignored){
            if (in_array($k,$ignored)) return false;
            if (is_array($v)) return collect($v)->filter(fn($x)=>$x!==null && $x!=='')->isNotEmpty();
            return $v !== null && $v !== '';
        });
        $activeCount = $activeFilters->count();
    @endphp

    {{-- ===== HEADER ===== --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title mb-0">
                            <i class="ti ti-receipt me-2"></i>Gastos Tarjeta Comodín
                        </h2>
                        <div class="text-secondary small mt-1">Filtra por tarjeta para ver sus gastos.</div>
                    </div>
                    <div class="col-auto ms-auto">
                        @if($tarjetaId)
                            <a href="{{ route('tarjetas-comodin.gastos.create', $tarjetaId) }}" class="btn btn-danger">
                                <i class="ti ti-plus me-1"></i>Nuevo gasto
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- FLASH ÉXITO --}}
            @if(session('status'))
                <div class="alert alert-success" role="alert">
                    <i class="ti ti-check me-2"></i>{{ session('status') }}
                </div>
            @endif

            {{-- =========================
                 FORM GLOBAL (GET) SOLO PARA BÚSQUEDA/FILTROS
                 IMPORTANTE: se cierra ANTES de la tabla para NO anidar formularios
               ========================= --}}
            <form method="GET" action="{{ route('comodin-gastos.index') }}">
                {{-- TOOLBAR: búsqueda + filtros --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-center">
                            {{-- Búsqueda por concepto --}}
                            <div class="col-12 col-xl">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    <input type="text"
                                           name="search"
                                           value="{{ request('search') }}"
                                           class="form-control"
                                           placeholder="Buscar por concepto…"
                                           aria-label="Búsqueda por concepto">
                                    <button class="btn btn-danger" type="submit">
                                        <i class="ti ti-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Botón Filtros (abre Offcanvas) --}}
                            <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">
                                <button type="button"
                                        class="btn btn-outline-dark position-relative"
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
                                $total   = $gastos->total();
                                $first   = $gastos->firstItem();
                                $last    = $gastos->lastItem();
                                $current = $gastos->currentPage();
                                $lastPage= $gastos->lastPage();
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

                {{-- OFFCANVAS DE FILTROS --}}
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
                                {{-- Tarjeta --}}
                                <div class="col-12">
                                    <label class="form-label">Tarjeta</label>
                                    <select class="form-select" name="tarjeta">
                                        <option value="">Todas</option>
                                        @foreach($tarjetas as $t)
                                            @php
                                                $isSel = (string)request('tarjeta', $tarjetaId) === (string)$t->id;
                                            @endphp
                                            <option value="{{ $t->id }}" @selected($isSel)>
                                                {{ $t->numero_tarjeta }}
                                                @if(optional($t->fecha_vencimiento)->format('Y-m'))
                                                    — vence {{ $t->fecha_vencimiento->format('Y-m') }}
                                                @endif
                                            </option>
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
                                    <input type="date" name="desde" value="{{ request('desde') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Hasta</label>
                                    <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control">
                                </div>

                                <div class="col-12 col-sm-6">
                                    @php
                                        $opts = [
                                            'fecha'    => 'Fecha',
                                            'monto'    => 'Monto',
                                            'concepto' => 'Concepto',
                                            'id'       => 'ID',
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

                        {{-- Métricas --}}
                        <div>
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Métricas</div>
                            <div class="row g-2">
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="0.01" name="monto_min" value="{{ request('monto_min') }}" class="form-control" placeholder="Monto mín">
                                </div>
                                <div class="col-6 col-lg-4">
                                    <input type="number" step="0.01" name="monto_max" value="{{ request('monto_max') }}" class="form-control" placeholder="Monto máx">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="offcanvas-footer d-flex justify-content-between align-items-center p-3 border-top">
                        <a href="{{ route('comodin-gastos.index') }}" class="btn btn-link">Limpiar filtros</a>
                        <div>
                            <button type="button" class="btn btn-outline-dark me-2" data-bs-dismiss="offcanvas">Cerrar</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="ti ti-filter me-1"></i>Aplicar filtros
                            </button>
                        </div>
                    </div>
                </div>
                {{-- /OFFCANVAS --}}
            </form> {{-- 👈 CERRAMOS AQUÍ EL GET PARA NO ANIDAR FORMULARIOS --}}

            {{-- ===== TABLA ===== --}}
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped table-hover">
                        <thead>
                            <tr class="text-uppercase text-secondary small">
                                <th class="text-center text-nowrap">#</th>
                                <th>Fecha</th>
                                <th>Tarjeta</th>
                                <th>Concepto</th>
                                <th class="text-end">Monto</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gastos as $g)
                                @php
                                    $rowId = is_numeric($g->id ?? null) ? (int)$g->id : 0;
                                    $tar   = $g->tarjeta ?? null;
                                @endphp
                                <tr>
                                    <td class="text-center text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">{{ optional($g->fecha)->format('Y-m-d') ?? '—' }}</td>
                                    <td class="font-monospace text-nowrap">{{ $tar->numero_tarjeta ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $g->concepto ?? '—' }}</td>
                                    <td class="text-end text-nowrap">$ {{ number_format((float)($g->monto ?? 0), 2) }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('gastos.edit', $g) }}"
                                               class="btn btn-outline-dark btn-sm"
                                               title="Editar">
                                                <i class="ti ti-edit me-1"></i>Editar
                                            </a>

                                            @if($rowId > 0)
                                                <button
                                                    type="submit"
                                                    class="btn btn-outline-danger btn-sm"
                                                    form="del-{{ $rowId }}"
                                                    onclick="event.stopPropagation(); return confirm('¿Eliminar gasto?');"
                                                    title="Eliminar">
                                                    <i class="ti ti-trash me-1"></i>Eliminar
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6">
                                        <div class="empty">
                                            <div class="empty-icon">
                                                <i class="ti ti-database-off"></i>
                                            </div>
                                            <p class="empty-title">No hay gastos</p>
                                            <p class="empty-subtitle text-secondary">
                                                @if(request()->hasAny(['search','tarjeta','desde','hasta','monto_min','monto_max']))
                                                    No se encontraron resultados con los filtros aplicados.
                                                @else
                                                    Aún no has registrado gastos.
                                                @endif
                                            </p>
                                            <div class="empty-action">
                                                @if(request()->hasAny(['search','tarjeta','desde','hasta','monto_min','monto_max']))
                                                    <a href="{{ route('comodin-gastos.index') }}" class="btn btn-outline-dark">
                                                        Limpiar filtros
                                                    </a>
                                                @endif
                                                @if($tarjetaId)
                                                    <a href="{{ route('tarjetas-comodin.gastos.create', $tarjetaId) }}" class="btn btn-danger">
                                                        <i class="ti ti-plus me-2"></i>Nuevo gasto
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

            {{-- PAGINACIÓN + CONTADOR --}}
            @if(method_exists($gastos, 'links'))
                @php
                    $totalAll   = $gastos->total();
                    $firstAll   = $gastos->firstItem();
                    $lastAll    = $gastos->lastItem();
                    $currentAll = $gastos->currentPage();
                    $lastPageAll= $gastos->lastPage();
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
                        {{ $gastos->appends(request()->only([
                            'search','tarjeta','desde','hasta','monto_min','monto_max','sort_by','sort_dir',
                        ]))->links() }}
                    </div>
                </div>
            @endif

            {{-- FORMULARIOS OCULTOS DELETE --}}
            @foreach($gastos as $gg)
                @php $rid = is_numeric($gg->id ?? null) ? (int)$gg->id : 0; @endphp
                @if($rid > 0)
                    <form id="del-{{ $rid }}" action="{{ route('gastos.destroy', $gg) }}" method="POST" class="d-none">
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
</x-app-layout>
