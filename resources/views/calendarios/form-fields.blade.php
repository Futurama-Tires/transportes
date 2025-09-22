@php $m = $model ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Estado --}}
    <div>
        <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
        <input id="estado" name="estado" type="text"
               value="{{ old('estado', $m->estado ?? '') }}"
               placeholder="Ej. EDO MEX, MORELOS, JALISCO"
               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
        @error('estado')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Terminación --}}
    <div>
        <label for="terminacion" class="block text-sm font-medium text-gray-700 mb-1">Terminación de placa</label>
        <input id="terminacion" name="terminacion" type="number" min="0" max="9"
               value="{{ old('terminacion', $m->terminacion ?? '') }}"
               placeholder="0 a 9"
               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
        @error('terminacion')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Mes inicio --}}
    <div>
        <label for="mes_inicio" class="block text-sm font-medium text-gray-700 mb-1">Mes inicio</label>
        <input id="mes_inicio" name="mes_inicio" type="number" min="1" max="12"
               value="{{ old('mes_inicio', $m->mes_inicio ?? '') }}"
               placeholder="1 = Enero"
               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
        @error('mes_inicio')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Mes fin --}}
    <div>
        <label for="mes_fin" class="block text-sm font-medium text-gray-700 mb-1">Mes fin</label>
        <input id="mes_fin" name="mes_fin" type="number" min="1" max="12"
               value="{{ old('mes_fin', $m->mes_fin ?? '') }}"
               placeholder="2 = Febrero"
               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
        @error('mes_fin')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Semestre --}}
    <div>
        <label for="semestre" class="block text-sm font-medium text-gray-700 mb-1">Semestre</label>
        <select id="semestre" name="semestre"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            <option value="">—</option>
            <option value="1" @selected(old('semestre', $m->semestre ?? null) == 1)>1</option>
            <option value="2" @selected(old('semestre', $m->semestre ?? null) == 2)>2</option>
        </select>
        @error('semestre')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Frecuencia --}}
    <div>
        <label for="frecuencia" class="block text-sm font-medium text-gray-700 mb-1">Frecuencia</label>
        <select id="frecuencia" name="frecuencia"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            @foreach (['Semestral','Anual'] as $f)
                <option value="{{ $f }}" @selected(old('frecuencia', $m->frecuencia ?? '') === $f)>{{ $f }}</option>
            @endforeach
        </select>
        @error('frecuencia')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Año --}}
    <div>
        <label for="anio" class="block text-sm font-medium text-gray-700 mb-1">Año (opcional)</label>
        <input id="anio" name="anio" type="number" min="2000" max="2100"
               value="{{ old('anio', $m->anio ?? '') }}"
               placeholder="Ej. 2025"
               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
        @error('anio')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Vigencia desde --}}
    <div>
        <label for="vigente_desde" class="block text-sm font-medium text-gray-700 mb-1">Vigente desde</label>
        <input id="vigente_desde" name="vigente_desde" type="date"
               value="{{ old('vigente_desde', optional($m->vigente_desde ?? null)->format('Y-m-d')) }}"
               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
        @error('vigente_desde')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Vigencia hasta --}}
    <div>
        <label for="vigente_hasta" class="block text-sm font-medium text-gray-700 mb-1">Vigente hasta</label>
        <input id="vigente_hasta" name="vigente_hasta" type="date"
               value="{{ old('vigente_hasta', optional($m->vigente_hasta ?? null)->format('Y-m-d')) }}"
               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
        @error('vigente_hasta')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
