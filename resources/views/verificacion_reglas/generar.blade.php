<x-app-layout>
    <div class="container-xl">

        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">Generar periodos</h2>
                    <div class="page-subtitle">
                        Regla: <strong>{{ $regla->nombre }}</strong>
                        @if($regla->version) ({{ $regla->version }}) @endif
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('verificacion-reglas.edit',$regla) }}" class="btn btn-outline-secondary">
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

        <form method="post" action="{{ route('verificacion-reglas.generar',$regla) }}" class="card">
            @csrf
            <div class="card-header">
                <h3 class="card-title">Configuración</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Año</label>
                        <input type="number" name="anio" class="form-control" min="2000" max="2999"
                               value="{{ old('anio', now()->year) }}" required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Acción</label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="sobrescribir" value="1" checked>
                            <span class="form-check-label">Sobrescribir periodos existentes de este año</span>
                        </label>
                    </div>
                </div>

                <div class="mt-3">
                    <p class="text-muted">
                        Se generarán 10 bimestres (Ene-Feb, Feb-Mar, …, Nov-Dic) con terminaciones:
                        <strong>5-6, 7-8, 3-4, 1-2, 9-0</strong> y se repetirá en el segundo semestre.
                    </p>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">
                    <i class="ti ti-calendar-stats"></i> Generar periodos
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
