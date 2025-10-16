{{-- resources/views/tarjetas/index.blade.php — versión Tabler (toolbar, offcanvas filtros, acciones separadas) --}}
<x-app-layout>
    {{-- Si ya incluyes @vite en tu layout, puedes quitar esta línea --}}
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
                    <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a>Inicio</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a>Panel</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Tarjetas Si Vale</li>
                            </ol>
                    <div class="col">
                        <h2 class="page-title mb-0">Tarjetas Si Vale</h2>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('tarjetas.create') }}" class="btn btn-danger">
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

            {{-- FLASH ÉXITO --}}
            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="ti ti-check me-2"></i>{{ session('success') }}
                </div>
            @endif

            {{-- FORM GLOBAL (GET) para mantener filtros/orden/búsqueda --}}
            <form method="GET" action="{{ route('tarjetas.index') }}">

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
                                    <button class="btn btn-danger" type="submit">
                                        <i class="ti ti-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Filtros (offcanvas) --}}
                            <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">
                                {{-- Botón Filtros (abre Offcanvas) --}}
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

                {{-- OFFCANVAS DE FILTROS (sin oscurecimiento) --}}
                <div class="offcanvas offcanvas-end"
                     tabindex="-1"
                     id="filtersOffcanvas"
                     aria-labelledby="filtersOffcanvasLabel"
                     data-bs-backdrop="false">
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
                                        <option value="vigente"   @selected(request('estado')==='vigente')>Vigente</option>
                                        <option value="por_vencer"@selected(request('estado')==='por_vencer')>Por vencer (≤30 días)</option>
                                        <option value="vencida"   @selected(request('estado')==='vencida')>Vencida</option>
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
                        <a href="{{ route('tarjetas.index') }}" class="btn btn-link">Limpiar filtros</a>
                        <div>
                            <button type="button" class="btn btn-outline-dark me-2" data-bs-dismiss="offcanvas">Cerrar</button>
                            <button type="submit" class="btn btn-danger">
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
                                <th style="width:1%; white-space:nowrap;">#</th>
                                <th>Número de tarjeta</th>
                                <th>NIP</th>
                                <th>Fecha de vencimiento</th>
                                <th>Estado</th>
                                <th>Descripción</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Inicio de numeración continuo entre páginas:
                                $startIndex = method_exists($tarjetas, 'firstItem') && !is_null($tarjetas->firstItem())
                                    ? $tarjetas->firstItem()
                                    : 1;
                            @endphp
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

                                    // Formato de número de tarjeta:
                                    $rawNumero = trim((string)($tarjeta->numero_tarjeta ?? ''));
                                    $digits    = preg_replace('/\D+/', '', $rawNumero ?? '');
                                    if ($digits !== '' && strlen($digits) === 4) {
                                        // Si sólo vienen 4 dígitos, mostrar 12 puntos a la izquierda
                                        // Agrupados en 4-4-4 para legibilidad: "•••• •••• •••• 1234"
                                        $numeroFormateado = '••••••••••••' . $digits;
                                    } else {
                                        // Mostrar como viene (ya puede incluir guiones/espacios)
                                        $numeroFormateado = $rawNumero !== '' ? $rawNumero : '—';
                                    }
                                @endphp
                                <tr>
                                    {{-- # consecutivo (no se reinicia entre páginas) --}}
                                    <td class="text-secondary">{{ $startIndex + $loop->index }}</td>

                                    {{-- Número (con enmascarado si sólo hay 4 dígitos) --}}
                                    <td class="font-monospace">{{ $numeroFormateado }}</td>

                                    {{-- NIP con toggle por fila --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="nip-field font-monospace" data-real="{{ $hasNip ? e($nip) : '' }}">
                                                {{ $hasNip ? '••••' : '—' }}
                                            </span>
                                            @if($hasNip)
                                                <button type="button"
                                                        class="btn btn-outline-dark btn-sm toggle-nip"
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

                                    {{-- Descripción --}}
                                    <td class="text-secondary">
                                        @php $desc = trim((string)($tarjeta->descripcion ?? '')); @endphp
                                        @if($desc === '')
                                            —
                                        @else
                                            <div class="text-truncate" style="max-width: 340px" title="{{ $desc }}">
                                                {{ $desc }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Acciones: Ver, Editar, Eliminar (separadas) --}}
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('tarjetas.show', $tarjeta) }}"
                                               class="btn btn-outline-dark btn-sm"
                                               title="Ver">
                                                <i class="ti ti-eye me-1"></i>Ver
                                            </a>

                                            <a href="{{ route('tarjetas.edit', $tarjeta) }}"
                                               class="btn btn-outline-dark btn-sm"
                                               title="Editar">
                                                <i class="ti ti-edit me-1"></i>Editar
                                            </a>

                                            {{-- Este formulario YA no está anidado: funcionará el DELETE correctamente --}}
                                            <form action="{{ route('tarjetas.destroy', $tarjeta) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('¿Seguro que quieres eliminar la tarjeta {{ $tarjeta->numero_tarjeta }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                    <i class="ti ti-trash me-1"></i>Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6">
                                        <div class="empty">
                                            <div class="empty-icon">
                                                <i class="ti ti-credit-card-off"></i>
                                            </div>
                                            <p class="empty-title">No hay tarjetas</p>
                                            <p class="empty-subtitle text-secondary">
                                                @if(request()->hasAny(['search','estado','tiene_nip','from','to','ultimos4']))
                                                    No se encontraron resultados con los filtros aplicados.
                                                @else
                                                    Aún no has registrado tarjetas.
                                                @endif
                                            </p>
                                            <div class="empty-action">
                                                @if(request()->hasAny(['search','estado','tiene_nip','from','to','ultimos4']))
                                                    <a href="{{ route('tarjetas.index') }}" class="btn btn-outline-dark">
                                                        Limpiar filtros
                                                    </a>
                                                @endif
                                                <a href="{{ route('tarjetas.create') }}" class="btn btn-danger">
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
