<x-app-layout>
    <div class="container-xl">
        {{-- Page header --}}
        <div class="page-header d-print-none mb-3">
            <div class="row align-items-center">
                <div class="col d-flex align-items-center gap-2">
                    <span class="avatar avatar-sm bg-blue-100 text-blue">
                        {{-- Icon: settings --}}
                        <span class="material-symbols-outlined">
                        settings
                        </span>
                    </span>
                    <div>
                        <h2 class="page-title m-0">Nueva regla de verificación</h2>
                        <div class="page-subtitle">
                            Define el periodo por <strong>Estado</strong> y <strong>Terminación de placa</strong>.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form method="post" action="{{ route('calendarios.store') }}" class="mb-4">
            @csrf
            @php $m = $model ?? null; @endphp

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center gap-2">
                        {{-- Icon: calendar-stats --}}
                        <span class="material-symbols-outlined">
                        rule
                        </span>
                        Detalles de la regla
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        {{-- Estado --}}
                        <div class="col-12 col-md-6">
                            <label for="estado" class="form-label d-flex align-items-center gap-2">                                
                            <span class="material-symbols-outlined">
                            location_on
                            </span>
                                Estado
                            </label>
                            <div class="input-icon">
                                
                                <input id="estado" name="estado" type="text"
                                       value="{{ old('estado', $m->estado ?? '') }}"
                                       list="lista-estados"
                                       placeholder="Ej. EDO MEX, MORELOS, JALISCO"
                                       class="form-control @error('estado') is-invalid @enderror">
                            </div>
                            <datalist id="lista-estados">
                                <option value="CDMX" />
                                <option value="EDO MEX" />
                                <option value="MORELOS" />
                                <option value="QUERETARO" />
                                <option value="PUEBLA" />
                                <option value="HIDALGO" />
                                <option value="TLAXCALA" />
                                <option value="JALISCO" />
                                <option value="FEDERAL" />
                            </datalist>
                            @error('estado')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Terminación --}}
                        <div class="col-12 col-md-6">
                            <label for="terminacion" class="form-label d-flex align-items-center gap-2">
                                {{-- Icon: hash --}}
                                <span class="material-symbols-outlined">
                                directions_car
                                </span>
                                Terminación de placa
                            </label>
                            <div class="input-icon">
                                <input id="terminacion" name="terminacion" type="number" min="0" max="9" step="1"
                                       value="{{ old('terminacion', $m->terminacion ?? '') }}"
                                       placeholder="0 a 9"
                                       class="form-control @error('terminacion') is-invalid @enderror">
                            </div>
                            @error('terminacion')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Mes inicio --}}
                        <div class="col-12 col-md-6">
                            <label for="mes_inicio" class="form-label d-flex align-items-center gap-2">
                                {{-- Icon: calendar --}}
                                <span class="material-symbols-outlined">
                                calendar_month
                                </span>
                                Mes inicio
                            </label>
                            <div class="input-icon">
                                <select id="mes_inicio" name="mes_inicio" class="form-select ps-5 @error('mes_inicio') is-invalid @enderror">
                                    @php
                                        $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                                        $mesIni = (int) old('mes_inicio', $m->mes_inicio ?? '');
                                    @endphp
                                    <option value="" @selected(!$mesIni)>Seleccione…</option>
                                    @foreach($meses as $num=>$nombre)
                                        <option value="{{ $num }}" @selected($mesIni === $num)>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('mes_inicio')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Mes fin --}}
                        <div class="col-12 col-md-6">
                            <label for="mes_fin" class="form-label d-flex align-items-center gap-2">
                                {{-- Icon: calendar-event --}}
                                <span class="material-symbols-outlined">
                                calendar_month
                                </span>
                                Mes fin
                            </label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                </span>
                                <select id="mes_fin" name="mes_fin" class="form-select ps-5 @error('mes_fin') is-invalid @enderror">
                                    @php $mesFin = (int) old('mes_fin', $m->mes_fin ?? ''); @endphp
                                    <option value="" @selected(!$mesFin)>Seleccione…</option>
                                    @foreach($meses as $num=>$nombre)
                                        <option value="{{ $num }}" @selected($mesFin === $num)>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('mes_fin')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Semestre --}}
                        <div class="col-12 col-md-6">
                            <label for="semestre" class="form-label d-flex align-items-center gap-2">
                                {{-- Icon: timeline-event --}}
                                <span class="material-symbols-outlined">
                                calendar_month
                                </span>                               
                                Semestre
                            </label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                </span>
                                <select id="semestre" name="semestre" class="form-select ps-5 @error('semestre') is-invalid @enderror">
                                    <option value="">—</option>
                                    <option value="1" @selected(old('semestre', $m->semestre ?? null) == 1)>1</option>
                                    <option value="2" @selected(old('semestre', $m->semestre ?? null) == 2)>2</option>
                                </select>
                            </div>
                            @error('semestre')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Frecuencia (segmented) --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label d-block">Frecuencia</label>
                            <div class="form-selectgroup">
                                @foreach (['Semestral','Anual'] as $f)
                                    @php $checked = old('frecuencia', $m->frecuencia ?? '') === $f; @endphp
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="frecuencia" value="{{ $f }}" class="form-selectgroup-input" @checked($checked)>
                                        <span class="form-selectgroup-label d-inline-flex align-items-center gap-2">
                                            {{-- Icon inside pill --}}
                                            @if($f === 'Semestral')
                                                <span class="material-symbols-outlined">
                                                replay
                                                </span>
                                            @else
                                                <span class="material-symbols-outlined">
                                                replay
                                                </span>
                                            @endif
                                            {{ $f }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('frecuencia')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Año (opcional) --}}
                        <div class="col-12 col-md-6">
                            <label for="anio" class="form-label d-flex align-items-center gap-2">
                                {{-- Icon: calendar-stats --}}
                                <span class="material-symbols-outlined">
                                calendar_month
                                </span>
                                Año (opcional)
                            </label>
                            <div class="input-icon">
                                <input id="anio" name="anio" type="number" min="2000" max="2100"
                                       value="{{ old('anio', $m->anio ?? '') }}"
                                       placeholder="Ej. 2025"
                                       class="form-control @error('anio') is-invalid @enderror">
                            </div>
                            @error('anio')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Vigente desde --}}
                        <div class="col-12 col-md-6">
                            <label for="vigente_desde" class="form-label d-flex align-items-center gap-2">
                                {{-- Icon: calendar-plus --}}
                                <span class="material-symbols-outlined">
                                today
                                </span>
                                Vigente desde
                            </label>
                            <div class="input-icon">
                                <input id="vigente_desde" name="vigente_desde" type="date"
                                       value="{{ old('vigente_desde', optional($m->vigente_desde ?? null)->format('Y-m-d')) }}"
                                       class="form-control @error('vigente_desde') is-invalid @enderror">
                            </div>
                            @error('vigente_desde')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Vigente hasta --}}
                        <div class="col-12 col-md-6">
                            <label for="vigente_hasta" class="form-label d-flex align-items-center gap-2">
                                {{-- Icon: calendar-x --}}
                                <span class="material-symbols-outlined">
                                event
                                </span>
                                Vigente hasta
                            </label>
                            <div class="input-icon">
                                <input id="vigente_hasta" name="vigente_hasta" type="date"
                                       value="{{ old('vigente_hasta', optional($m->vigente_hasta ?? null)->format('Y-m-d')) }}"
                                       class="form-control @error('vigente_hasta') is-invalid @enderror">
                            </div>
                            @error('vigente_hasta')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex gap-2">
                    <a href="{{ route('calendarios.index') }}" class="btn btn-link d-inline-flex align-items-center gap-2">
                        {{-- Icon: arrow-left --}}
                        <span class="material-symbols-outlined">
                        arrow_back
                        </span>
                        Cancelar
                    </a>
                    <button class="btn btn-danger ms-auto d-inline-flex align-items-center gap-2" type="submit">
                        {{-- Icon: device-floppy (save) --}}
                        <span class="material-symbols-outlined">
                        save
                        </span>
                        Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- UX helpers (no crea archivos nuevos) --}}
    <script>
        // Uppercase automático del estado (evita variantes)
        const estado = document.getElementById('estado');
        if (estado) {
            estado.addEventListener('blur', () => estado.value = estado.value.trim().toUpperCase());
        }

        // Habilitar/inhabilitar "semestre" cuando la frecuencia es Anual
        const radios = document.querySelectorAll('input[name="frecuencia"]');
        const semestre = document.getElementById('semestre');
        function toggleSemestre() {
            const val = document.querySelector('input[name="frecuencia"]:checked')?.value;
            const anual = val === 'Anual';
            semestre.disabled = anual;
            if (anual) semestre.value = '';
        }
        radios.forEach(r => r.addEventListener('change', toggleSemestre));
        toggleSemestre();
    </script>
</x-app-layout>
