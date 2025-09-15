<x-app-layout>
    @vite(['resources/js/app.js'])

    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl d-flex justify-content-between align-items-center">
                <h2 class="page-title mb-0">
                    <i class="ti ti-receipt"></i> Nuevo Gasto — •••• {{ $tarjeta->last4 }}
                </h2>
                <a href="{{ route('comodin-gastos.index', ['tarjeta' => $tarjeta->id]) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('tarjetas-comodin.gastos.store', $tarjeta) }}" class="card">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Concepto</label>
                            <input type="text" name="concepto" class="form-control" value="{{ old('concepto') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Monto</label>
                            <input type="number" step="0.01" min="0" name="monto" class="form-control" value="{{ old('monto') }}" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary"><i class="ti ti-device-floppy"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
