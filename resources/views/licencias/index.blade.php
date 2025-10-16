{{-- resources/views/licencias/index.blade.php --}}
<x-app-layout>
    @php
        $q       = request();
        $search  = $q->input('search', '');
        $ambito  = $q->input('ambito', '');
        $estatus = $q->input('estatus', ''); // vigente | por_vencer | vencida
    @endphp

    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <p class="text-secondary text-uppercase small mb-1">Licencias</p>
                        <h2 class="page-title mb-0">Licencias de conducir</h2>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('licencias.create') }}" class="btn btn-danger">
                            <span class="material-symbols-outlined me-1 align-middle">add</span>Nueva licencia
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <span class="material-symbols-outlined me-2 align-middle">check_circle</span>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <form method="GET" action="{{ route('licencias.index') }}" autocomplete="off" novalidate>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-xl">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="material-symbols-outlined">search</span>
                                    </span>
                                    <input
                                        type="text"
                                        name="search"
                                        value="{{ $search }}"
                                        class="form-control"
                                        placeholder="Buscar por folio, tipo, emisor, estado de emisión…">
                                    <button class="btn btn-danger" type="submit">
                                        <span class="material-symbols-outlined me-1 align-middle">search</span>Buscar
                                    </button>
                                </div>
                            </div>
                            <div class="col-12 col-xl-auto d-flex gap-2 justify-content-end">
                                <button class="btn btn-outline-dark" type="button"
                                        data-bs-toggle="offcanvas" data-bs-target="#filters" aria-controls="filters">
                                    <span class="material-symbols-outlined me-1 align-middle">tune</span>Filtros
                                </button>
                                <a href="{{ route('licencias.index') }}" class="btn btn-outline-dark">
                                    Limpiar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Offcanvas filtros --}}
                <div class="offcanvas offcanvas-end" tabindex="-1" id="filters" aria-labelledby="filtersLabel"
                     data-bs-backdrop="false" data-bs-scroll="true">
                    <div class="offcanvas-header">
                        <h2 class="offcanvas-title h4" id="filtersLabel">Filtros</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                    </div>
                    <div class="offcanvas-body">
                        <div class="mb-3">
                            <label class="form-label">Ámbito</label>
                            <select name="ambito" class="form-select">
                                <option value="">(todos)</option>
                                <option value="federal" {{ $ambito==='federal'?'selected':'' }}>Federal</option>
                                <option value="estatal" {{ $ambito==='statal'?'selected':'' }}>Estatal</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estatus</label>
                            <select name="estatus" class="form-select">
                                <option value="">(todos)</option>
                                <option value="vigente" {{ $estatus==='vigente'?'selected':'' }}>Vigente</option>
                                <option value="por_vencer" {{ $estatus==='por_vencer'?'selected':'' }}>Por vencer (≤30 días)</option>
                                <option value="vencida" {{ $estatus==='vencida'?'selected':'' }}>Vencida</option>
                            </select>
                        </div>
                        {{-- Se elimina filtro por Operador ID --}}
                    </div>
                    <div class="offcanvas-footer p-3 border-top">
                        <button type="submit" class="btn btn-danger w-100">
                            <span class="material-symbols-outlined me-1 align-middle">filter_alt</span>Aplicar filtros
                        </button>
                    </div>
                </div>
            </form>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped table-hover">
                        <thead>
                            <tr class="text-uppercase text-secondary small">
                                <th style="width: 1%">#</th>
                                <th>Operador</th>
                                <th>Ámbito</th>
                                <th>Tipo</th>
                                <th>Folio</th>
                                <th>Expedición</th>
                                <th>Vencimiento</th>
                                <th>Estatus</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $iBase = $licencias->firstItem() ?? 0; @endphp
                            @forelse($licencias as $l)
                                @php
                                    $op = $l->operador;
                                    $nombre = $op?->nombre_completo
                                        ?? trim(($op?->apellido_paterno ?? '').' '.($op?->apellido_materno ?? '').' '.($op?->nombre ?? ''));
                                @endphp
                                <tr>
                                    <td>{{ $iBase + $loop->index }}</td>
                                    <td class="text-nowrap">
                                        @if($op)
                                            <span>{{ $nombre ?: 'Operador' }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-capitalize">{{ $l->ambito ?: '—' }}</td>
                                    <td>{{ $l->tipo ?: '—' }}</td>
                                    <td>{{ $l->folio ?: '—' }}</td>
                                    <td>{{ optional($l->fecha_expedicion)->format('Y-m-d') ?: '—' }}</td>
                                    <td>{{ optional($l->fecha_vencimiento)->format('Y-m-d') ?: '—' }}</td>
                                    <td>
                                        @php $status = $l->estatus; @endphp
                                        @if($status==='vigente')
                                            <span class="badge bg-success">Vigente</span>
                                        @elseif($status==='por_vencer')
                                            <span class="badge bg-warning text-dark">Por vencer</span>
                                        @elseif($status==='vencida')
                                            <span class="badge bg-danger">Vencida</span>
                                        @else
                                            <span class="badge bg-secondary">N/D</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('licencias.edit', $l) }}" class="btn btn-outline-dark btn-sm">
                                            <span class="material-symbols-outlined me-1 align-middle">edit</span>Editar
                                        </a>
                                        <form action="{{ route('licencias.destroy', $l) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar esta licencia? Esta acción borra sus archivos.');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm">
                                                <span class="material-symbols-outlined me-1 align-middle">delete</span>Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-5 text-secondary">Sin licencias registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                {{ $licencias->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
