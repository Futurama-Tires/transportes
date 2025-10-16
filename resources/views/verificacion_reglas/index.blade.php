{{-- resources/views/verificacion_reglas/index.blade.php --}}
<x-app-layout>
    {{-- ================= HEADER (estilo de cargas) ================= --}}
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
                        <li class="breadcrumb-item active" aria-current="page">Reglas de verificación</li>
                    </ol>

                    <div class="col">
                        <h2 class="page-title mb-0">Reglas de verificación</h2>
                    </div>

                    {{-- CTA principal --}}
                    <div class="col-auto ms-auto">
                        <a href="{{ route('verificacion-reglas.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1" aria-hidden="true"></i>
                            <span>Nueva regla</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- ===== Flash messages ===== --}}
            @if (session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                {{-- ===== Tabla ===== --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-hover table-striped align-middle table-sticky table-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th class="col-name">Nombre</th>
                                    <th class="text-nowrap">Año</th>
                                    <th class="text-nowrap d-none d-md-table-cell">Frecuencia</th>
                                    <th class="text-nowrap d-none d-sm-table-cell">Periodos</th>
                                    <th class="text-nowrap">Status</th>
                                    <th class="text-nowrap d-none d-lg-table-cell">Creada</th>
                                    <th class="w-1 text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reglas as $r)
                                    @php
                                        $y1   = optional($r->vigencia_inicio)->format('Y');
                                        $y2   = optional($r->vigencia_fin)->format('Y');
                                        $anio = $y1 === $y2 ? $y1 : ($y1.'—'.$y2);

                                        $map = [
                                            'published' => 'bg-green-lt',
                                            'draft'     => 'bg-yellow-lt',
                                            'archived'  => 'bg-secondary',
                                        ];
                                    @endphp
                                    <tr class="{{ $r->status === 'archived' ? 'is-archived' : '' }} ">
                                        <td class="fw-medium">
                                            <span class="text-ellipsis col-name" title="{{ trim($r->nombre.' '.($r->version ? '· '.$r->version : '')) }}">
                                                {{ $r->nombre }}
                                                @if($r->version)
                                                    <span class="text-secondary"> · {{ $r->version }}</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-nowrap">{{ $anio }}</td>
                                        <td class="text-capitalize d-none d-md-table-cell">{{ $r->frecuencia }}</td>
                                        <td class="d-none d-sm-table-cell">
                                            <span class="badge bg-blue-lt">{{ $r->periodos_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-status {{ $map[$r->status] ?? 'bg-secondary' }}">
                                                {{ $r->status }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap d-none d-lg-table-cell">{{ optional($r->created_at)->format('Y-m-d') }}</td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('verificacion-reglas.edit',$r) }}">
                                                    <i class="ti ti-pencil"></i> Editar
                                                </a>
                                                <form action="{{ route('verificacion-reglas.destroy',$r) }}" method="post"
                                                      onsubmit="return confirm('¿Eliminar la regla &quot;{{ $r->nombre }}&quot;? También se eliminarán sus periodos si tu FK usa ON DELETE CASCADE.');">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">
                                                        <i class="ti ti-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-secondary py-4">Sin reglas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($reglas->hasPages())
                    <div class="card-footer d-flex justify-content-center">
                        {{ $reglas->links() }}
                    </div>
                @endif
            </div>

            {{-- ===== Footer ===== --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    @once
        <style>
            /* Pulido de tabla y layout */
            .table-sticky thead th {
                position: sticky;
                top: 0;
                z-index: 2;
                background: var(--tblr-bg-surface, #fff);
            }
            .table thead th {
                font-weight: 600;
            }
            .col-name { max-width: 480px; }
            @media (max-width: 992px) { .col-name { max-width: 320px; } }
            @media (max-width: 576px) { .col-name { max-width: 220px; } }

            .text-ellipsis {
                display: inline-block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                vertical-align: bottom;
            }
            .badge-status {
                text-transform: uppercase;
                letter-spacing: .02em;
                font-weight: 600;
            }
            .btn-group .btn { white-space: nowrap; }
            .card {
                border: 0;
                box-shadow: var(--tblr-shadow, 0 1px 2px rgba(0,0,0,.06));
            }
            .card-table.table > :not(caption) > * > * {
                border-bottom-color: var(--tblr-border-color);
            }
            tr.is-archived td {
                color: var(--tblr-secondary, #6c757d);
            }
        </style>
    @endonce
</x-app-layout>
