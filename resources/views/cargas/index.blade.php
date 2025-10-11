{{-- resources/views/cargas_combustible/index.blade.php --}}
<x-app-layout>
    @vite(['resources/js/app.js'])

    @php
        /* ================= Utilidades y constantes ================= */
        $ignored      = ['search','page','sort_by','sort_dir'];
        $activeFilters = collect(request()->query())
            ->reject(fn($v,$k)=>in_array($k,$ignored,true))
            ->filter(fn($v)=>is_array($v) ? collect($v)->filter(fn($x)=>$x!==null && $x!=='')->isNotEmpty()
                                          : $v!==null && $v!=='');

        $activeCount  = $activeFilters->count();
        $exportHref   = route('cargas.index', array_merge(request()->except('page'), ['export'=>'xlsx']));

        // Campos que debemos preservar en la paginación y al limpiar/reenviar
        $qsKeep = [
            'search','vehiculo_id','operador_id','tipo_combustible',
            'from','to',
            'litros_min','litros_max','precio_min','precio_max','total_min','total_max',
            'rend_min','rend_max','km_ini_min','km_ini_max','km_fin_min','km_fin_max',
            'destino','custodio','sort_by','sort_dir','estado',
        ];

        // Opciones para selects
        $sortOptions = [
            'fecha' => 'Fecha','vehiculo'=>'Vehículo','placa'=>'Placa','operador'=>'Operador',
            'tipo_combustible'=>'Tipo','litros'=>'Litros','precio'=>'Precio','total'=>'Total',
            'rendimiento'=>'Rendimiento','km_inicial'=>'KM Inicial','km_final'=>'KM Final',
            'recorrido'=>'KM Recorridos','id'=>'ID',
        ];

        // Rangos numéricos (name => [placeholder, step])
        $rangeFields = [
            'litros_min' => ['Litros mín', '0.001'], 'litros_max' => ['Litros máx', '0.001'],
            'precio_min' => ['Precio mín', '0.01'],  'precio_max' => ['Precio máx', '0.01'],
            'total_min'  => ['Total mín',  '0.01'],  'total_max'  => ['Total máx',  '0.01'],
            'rend_min'   => ['Rend mín',   '0.01'],  'rend_max'   => ['Rend máx',   '0.01'],
            'km_ini_min' => ['KM inicial mín','1'],  'km_ini_max' => ['KM inicial máx','1'],
            'km_fin_min' => ['KM final mín','1'],    'km_fin_max' => ['KM final máx','1'],
        ];

        // Helpers locales
        $rq = fn($key,$default=null)=>request($key,$default);
        $nf = fn($n,$d=2)=>number_format((float)($n ?? 0),$d);
        $fullName = function($o){
            if(!$o) return '—';
            return trim(collect([$o->nombre??'',$o->apellido_paterno??'',$o->apellido_materno??''])
                ->filter(fn($x)=>$x!=='')->implode(' ')) ?: 'Operador';
        };

        // Stats de paginación/búsqueda
        $total    = $cargas->total();
        $first    = $cargas->firstItem();
        $last     = $cargas->lastItem();
        $current  = $cargas->currentPage();
        $lastPage = $cargas->lastPage();
    @endphp

    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a>Inicio</a></li>
                        <li class="breadcrumb-item"><a>Panel</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Cargas de combustible</li>
                    </ol>
                    <div class="col">
                        <h2 class="page-title mb-0">Cargas de Combustible</h2>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('cargas.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1" aria-hidden="true"></i><span>Agregar Nueva Carga</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="ti ti-check me-2" aria-hidden="true"></i>{{ session('success') }}
                </div>
            @endif

            {{-- ============ FORM GET: búsqueda + filtros + orden ============ --}}
            <form method="GET" action="{{ route('cargas.index') }}" aria-label="Búsqueda y filtros de cargas">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-center">
                            {{-- Búsqueda --}}
                            <div class="col-12 col-xl">
                                <div class="input-group" role="search" aria-label="Buscar en cargas">
                                    <span class="input-group-text" id="icon-search"><i class="ti ti-search" aria-hidden="true"></i></span>
                                    <input type="text" name="search" value="{{ $rq('search') }}" class="form-control"
                                           placeholder="Buscar…" aria-label="Término de búsqueda" aria-describedby="icon-search">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti ti-search me-1" aria-hidden="true"></i>Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Acciones --}}
                            <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">
                                <a href="{{ $exportHref }}" class="btn btn-outline-success" title="Exportar a Excel">
                                    <i class="ti ti-brand-excel me-1" aria-hidden="true"></i><span>Exportar</span>
                                </a>

                                <button type="button"
                                        class="btn btn-outline-secondary position-relative"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#filtersOffcanvas"
                                        aria-controls="filtersOffcanvas"
                                        aria-label="Abrir filtros">
                                    <i class="ti ti-adjustments" aria-hidden="true"></i>
                                    <span class="ms-2">Filtros</span>
                                    @if($activeCount > 0)
                                        <span class="badge bg-primary ms-2" aria-label="Filtros activos">{{ $activeCount }}</span>
                                    @endif
                                </button>
                            </div>
                        </div>

                        {{-- Resumen de resultados (solo si hay búsqueda) --}}
                        @if($rq('search'))
                            <div class="mt-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between" role="status">
                                <div class="small">
                                    <span class="badge bg-secondary text-uppercase">Filtro</span>
                                    <span class="ms-2">“{{ $rq('search') }}”</span>
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

                {{-- ================ Offcanvas Filtros ================= --}}
                <x-filters-offcanvas id="filtersOffcanvas" title="Filtros" :backdrop="false" :scroll="true">
                    <x-slot name="filters">
                        {{-- Estado --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Estado</div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label" for="estado">Estado</label>
                                    <select id="estado" name="estado" class="form-select">
                                        @foreach([ ''=>'Todos', 'Aprobada'=>'Aprobada', 'Pendiente'=>'Pendiente'] as $val=>$label)
                                            <option value="{{ $val }}" @selected($rq('estado')===$val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Principales --}}
                        <div class="mb-4">
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Principales</div>
                            <div class="row g-2">
                                {{-- Vehículo --}}
                                <div class="col-12">
                                    <label class="form-label" for="vehiculo_id">Vehículo</label>
                                    <select id="vehiculo_id" name="vehiculo_id" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($vehiculos as $v)
                                            <option value="{{ $v->id }}" @selected((string)$v->id === $rq('vehiculo_id'))>{{ $v->unidad }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Operador --}}
                                <div class="col-12">
                                    <label class="form-label" for="operador_id">Operador</label>
                                    <select id="operador_id" name="operador_id" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($operadores as $o)
                                            <option value="{{ $o->id }}" @selected((string)$o->id === $rq('operador_id'))>{{ $fullName($o) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Tipo de combustible --}}
                                <div class="col-12">
                                    <label class="form-label" for="tipo_combustible">Tipo de combustible</label>
                                    <select id="tipo_combustible" name="tipo_combustible" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($tipos as $t)
                                            <option value="{{ $t }}" @selected($t === $rq('tipo_combustible'))>{{ $t }}</option>
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
                                    <label class="form-label" for="from">Desde</label>
                                    <input id="from" type="date" name="from" value="{{ $rq('from') }}" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="to">Hasta</label>
                                    <input id="to" type="date" name="to" value="{{ $rq('to') }}" class="form-control">
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="sort_by">Ordenar por</label>
                                    <select id="sort_by" name="sort_by" class="form-select">
                                        @foreach($sortOptions as $val=>$label)
                                            <option value="{{ $val }}" @selected($rq('sort_by','fecha')===$val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label" for="sort_dir">Dirección</label>
                                    <select id="sort_dir" name="sort_dir" class="form-select">
                                        @foreach(['asc'=>'Ascendente','desc'=>'Descendente'] as $val=>$label)
                                            <option value="{{ $val }}" @selected($rq('sort_dir','desc')===$val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Métricas --}}
                        <div>
                            <div class="text-secondary text-uppercase fw-semibold small mb-2">Métricas</div>
                            <div class="row g-2">
                                @foreach($rangeFields as $name => [$ph, $step])
                                    <div class="col-6 col-lg-4">
                                        <input type="number" step="{{ $step }}" name="{{ $name }}" value="{{ $rq($name) }}" class="form-control" placeholder="{{ $ph }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-slot>

                    <x-slot name="footer">
                        <a href="{{ route('cargas.index') }}" class="btn btn-link">Limpiar filtros</a>
                        <div class="d-flex">
                            <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="offcanvas">Cerrar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1" aria-hidden="true"></i>Aplicar filtros
                            </button>
                        </div>
                    </x-slot>
                </x-filters-offcanvas>
            </form>

            {{-- ================== TABLA ================== --}}
            <div class="card">
                <div id="cargas-table-scroll-top" class="table-scroll-top" role="presentation" aria-hidden="true">
                    <div id="cargas-table-scroll-spacer"></div>
                </div>

                <div class="table-responsive" id="cargas-table-wrap">
                    <table class="table table-vcenter table-striped table-hover" id="cargas-table">
                        <thead>
                            <tr class="text-uppercase text-secondary small">
                                @foreach(['#','Fecha','Vehículo','Operador','Tipo','Litros','Precio','Total','Rendimiento','KM Inicial','KM Final','KM Recorridos','Destino','Custodio','Observaciones','Estado','Acciones'] as $th)
                                    <th class="@class([
                                        'text-center text-nowrap' => $th === '#',
                                        'text-end' => in_array($th,['Litros','Precio','Total','Rendimiento','KM Inicial','KM Final','KM Recorridos','Acciones']),
                                        'text-nowrap' => in_array($th,['#','Fecha','Vehículo','Operador','Tipo','Estado','Acciones'])
                                    ])" @style([
                                        'min-width:12rem;' => $th==='Destino',
                                        'min-width:10rem;' => $th==='Custodio',
                                        'min-width:16rem;' => $th==='Observaciones',
                                    ])>{{ $th }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cargas as $c)
                                @php
                                    $veh        = $c->vehiculo;
                                    $kmRec      = (is_numeric($c->km_final ?? null) && is_numeric($c->km_inicial ?? null))
                                                    ? ((int)$c->km_final - (int)$c->km_inicial) : null;
                                    $obs        = $c->observaciones ?? $c->comentarios ?? null;
                                    $fechaStr   = $c->fecha ? \Illuminate\Support\Carbon::parse($c->fecha)->format('Y-m-d') : '—';
                                    $rowId      = is_numeric($c->id ?? null) ? (int)$c->id : 0;
                                    $estado     = $c->estado ?? 'Pendiente';
                                @endphp
                                <tr>
                                    <td class="text-center text-nowrap">{{ ($cargas->firstItem() ?? 0) + $loop->index }}</td>
                                    <td class="text-nowrap">{{ $fechaStr }}</td>
                                    <td class="text-nowrap">{{ $veh->unidad ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $fullName($c->operador) }}</td>
                                    <td class="text-nowrap">{{ $c->tipo_combustible ?? '—' }}</td>

                                    <td class="text-end text-nowrap">{{ $nf($c->litros,3) }}</td>
                                    <td class="text-end text-nowrap">${{ $nf($c->precio,2) }}</td>
                                    <td class="text-end text-nowrap">${{ $nf($c->total,2) }}</td>
                                    <td class="text-end text-nowrap">{{ !is_null($c->rendimiento) ? $nf($c->rendimiento,2) : '—' }}</td>

                                    <td class="text-end text-nowrap">{{ $c->km_inicial ?? '—' }}</td>
                                    <td class="text-end text-nowrap">{{ $c->km_final ?? '—' }}</td>
                                    <td class="text-end text-nowrap">{{ !is_null($kmRec) ? $kmRec : '—' }}</td>

                                    @foreach(['destino'=>$c->destino,'custodio'=>$c->custodio,'obs'=>$obs] as $val)
                                        <td><div class="text-truncate" title="{{ $val }}">{{ $val ?? '—' }}</div></td>
                                    @endforeach

                                    <td class="text-nowrap">
                                        <span class="badge {{ $estado==='Aprobada' ? 'bg-green-lt' : 'bg-yellow-lt' }}">
                                            {{ $estado==='Aprobada' ? 'Aprobada' : 'Pendiente' }}
                                        </span>
                                    </td>

                                    <td class="text-end text-nowrap" style="min-width:15rem;">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('cargas.edit',$c->id) }}" class="btn btn-outline-secondary btn-sm" title="Editar">
                                                <i class="ti ti-edit me-1" aria-hidden="true"></i>Editar
                                            </a>
                                            @if($rowId>0)
                                                <button type="submit" class="btn btn-danger btn-sm" form="del-{{ $rowId }}"
                                                        onclick="event.stopPropagation(); return confirm('¿Seguro que quieres eliminar?');"
                                                        title="Eliminar">
                                                    <i class="ti ti-trash me-1" aria-hidden="true"></i>Eliminar
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                @php $hasFilters = request()->hasAny($qsKeep); @endphp
                                <tr>
                                    <td colspan="17" class="py-6">
                                        <div class="empty">
                                            <div class="empty-icon"><i class="ti ti-database-off" aria-hidden="true"></i></div>
                                            <p class="empty-title">No hay datos</p>
                                            <p class="empty-subtitle text-secondary">
                                                {{ $hasFilters ? 'No se encontraron resultados con los filtros aplicados.' : 'Aún no has registrado cargas de combustible.' }}
                                            </p>
                                            <div class="empty-action">
                                                @if($hasFilters)
                                                    <a href="{{ route('cargas.index') }}" class="btn btn-outline-secondary">Limpiar filtros</a>
                                                @endif
                                                <a href="{{ route('cargas.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-2" aria-hidden="true"></i>Agregar Nueva Carga
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

            {{-- =============== PAGINACIÓN + CONTADOR =============== --}}
            @if(method_exists($cargas,'links'))
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between mt-3">
                    <p class="text-secondary small mb-2 mb-sm-0">
                        @if($total === 0)
                            Mostrando 0 resultados
                        @elseif($total === 1)
                            Resultado <strong>(1 de 1)</strong>
                        @else
                            Página <strong>{{ $current }}</strong> de <strong>{{ $lastPage }}</strong> —
                            Mostrando <strong>{{ $first }}–{{ $last }}</strong> de <strong>{{ $total }}</strong> resultados
                        @endif
                    </p>
                    <div>{{ $cargas->appends(request()->only($qsKeep))->links() }}</div>
                </div>
            @endif

            {{-- ====== Formularios DELETE ocultos ====== --}}
            @foreach($cargas as $cc)
                @php $rid = is_numeric($cc->id ?? null) ? (int)$cc->id : 0; @endphp
                @if($rid>0)
                    <form id="del-{{ $rid }}" action="{{ url('/cargas/'.$rid) }}" method="POST" class="d-none">
                        @csrf @method('DELETE')
                    </form>
                @endif
            @endforeach

            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    @once
        <style>
            .offcanvas-backdrop,.modal-backdrop{display:none!important;opacity:0!important}
            .dropdown-menu{z-index:1080}
            .table-scroll-top{overflow-x:auto;overflow-y:hidden;height:14px;margin:.25rem 0;background:transparent}
            .table-scroll-top>#cargas-table-scroll-spacer{height:1px}
            .table-scroll-top::-webkit-scrollbar{height:12px}
            .table-scroll-top::-webkit-scrollbar-thumb{border-radius:8px}
            .theme-dark .table-scroll-top,[data-bs-theme="dark"] .table-scroll-top{background:transparent}
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.bootstrap?.Dropdown) {
                    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el=>new window.bootstrap.Dropdown(el));
                }
                const topScroll=document.getElementById('cargas-table-scroll-top');
                const spacer=document.getElementById('cargas-table-scroll-spacer');
                const tableWrap=document.getElementById('cargas-table-wrap');
                if(!(topScroll&&spacer&&tableWrap)) return;

                const updateWidths=()=>{
                    const sw=tableWrap.scrollWidth; spacer.style.width=sw+'px';
                    topScroll.style.display= sw > (tableWrap.clientWidth+2) ? 'block':'none';
                };
                let syncingTop=false, syncingBottom=false;
                topScroll.addEventListener('scroll', ()=>{
                    if(syncingTop){syncingTop=false;return;}
                    syncingBottom=true; tableWrap.scrollLeft=topScroll.scrollLeft;
                },{passive:true});
                tableWrap.addEventListener('scroll', ()=>{
                    if(syncingBottom){syncingBottom=false;return;}
                    syncingTop=true; topScroll.scrollLeft=tableWrap.scrollLeft;
                },{passive:true});

                updateWidths(); window.addEventListener('resize', updateWidths);
                setTimeout(updateWidths,150); setTimeout(updateWidths,500);
            });
        </script>
    @endonce
</x-app-layout>
