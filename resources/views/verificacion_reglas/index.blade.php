<x-app-layout>
    <div class="container-xl">

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <strong>Errores:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title text-dark">Reglas de verificación</h2>
                    <div class="page-subtitle text-dark">Crea reglas, asígnales estados y genera periodos bimestrales.</div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('verificacion-reglas.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i>
                        Nueva regla
                    </a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Versión</th>
                                <th>Status</th>
                                <th>Frecuencia</th>
                                <th>Vigencia</th>
                                <th>Estados</th>
                                <th>Periodos</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($reglas as $r)
                            <tr>
                                <td class="text-nowrap text-dark">
                                    <strong>{{ $r->nombre }}</strong>
                                </td>
                                <td class="text-dark">{{ $r->version ?? '—' }}</td>
                                <td>
                                    <span class="badge @if($r->status==='published') bg-green @elseif($r->status==='draft') bg-yellow @else bg-secondary @endif">
                                        {{ $r->status }}
                                    </span>
                                </td>
                                <td class="text-dark">{{ $r->frecuencia }}</td>
                                <td class="text-nowrap text-dark">
                                    {{ $r->vigencia_inicio?->format('Y-m-d') ?? '—' }} —
                                    {{ $r->vigencia_fin?->format('Y-m-d') ?? '—' }}
                                </td>
                                <td>
                                    @php $countEstados = is_array($r->estados) ? count($r->estados) : 0; @endphp
                                    <span class="badge bg-blue text-white">{{ $countEstados }}</span>
                                </td>
                                <td>
                                    {{-- Cambiamos bg-muted (gris) por un color visible --}}
                                    <span class="badge bg-dark text-white">{{ $r->periodos_count }}</span>
                                </td>
                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('verificacion-reglas.generar.form', $r) }}">
                                        <i class="ti ti-calendar"></i> Generar
                                    </a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('verificacion-reglas.edit', $r) }}">
                                        <i class="ti ti-pencil"></i> Editar
                                    </a>
                                    <form action="{{ route('verificacion-reglas.destroy', $r) }}" method="post" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar la regla? Puedes optar por borrar también sus periodos en el siguiente paso.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- Texto visible (no gris) cuando no hay reglas --}}
                                <td colspan="8" class="text-center text-dark">No hay reglas registradas aún.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($reglas->hasPages())
                <div class="card-footer d-flex align-items-center">
                    {{ $reglas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
