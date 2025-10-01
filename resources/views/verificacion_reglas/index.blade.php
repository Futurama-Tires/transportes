{{-- resources/views/verificacion_reglas/index.blade.php --}}
<x-app-layout>
    <div class="container-xl">
        {{-- Header --}}
        <div class="page-header d-print-none mb-3">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">Reglas de verificación</h2>
                    <div class="page-subtitle text-secondary">Gestiona las reglas por año y consulta los periodos generados.</div>
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('verificacion-reglas.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Nueva regla
                    </a>
                </div>
            </div>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Año</th>
                                <th>Frecuencia</th>
                                <th>Periodos</th>
                                <th>Status</th>
                                <th>Creada</th>
                                <th class="w-1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reglas as $r)
                                <tr>
                                    <td class="fw-medium">
                                        {{ $r->nombre }}
                                        @if($r->version)
                                            <span class="text-secondary"> · {{ $r->version }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $y1 = optional($r->vigencia_inicio)->format('Y');
                                            $y2 = optional($r->vigencia_fin)->format('Y');
                                        @endphp
                                        {{ $y1 === $y2 ? $y1 : ($y1.'—'.$y2) }}
                                    </td>
                                    <td>{{ $r->frecuencia }}</td>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ $r->periodos_count }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $map = [
                                                'published' => 'bg-green-lt',
                                                'draft'     => 'bg-yellow-lt',
                                                'archived'  => 'bg-secondary',
                                            ];
                                        @endphp
                                        <span class="badge {{ $map[$r->status] ?? 'bg-secondary' }}">{{ $r->status }}</span>
                                    </td>
                                    <td>{{ optional($r->created_at)->format('Y-m-d') }}</td>
                                    <td class="text-nowrap">
                                        <div class="btn-list">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('verificacion-reglas.edit',$r) }}">
                                                <i class="ti ti-pencil"></i> Editar
                                            </a>
                                            <a class="btn btn-sm btn-outline-indigo" href="{{ route('verificacion-reglas.generar.form',$r) }}">
                                                <i class="ti ti-refresh"></i> Regenerar
                                            </a>
                                            <form action="{{ route('verificacion-reglas.destroy',$r) }}" method="post" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar la regla \"{{ $r->nombre }}\"? Se eliminarán también sus periodos si tienes ON DELETE CASCADE.');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="ti ti-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-secondary py-4">Sin reglas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($reglas->hasPages())
                <div class="card-footer">
                    {{ $reglas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
