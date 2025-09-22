<x-app-layout>
    <div class="container-xl">

        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">Nueva regla</h2>
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

        <form method="post" action="{{ route('verificacion-reglas.store') }}" class="card">
            @csrf
            <div class="card-header">
                <h3 class="card-title">Detalles</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="{{ old('nombre') }}" placeholder="CAMe 2025">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Versión</label>
                        <input type="text" name="version" class="form-control" value="{{ old('version') }}" placeholder="2025">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach (['published'=>'published','draft'=>'draft','archived'=>'archived'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('status','published')===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Frecuencia</label>
                        <select name="frecuencia" class="form-select" required>
                            <option value="Semestral" @selected(old('frecuencia')==='Semestral')>Semestral</option>
                            <option value="Anual" @selected(old('frecuencia')==='Anual')>Anual</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vigencia inicio</label>
                        <input type="date" name="vigencia_inicio" class="form-control" value="{{ old('vigencia_inicio') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vigencia fin</label>
                        <input type="date" name="vigencia_fin" class="form-control" value="{{ old('vigencia_fin') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notas</label>
                        <textarea name="notas" class="form-control" rows="2" placeholder="Referencia oficial, URL, etc.">{{ old('notas') }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Estados que aplican</label>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="seleccionarMegalopolis(true)">
                                    Seleccionar Megalópolis
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="seleccionarMegalopolis(false)">
                                    Quitar Megalópolis
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-muted" onclick="toggleTodos()">
                                    Marcar/Desmarcar todos
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            @php $oldEstados = old('estados', []); @endphp
                            @foreach ($catalogoEstados as $e)
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-check">
                                        <input class="form-check-input estado-check" type="checkbox" name="estados[]" value="{{ $e }}"
                                               @checked(in_array($e, $oldEstados))>
                                        <span class="form-check-label">{{ $e }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i> Guardar
                </button>
            </div>
        </form>
    </div>

    <script>
        const MEGALOPOLIS = @json($megalopolis);
        function seleccionarMegalopolis(flag) {
            document.querySelectorAll('.estado-check').forEach(chk => {
                if (MEGALOPOLIS.includes(chk.value)) chk.checked = !!flag;
            });
        }
        function toggleTodos() {
            document.querySelectorAll('.estado-check').forEach(chk => chk.checked = !chk.checked);
        }
    </script>
</x-app-layout>
