{{-- resources/views/tarjetas_comodin/index.blade.php — versión Tabler (toolbar, offcanvas filtros, acciones separadas) --}}
<x-app-layout>
    {{-- Si ya incluyes @vite en tu layout, puedes quitar esta línea --}}
    @vite(['resources/js/app.js'])

    @php
        // Contar filtros activos (excluye búsqueda, orden y paginación)
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
                        <h2 class="page-title mb-0 d-flex align-items-center gap-2">
                            <i class="ti ti-credit-card"></i>
                            Tarjetas Comodín
                        </h2>
                        <div class="text-secondary small mt-1">
                            Administra las tarjetas para gastos del departamento.
                        </div>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('tarjetas-comodin.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>
                            Nueva tarjeta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- FLASH ÉXITO / STATUS --}}
            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="ti ti-check me-2"></i>{{ session('success') }}
                </div>
            @elseif(session('status'))
                <div class="alert alert-success" role="alert">
                    <i class="ti ti-check me-2"></i>{{ session('status') }}
                </div>
            @endif

            {{-- FORM GLOBAL (GET) para mantener filtros/orden/búsqueda --}}
            <form method="GET" action="{{ route('tarjetas-comodin.index') }}">

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
                                           placeholder="Buscar por número de tarjeta, NIP o fecha de vencimiento…"
                                           aria-label="Búsqueda global">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti ti-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Acciones: Exportar + Filtros (offcanvas) --}}
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
                                $total   = $tarjetas->total();
                                $first   = $tarjetas->firstItem();
                                $last    = $tarjetas->lastItem();
                                $current = $tarjetas->currentPage();
                                $lastPage= $tarjetas->lastPage();
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
                                {{-- Estado (derivado por vencimiento) --}}
                                <div class="col-12">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="vigente"    @selected(request('estado')==='vigente')>Vigente</option>
                                        <option value="por_vencer" @selected(request('estado')==='por_vencer')>Por vencer (≤30 días)</option>
                                        <option value="vencida"    @selected(request('estado')==='vencida')>Vencida</option>
                                    </select>
                                </div>

                                {{-- ¿Tiene NIP? --}}
                                <div class="col-12">
                                    <label class="form-label">NIP</label>
                                    <select name="tiene_nip" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="1" @selected(request('tiene_nip')==='1')>Con NIP</option>
                                        <option value="0" @selected(request('tiene_nip')==='0')>Sin NIP</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Fecha y orden --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Fecha y orden</div>
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Vence desde</label>
                                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Vence hasta</label>
                                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                                </div>

                                <div class="col-12 col-sm-6">
                                    @php
                                        $opts = [
                                            'numero_tarjeta'    => 'Número de tarjeta',
                                            'fecha_vencimiento' => 'Fecha de vencimiento',
                                            'id'                => 'ID',
                                        ];
                                    @endphp
                                    <label class="form-label">Ordenar por</label>
                                    <select name="sort_by" class="form-select">
                                        @foreach($opts as $val => $label)
                                            <option value="{{ $val }}" @selected(request('sort_by','numero_tarjeta')===$val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label">Dirección</label>
                                    <select name="sort_dir" class="form-select">
                                        <option value="asc"  @selected(request('sort_dir','asc')==='asc')>Ascendente</option>
                                        <option value="desc" @selected(request('sort_dir')==='desc')>Descendente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Auxiliares --}}
                        <div>
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Auxiliares</div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <input type="text" name="ultimos4" value="{{ request('ultimos4') }}" class="form-control" placeholder="Últimos 4 dígitos">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="offcanvas-footer d-flex justify-content-between align-items-center p-3 border-top">
                        <a href="{{ route('tarjetas-comodin.index') }}" class="btn btn-link">Limpiar filtros</a>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="offcanvas">Cerrar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1"></i>Aplicar filtros
                            </button>
                        </div>
                    </div>
                </div>
                {{-- /OFFCANVAS --}}
            </form> {{-- ←← CERRAMOS EL FORM GET ANTES DE LA TABLA para evitar "nested forms" --}}

            {{-- ===== TABLA ===== (ya FUERA del form GET) --}}
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped table-hover">
                        <thead>
                            <tr class="text-uppercase text-secondary small">
                                <th>Número de tarjeta</th>
                                <th>NIP</th>
                                <th>Fecha de vencimiento</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tarjetas as $tarjeta)
                                @php
                                    $fv = $tarjeta->fecha_vencimiento ? \Carbon\Carbon::parse($tarjeta->fecha_vencimiento) : null;
                                    $hoy = \Carbon\Carbon::today();
                                    $estado = '—';
                                    $badge  = 'bg-secondary-lt';
                                    if ($fv) {
                                        $daysTo = $hoy->diffInDays($fv, false); // <0 vencida, 0..30 por vencer, >30 vigente
                                        if ($daysTo < 0) {
                                            $estado = 'Vencida';       $badge = 'bg-rose-lt';
                                        } elseif ($daysTo <= 30) {
                                            $estado = 'Por vencer';    $badge = 'bg-amber-lt';
                                        } else {
                                            $estado = 'Vigente';       $badge = 'bg-emerald-lt';
                                        }
                                    }
                                    $nip = $tarjeta->nip ?? '—';
                                    $hasNip = $nip !== '—' && $nip !== '';
                                @endphp
                                <tr>
                                    {{-- Número (visible completo) --}}
                                    <td class="font-monospace">{{ $tarjeta->numero_tarjeta }}</td>

                                    {{-- NIP con toggle por fila --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="nip-field font-monospace" data-real="{{ $hasNip ? e($nip) : '' }}">
                                                {{ $hasNip ? '••••' : '—' }}
                                            </span>
                                            @if($hasNip)
                                                <button type="button"
                                                        class="btn btn-outline-secondary btn-sm toggle-nip"
                                                        aria-label="Mostrar NIP" title="Mostrar NIP">
                                                    <i class="ti ti-eye"></i>
                                                    <span class="ms-1 d-none d-sm-inline">Mostrar</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Vencimiento --}}
                                    <td class="text-nowrap">{{ $fv ? $fv->format('Y-m-d') : '—' }}</td>

                                    {{-- Estado --}}
                                    <td>
                                        <span class="badge {{ $badge }}">
                                            <i class="ti ti-circle-dot me-1"></i>{{ $estado }}
                                        </span>
                                    </td>

                                    {{-- Acciones: Ver Gastos, Editar, Eliminar (separadas) --}}
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            {{-- Ver gastos de la tarjeta comodín --}}
                                            <a href="{{ route('comodin-gastos.index', ['tarjeta' => $tarjeta->id]) }}"
                                               class="btn btn-outline-primary btn-sm"
                                               title="Ver gastos">
                                                <i class="ti ti-eye me-1"></i>Ver gastos
                                            </a>

                                            {{-- Editar --}}
                                            <a href="{{ route('tarjetas-comodin.edit', $tarjeta) }}"
                                               class="btn btn-outline-secondary btn-sm"
                                               title="Editar">
                                                <i class="ti ti-edit me-1"></i>Editar
                                            </a>

                                            {{-- Eliminar (no anidar formularios) --}}
                                            <form action="{{ route('tarjetas-comodin.destroy', $tarjeta) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('¿Seguro que quieres eliminar la tarjeta {{ $tarjeta->numero_tarjeta }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                    <i class="ti ti-trash me-1"></i>Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6">
                                        <div class="empty">
                                            <div class="empty-icon">
                                                <i class="ti ti-credit-card-off"></i>
                                            </div>
                                            <p class="empty-title">No hay tarjetas comodín</p>
                                            <p class="empty-subtitle text-secondary">
                                                @if(request()->hasAny(['search','estado','tiene_nip','from','to','ultimos4']))
                                                    No se encontraron resultados con los filtros aplicados.
                                                @else
                                                    Aún no has registrado tarjetas comodín.
                                                @endif
                                            </p>
                                            <div class="empty-action">
                                                @if(request()->hasAny(['search','estado','tiene_nip','from','to','ultimos4']))
                                                    <a href="{{ route('tarjetas-comodin.index') }}" class="btn btn-outline-secondary">
                                                        Limpiar filtros
                                                    </a>
                                                @endif
                                                <a href="{{ route('tarjetas-comodin.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-2"></i>Nueva tarjeta
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
            @if(method_exists($tarjetas, 'links'))
                @php
                    $totalAll   = $tarjetas->total();
                    $firstAll   = $tarjetas->firstItem();
                    $lastAll    = $tarjetas->lastItem();
                    $currentAll = $tarjetas->currentPage();
                    $lastPageAll= $tarjetas->lastPage();
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
                        {{-- Para Bootstrap/Tabler: en AppServiceProvider -> Paginator::useBootstrapFive(); --}}
                        {{ $tarjetas->appends(request()->only([
                            'search','estado','tiene_nip','from','to','ultimos4','sort_by','sort_dir',
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

    {{-- ===== SCRIPTS ===== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Toggle NIP por fila (•••• <-> real)
            document.body.addEventListener('click', (e) => {
                const btn = e.target.closest('.toggle-nip');
                if (!btn) return;

                const td = btn.closest('td');
                const field = td?.querySelector('.nip-field');
                const real = field?.dataset.real || '';
                if (!field || !real) return;

                const hidden = field.textContent.trim() === '••••';
                if (hidden) {
                    field.textContent = real;
                    btn.innerHTML = '<i class="ti ti-eye-off"></i><span class="ms-1 d-none d-sm-inline">Ocultar</span>';
                    btn.setAttribute('aria-label', 'Ocultar NIP');
                    btn.setAttribute('title', 'Ocultar NIP');
                } else {
                    field.textContent = '••••';
                    btn.innerHTML = '<i class="ti ti-eye"></i><span class="ms-1 d-none d-sm-inline">Mostrar</span>';
                    btn.setAttribute('aria-label', 'Mostrar NIP');
                    btn.setAttribute('title', 'Mostrar NIP');
                }
            });
        });
    </script>
</x-app-layout>
