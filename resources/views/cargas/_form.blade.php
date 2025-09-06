@php
    $isEdit = isset($carga) && $carga->exists;
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium">Fecha</label>
        <input type="date" name="fecha"
               value="{{ old('fecha', optional($carga->fecha ?? null)->format('Y-m-d')) }}"
               class="mt-1 w-full rounded-lg border-gray-300" required>
        @error('fecha')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Ubicación</label>
        <select name="ubicacion" class="mt-1 w-full rounded-lg border-gray-300">
            <option value="">—</option>
            @foreach($ubicaciones as $u)
                <option value="{{ $u }}" @selected(old('ubicacion', $carga->ubicacion ?? null) === $u)>{{ $u }}</option>
            @endforeach
        </select>
        @error('ubicacion')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Tipo de combustible</label>
        <select name="tipo_combustible" class="mt-1 w-full rounded-lg border-gray-300" required>
            @foreach($tipos as $t)
                <option value="{{ $t }}" @selected(old('tipo_combustible', $carga->tipo_combustible ?? null) === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('tipo_combustible')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Precio ($)</label>
        <input type="number" step="0.01" min="0" name="precio"
               value="{{ old('precio', $carga->precio ?? null) }}"
               class="mt-1 w-full rounded-lg border-gray-300" required>
        @error('precio')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Litros</label>
        <input type="number" step="0.001" min="0.001" name="litros"
               value="{{ old('litros', $carga->litros ?? null) }}"
               class="mt-1 w-full rounded-lg border-gray-300" required>
        @error('litros')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Custodio</label>
        <input type="text" name="custodio"
               value="{{ old('custodio', $carga->custodio ?? null) }}"
               class="mt-1 w-full rounded-lg border-gray-300">
        @error('custodio')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Operador</label>
        <select name="operador_id" class="mt-1 w-full rounded-lg border-gray-300" required>
            <option value="">Seleccione...</option>
            @foreach($operadores as $op)
                <option value="{{ $op->id }}"
                    @selected((int)old('operador_id', $carga->operador_id ?? 0) === $op->id)>
                    {{ $op->nombre }} {{ $op->apellido_paterno }} {{ $op->apellido_materno }}
                </option>
            @endforeach
        </select>
        @error('operador_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium">Vehículo (Unidad / Placa)</label>
        <select name="vehiculo_id" class="mt-1 w-full rounded-lg border-gray-300" required>
            <option value="">Seleccione...</option>
            @foreach($vehiculos as $v)
                <option value="{{ $v->id }}"
                    @selected((int)old('vehiculo_id', $carga->vehiculo_id ?? 0) === $v->id)>
                    {{ $v->unidad }} — {{ $v->placa ?? $v->placas }}
                </option>
            @endforeach
        </select>
        @error('vehiculo_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">KM Inicial</label>
        <input type="number" name="km_inicial"
               value="{{ old('km_inicial', $carga->km_inicial ?? null) }}"
               class="mt-1 w-full rounded-lg border-gray-300">
        @error('km_inicial')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">KM Final</label>
        <input type="number" name="km_final"
               value="{{ old('km_final', $carga->km_final ?? null) }}"
               class="mt-1 w-full rounded-lg border-gray-300">
        @error('km_final')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Destino</label>
        <input type="text" name="destino"
               value="{{ old('destino', $carga->destino ?? null) }}"
               class="mt-1 w-full rounded-lg border-gray-300">
        @error('destino')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-3">
        <label class="block text-sm font-medium">Observaciones</label>
        <textarea name="observaciones" rows="3"
                  class="mt-1 w-full rounded-lg border-gray-300">{{ old('observaciones', $carga->observaciones ?? null) }}</textarea>
        @error('observaciones')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- Campos derivados (solo lectura) en edición --}}
    @if($isEdit)
        <div>
            <label class="block text-sm font-medium">Total ($)</label>
            <input type="text" value="{{ number_format($carga->total, 2) }}"
                   class="mt-1 w-full rounded-lg border-gray-300 bg-gray-100" disabled>
        </div>
        <div>
            <label class="block text-sm font-medium">Recorrido (km)</label>
            <input type="text" value="{{ $carga->recorrido }}"
                   class="mt-1 w-full rounded-lg border-gray-300 bg-gray-100" disabled>
        </div>
        <div>
            <label class="block text-sm font-medium">Rendimiento (km/L)</label>
            <input type="text" value="{{ $carga->rendimiento }}"
                   class="mt-1 w-full rounded-lg border-gray-300 bg-gray-100" disabled>
        </div>
        <div>
            <label class="block text-sm font-medium">Dif $</label>
            <input type="text" value="{{ isset($carga->diferencia) ? number_format($carga->diferencia, 2) : '' }}"
                   class="mt-1 w-full rounded-lg border-gray-300 bg-gray-100" disabled>
        </div>
    @endif
</div>
