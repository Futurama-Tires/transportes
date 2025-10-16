{{-- resources/views/licencias/edit.blade.php --}}
<x-app-layout>
    @php
        $op = $licencia->operador;
        $nombre = $op?->nombre_completo ?? trim(($op->nombre ?? '').' '.($op->apellido_paterno ?? '').' '.($op->apellido_materno ?? ''));
    @endphp

    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <p class="text-secondary text-uppercase small mb-1">Licencias</p>
                        <h2 class="page-title mb-0">Editar licencia #{{ $licencia->id }}</h2>
                        <div class="text-secondary small mt-1">
                            Operador: @if($op) <a href="{{ route('operadores.edit', $op) }}">{{ $nombre ?: 'Operador #'.$op->id }}</a> @else — @endif
                        </div>
                    </div>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('licencias.index') }}" class="btn btn-outline-secondary">
                            <span class="material-symbols-outlined me-1 align-middle">arrow_back</span> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            @if(session('success'))
                <div class="alert alert-success mb-4" role="alert">
                    <span class="material-symbols-outlined me-2 align-middle">check_circle</span>{{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4" role="alert">
                    <span class="material-symbols-outlined me-2 align-middle">warning</span>Corrige los errores y vuelve a intentar.
                </div>
            @endif

            <div class="row g-4">

                <div class="col-12 col-xl-7">
                    {{-- ===== FORM DATOS ===== --}}
                    <form method="POST" action="{{ route('licencias.update', $licencia) }}" novalidate>
                        @csrf @method('PUT')

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0"><span class="material-symbols-outlined me-1 align-middle">edit_note</span>Datos de la licencia</h3>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Ámbito</label>
                                        <select name="ambito" class="form-select @error('ambito') is-invalid @enderror">
                                            <option value="">(sin especificar)</option>
                                            <option value="federal" {{ old('ambito', $licencia->ambito)==='federal'?'selected':'' }}>Federal</option>
                                            <option value="estatal" {{ old('ambito', $licencia->ambito)==='estatal'?'selected':'' }}>Estatal</option>
                                        </select>
                                        @error('ambito') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Tipo</label>
                                        <input name="tipo" type="text" class="form-control @error('tipo') is-invalid @enderror"
                                               value="{{ old('tipo', $licencia->tipo) }}" maxlength="50" style="text-transform:uppercase">
                                        @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Folio</label>
                                        <input name="folio" type="text" class="form-control @error('folio') is-invalid @enderror"
                                               value="{{ old('folio', $licencia->folio) }}" maxlength="50" style="text-transform:uppercase"
                                               oninput="this.value=this.value.toUpperCase().replace(/\s+/g,'');">
                                        @error('folio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Fecha expedición</label>
                                        <input name="fecha_expedicion" type="date" class="form-control @error('fecha_expedicion') is-invalid @enderror"
                                               value="{{ old('fecha_expedicion', optional($licencia->fecha_expedicion)->format('Y-m-d')) }}">
                                        @error('fecha_expedicion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Fecha vencimiento</label>
                                        <input name="fecha_vencimiento" type="date" class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                                               value="{{ old('fecha_vencimiento', optional($licencia->fecha_vencimiento)->format('Y-m-d')) }}">
                                        @error('fecha_vencimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Emisor</label>
                                        <input name="emisor" type="text" class="form-control @error('emisor') is-invalid @enderror"
                                               value="{{ old('emisor', $licencia->emisor) }}" maxlength="100">
                                        @error('emisor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Estado/Ciudad de emisión</label>
                                        <input name="estado_emision" type="text" class="form-control @error('estado_emision') is-invalid @enderror"
                                               value="{{ old('estado_emision', $licencia->estado_emision) }}" maxlength="100">
                                        @error('estado_emision') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Observaciones</label>
                                        <textarea name="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $licencia->observaciones) }}</textarea>
                                        @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Operador id (no editable aquí normalmente) --}}
                                    <input type="hidden" name="operador_id" value="{{ $licencia->operador_id }}">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('licencias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button class="btn btn-primary" type="submit">
                                <span class="material-symbols-outlined me-1 align-middle">save</span>Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-12 col-xl-5">
                    {{-- ===== ARCHIVOS ===== --}}
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><span class="material-symbols-outlined me-1 align-middle">picture_as_pdf</span>Archivos de la licencia</h3>
                            <div class="card-subtitle">PDF/JPG/PNG/WEBP — privados</div>
                        </div>
                        <div class="card-body">
                            {{-- Subir nuevos --}}
                            <form method="POST" action="{{ route('licencias.archivos.store', $licencia) }}" enctype="multipart/form-data" class="mb-3">
                                @csrf
                                <div class="row g-2 align-items-end">
                                    <div class="col-12">
                                        <label class="form-label">Selecciona archivos</label>
                                        <input type="file" name="archivos[]" class="form-control @error('archivos') is-invalid @enderror @error('archivos.*') is-invalid @enderror" multiple accept=".pdf,image/*">
                                        @error('archivos')   <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        @error('archivos.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        <div class="form-hint">Hasta 12 archivos; máx 10MB c/u. Se guardan en privado.</div>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-outline-primary" type="submit">
                                            <span class="material-symbols-outlined me-1 align-middle">upload</span>Subir
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {{-- Lista de archivos --}}
                            @forelse($licencia->archivos as $a)
                                <div class="card card-sm mb-2">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="fw-semibold">{{ $a->nombre_original }}</div>
                                            <div class="text-secondary small">
                                                {{ strtoupper($a->mime ?? 'N/D') }} · {{ number_format(($a->size ?? 0)/1024, 0) }} KB
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('licencias.archivos.inline', $a) }}" class="btn btn-outline-secondary btn-sm" target="_blank" title="Ver">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </a>
                                            <a href="{{ route('licencias.archivos.download', $a) }}" class="btn btn-outline-secondary btn-sm" title="Descargar">
                                                <span class="material-symbols-outlined">download</span>
                                            </a>
                                            <form action="{{ route('licencias.archivos.destroy', $a) }}" method="POST" onsubmit="return confirm('¿Eliminar archivo {{ $a->nombre_original }}?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-sm" title="Eliminar">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty my-3">
                                    <div class="empty-icon"><span class="material-symbols-outlined">folder_off</span></div>
                                    <p class="empty-title">Aún no hay archivos</p>
                                    <p class="empty-subtitle text-secondary">Sube un PDF o imagen escaneada de la licencia.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div> {{-- /row --}}
        </div>
    </div>
</x-app-layout>
