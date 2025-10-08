{{-- resources/views/verificacion_reglas/generar.blade.php --}}
<x-app-layout>
    <div class="container-xl">
        <div class="page-header d-print-none mb-3">
            <div class="row align-items-center">
                <div class="col">
                    <br>
                    <h2 class="page-title">Regenerar periodos</h2>
                    <div class="page-subtitle text-secondary">
                        {{ $regla->nombre }} @if($regla->version) · {{ $regla->version }} @endif
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('verificacion-reglas.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <form method="post" action="{{ route('verificacion-reglas.generar',$regla) }}">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Año</label>
                            <input type="number" class="form-control" name="anio" min="2000" max="2999" value="{{ old('anio', now()->year) }}" required>
                        </div>
                        <div class="col-md-9 d-flex align-items-end">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="sobrescribir" value="1">
                                <span class="form-check-label">
                                    Sobrescribir periodos existentes de esta regla para el año elegido.
                                </span>
                            </label>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="h5">Estados (por año en esta regla)</h4>
                            @php
                                $grouped = $regla->estadosAsignados->groupBy('anio')->sortKeys();
                            @endphp
                            @forelse($grouped as $anio => $items)
                                <div class="mb-2">
                                    <div class="fw-medium">{{ $anio }}</div>
                                    <div>
                                        @foreach($items as $it)
                                            <span class="badge bg-blue-lt me-1 mb-1">{{ $it->estado }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-secondary">Sin estados asignados aún.</div>
                            @endforelse
                        </div>
                        <div class="col-md-6">
                            <h4 class="h5">Detalles de terminación</h4>
                            <div class="text-secondary">
                                {{ $regla->frecuencia === 'Anual' ? 'Anual' : 'Semestral' }} ·
                                {{ $regla->detalles()->count() }} filas de detalle
                            </div>
                            <small class="form-hint">
                                Los periodos se generan combinando estados del año con las ventanas por terminación.
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('verificacion-reglas.edit',$regla) }}" class="btn btn-link">Cancelar</a>
                    <button class="btn btn-indigo">
                        <i class="ti ti-refresh"></i> Regenerar periodos
                    </button>
                </div>
            </form>
        </div>
        {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
    </div>
</x-app-layout>
