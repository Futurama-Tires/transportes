<x-app-layout>
    <div class="container-xl">

        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">Editar regla</h2>
                    <div class="page-subtitle">{{ $regla->nombre }}</div>
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

        <form method="post" action="{{ route('verificacion-reglas.update',$regla) }}" class="card">
            @csrf @method('PUT')
            <div class="card-header">
                <h3 class="card-title">Detalles</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="{{ old('nombre',$regla->nombre) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Versión</label>
                        <input type="text" name="version" class="form-control" value="{{ old('version',$regla->version) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach (['published','draft','archived'] as $opt)
                                <option value="{{ $opt }}" @selected(old('status',$regla->status)===$opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Frecuencia</label>
                        <select name="frecuencia" class="form-select" required>
                            @foreach (['Semestral','Anual'] as $opt)
                                <option value="{{ $opt }}" @selected(old('frecuencia',$regla->frecuencia)===$opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vigencia inicio</label>
                        <input type="date" name="vigencia_inicio" class="form-control" value="{{ old('vigencia_inicio', optional($regla->vigencia_inicio)->toDateString()) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vigencia fin</label>
                        <input type="date" name="vigencia_fin" class="form-control" value="{{ old('vigencia_fin', optional($regla->vigencia_fin)->toDateString()) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notas</label>
                        <textarea name="notas" class="form-control" rows="2">{{ old('notas',$regla->notas) }}</textarea>
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
                            @php
                                $seleccionados = old('estados', is_array($regla->estados)?$regla->estados:[]);
                            @endphp
                            @foreach ($catalogoEstados as $e)
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-check">
                                        <input class="form-check-input estado-check" type="checkbox" name="estados[]" value="{{ $e }}"
                                               @checked(in_array($e, $seleccionados))>
                                        <span class="form-check-label">{{ $e }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('verificacion-reglas.generar.form',$regla) }}" class="btn btn-outline-primary">
                    <i class="ti ti-calendar"></i> Generar periodos
                </a>
                <div>
                    <button class="btn btn-primary">
                        <i class="ti ti-device-floppy"></i> Guardar cambios
                    </button>
                </div>
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
