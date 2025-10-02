{{-- resources/views/licencias/create.blade.php --}}
<x-app-layout>
    @vite(['resources/js/app.js'])

    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <p class="text-secondary text-uppercase small mb-1">Licencias</p>
                        <h2 class="page-title mb-0">Nueva licencia</h2>
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

            @if ($errors->any())
                <div class="alert alert-danger mb-4" role="alert">
                    <span class="material-symbols-outlined me-2 align-middle">warning</span>Corrige los errores y vuelve a intentar.
                    <ul class="mt-2 mb-0 ps-4">
                        @foreach($errors->all() as $e) <li class="small">{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('licencias.store') }}" novalidate>
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><span class="material-symbols-outlined me-1 align-middle">badge</span> Datos de la licencia</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">

                            {{-- Operador --}}
                            @if(isset($operador) && $operador)
                                <div class="col-12">
                                    <label class="form-label">Operador</label>
                                    <input type="hidden" name="operador_id" value="{{ $operador->id }}">
                                    <input type="text" class="form-control" value="{{ $operador->nombre_completo }} (ID: {{ $operador->id }})" disabled>
                                </div>
                            @else
                                <div class="col-12 col-md-6">
                                    <label for="operador_id" class="form-label">Operador (ID) <span class="text-danger">*</span></label>
                                    <input id="operador_id" name="operador_id" type="number" class="form-control @error('operador_id') is-invalid @enderror" value="{{ old('operador_id') }}" required>
                                    @error('operador_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div class="col-12 col-md-4">
                                <label class="form-label">Ámbito</label>
                                <select name="ambito" class="form-select @error('ambito') is-invalid @enderror">
                                    <option value="">(sin especificar)</option>
                                    <option value="federal" {{ old('ambito')==='federal'?'selected':'' }}>Federal</option>
                                    <option value="estatal" {{ old('ambito')==='estatal'?'selected':'' }}>Estatal</option>
                                </select>
                                @error('ambito') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Tipo</label>
                                <input name="tipo" type="text" class="form-control @error('tipo') is-invalid @enderror"
                                       value="{{ old('tipo') }}" placeholder="Ej. A, B, Chofer" maxlength="50" style="text-transform:uppercase">
                                @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Folio</label>
                                <input name="folio" type="text" class="form-control @error('folio') is-invalid @enderror"
                                       value="{{ old('folio') }}" maxlength="50" style="text-transform:uppercase"
                                       oninput="this.value=this.value.toUpperCase().replace(/\s+/g,'');">
                                @error('folio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Fecha expedición</label>
                                <input name="fecha_expedicion" type="date" class="form-control @error('fecha_expedicion') is-invalid @enderror" value="{{ old('fecha_expedicion') }}">
                                @error('fecha_expedicion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Fecha vencimiento</label>
                                <input name="fecha_vencimiento" type="date" class="form-control @error('fecha_vencimiento') is-invalid @enderror" value="{{ old('fecha_vencimiento') }}">
                                @error('fecha_vencimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Emisor</label>
                                <input name="emisor" type="text" class="form-control @error('emisor') is-invalid @enderror" value="{{ old('emisor') }}" maxlength="100" placeholder="Ej. SCT">
                                @error('emisor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Estado/Ciudad de emisión</label>
                                <input name="estado_emision" type="text" class="form-control @error('estado_emision') is-invalid @enderror" value="{{ old('estado_emision') }}" maxlength="100" placeholder="Ej. Morelos">
                                @error('estado_emision') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror" placeholder="Notas adicionales…">{{ old('observaciones') }}</textarea>
                                @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('licencias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button class="btn btn-primary" type="submit">
                        <span class="material-symbols-outlined me-1 align-middle">save</span>Guardar
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
