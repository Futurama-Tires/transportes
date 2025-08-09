@if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Ubicación *</label>
    <select name="ubicacion" class="w-full border-gray-300 rounded" required>
        <option value="">-- Selecciona ubicación --</option>
        <option value="CVC" {{ old('ubicacion', $vehiculo->ubicacion ?? '') == 'CVC' ? 'selected' : '' }}>Cuernavaca</option>
        <option value="IXT" {{ old('ubicacion', $vehiculo->ubicacion ?? '') == 'IXT' ? 'selected' : '' }}>Ixtapaluca</option>
        <option value="QRO" {{ old('ubicacion', $vehiculo->ubicacion ?? '') == 'QRO' ? 'selected' : '' }}>Queretaro</option>
        <option value="VALL" {{ old('ubicacion', $vehiculo->ubicacion ?? '') == 'VALL' ? 'selected' : '' }}>Vallejo</option>
        <option value="GDL" {{ old('ubicacion', $vehiculo->ubicacion ?? '') == 'GDL' ? 'selected' : '' }}>Guadalajara</option>
    </select>
    @error('ubicacion')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Propietario *</label>
    <input type="text" name="propietario" value="{{ old('propietario', $vehiculo->propietario ?? '') }}"
           class="w-full border-gray-300 rounded" required>
    @error('propietario')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Unidad *</label>
    <input type="text" name="unidad" value="{{ old('unidad', $vehiculo->unidad ?? '') }}"
           class="w-full border-gray-300 rounded" required>
    @error('unidad')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Serie *</label>
    <input type="text" name="serie" value="{{ old('serie', $vehiculo->serie ?? '') }}"
           class="w-full border-gray-300 rounded" required>
    @error('serie')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

{{-- Campos opcionales --}}
<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Marca</label>
    <input type="text" name="marca" value="{{ old('marca', $vehiculo->marca ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('marca')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

{{-- Campo Año --}}
<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Año</label>
    <input type="number" name="anio" min="1900" max="{{ date('Y') }}"
           value="{{ old('anio', $vehiculo->anio ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('anio')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>


<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Motor</label>
    <input type="text" name="motor" value="{{ old('motor', $vehiculo->motor ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('motor')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Placa</label>
    <input type="text" name="placa" value="{{ old('placa', $vehiculo->placa ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('placa')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Estado</label>
    <input type="text" name="estado" value="{{ old('estado', $vehiculo->estado ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('estado')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Tarjeta SiVale</label>
    <select name="tarjeta_si_vale_id" class="w-full border-gray-300 rounded">
        <option value="">-- Sin tarjeta asignada --</option>
        @foreach($tarjetas as $tarjeta)
            <option value="{{ $tarjeta->id }}"
                {{ old('tarjeta_si_vale_id', $vehiculo->tarjeta_si_vale_id ?? '') == $tarjeta->id ? 'selected' : '' }}>
                {{ $tarjeta->numero_tarjeta }}
            </option>
        @endforeach
    </select>
    @error('tarjeta_si_vale_id')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>


<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Vencimiento tarjeta circulación</label>
    <input type="date" name="vencimiento_t_circulacion" value="{{ old('vencimiento_t_circulacion', $vehiculo->vencimiento_t_circulacion ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('vencimiento_t_circulacion')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Cambio de placas</label>
    <input type="date" name="cambio_placas" value="{{ old('cambio_placas', $vehiculo->cambio_placas ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('cambio_placas')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Póliza HDI</label>
    <input type="text" name="poliza_hdi" value="{{ old('poliza_hdi', $vehiculo->poliza_hdi ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('poliza_hdi')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-300">Rendimiento (km/l)</label>
    <input type="number" step="0.01" name="rend" value="{{ old('rend', $vehiculo->rend ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('rend')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
