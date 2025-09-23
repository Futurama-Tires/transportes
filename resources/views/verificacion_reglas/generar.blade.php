<x-app-layout>
    <div class="container-xl">

        <div class="page-header d-print-none mb-3">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title text-dark">Generar periodos — {{ $regla->nombre }}</h2>
                    <div class="text-dark">Usará los <strong>estados asignados por año</strong> y los <strong>detalles capturados</strong> (terminación → meses).</div>
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

        <form method="post" action="{{ route('verificacion-reglas.generar', $regla) }}" class="card">
            @csrf
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Año</label>
                        <input type="number" name="anio" min="2000" max="2999" class="form-control" value="{{ now()->year }}" required>
                    </div>
                    <div class="col-md-9 d-flex align-items-end">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="sobrescribir" value="1" checked>
                            <span class="form-check-label">Sobrescribir periodos existentes de este año</span>
                        </label>
                    </div>
                </div>
                <div class="mt-3 text-secondary">
                    <i class="ti ti-info-circle"></i> Si la regla no tiene <strong>estados asignados</strong> para el año elegido, se mostrará un error.
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('verificacion-reglas.index') }}" class="btn btn-link">Cancelar</a>
                <button class="btn btn-primary"><i class="ti ti-calendar-check"></i> Generar periodos</button>
            </div>
        </form>
    </div>
</x-app-layout>
