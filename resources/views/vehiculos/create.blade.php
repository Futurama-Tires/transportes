{{-- resources/views/vehiculos/create.blade.php --}}
<x-app-layout>
    {{-- Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Vehículos</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                    Agregar Vehículo
                </h2>
            </div>
            <a href="{{ route('vehiculos.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                {{-- back icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m10 19-7-7 7-7M3 12h18"/>
                </svg>
                Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('vehiculos.store') }}" novalidate>
                @csrf

                {{-- Alerta de errores globales --}}
                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900/40 dark:bg-rose-900/30 dark:text-rose-100">
                        Revisa los campos marcados y vuelve a intentar.
                    </div>
                @endif

                {{-- Tarjeta --}}
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Datos del vehículo</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Completa la información del vehículo. Los campos con <span class="text-rose-600">*</span> son obligatorios.
                        </p>
                    </div>

                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            {{-- Ubicación --}}
                            <div>
                                <label for="ubicacion" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Ubicación <span class="text-rose-600">*</span>
                                </label>
                                <select id="ubicacion" name="ubicacion" required
                                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                    <option value="">-- Selecciona ubicación --</option>
                                    <option value="CVC" {{ old('ubicacion') == 'CVC' ? 'selected' : '' }}>Cuernavaca</option>
                                    <option value="IXT" {{ old('ubicacion') == 'IXT' ? 'selected' : '' }}>Ixtapaluca</option>
                                    <option value="QRO" {{ old('ubicacion') == 'QRO' ? 'selected' : '' }}>Querétaro</option>
                                    <option value="VALL" {{ old('ubicacion') == 'VALL' ? 'selected' : '' }}>Vallejo</option>
                                    <option value="GDL" {{ old('ubicacion') == 'GDL' ? 'selected' : '' }}>Guadalajara</option>
                                </select>
                                @error('ubicacion')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Propietario --}}
                            <div>
                                <label for="propietario" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Propietario <span class="text-rose-600">*</span>
                                </label>
                                <input id="propietario" type="text" name="propietario" required
                                       value="{{ old('propietario') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('propietario')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Unidad --}}
                            <div>
                                <label for="unidad" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Unidad <span class="text-rose-600">*</span>
                                </label>
                                <input id="unidad" type="text" name="unidad" required
                                       value="{{ old('unidad') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('unidad')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Serie --}}
                            <div>
                                <label for="serie" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Serie (VIN) <span class="text-rose-600">*</span>
                                </label>
                                <input id="serie" type="text" name="serie" required
                                       value="{{ old('serie') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('serie')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Usa el número de serie completo registrado en la tarjeta.</p>
                            </div>

                            {{-- Marca --}}
                            <div>
                                <label for="marca" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Marca
                                </label>
                                <input id="marca" type="text" name="marca"
                                       value="{{ old('marca') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('marca')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Año --}}
                            <div>
                                <label for="anio" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Año
                                </label>
                                <input id="anio" type="number" name="anio" min="1900" max="{{ date('Y') + 1 }}"
                                       value="{{ old('anio') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('anio')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Motor --}}
                            <div>
                                <label for="motor" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Motor
                                </label>
                                <input id="motor" type="text" name="motor"
                                       value="{{ old('motor') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('motor')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Placa --}}
                            <div>
                                <label for="placa" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Placa
                                </label>
                                <input id="placa" type="text" name="placa"
                                       value="{{ old('placa') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('placa')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Estado --}}
                            <div>
                                <label for="estado" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Estado
                                </label>
                                <input id="estado" type="text" name="estado"
                                       value="{{ old('estado') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('estado')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tarjeta SiVale --}}
                            <div class="md:col-span-2">
                                <label for="tarjeta_si_vale_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Tarjeta SiVale
                                </label>
                                <select id="tarjeta_si_vale_id" name="tarjeta_si_vale_id"
                                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                    <option value="">-- Sin tarjeta asignada --</option>
                                    @foreach($tarjetas as $tarjeta)
                                        <option value="{{ $tarjeta->id }}" {{ old('tarjeta_si_vale_id') == $tarjeta->id ? 'selected' : '' }}>
                                            {{ $tarjeta->numero_tarjeta }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tarjeta_si_vale_id')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Asigna una tarjeta válida en caso de corresponder.</p>
                            </div>

                            {{-- Vencimiento tarjeta de circulación --}}
                            <div>
                                <label for="vencimiento_t_circulacion" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Vencimiento tarjeta de circulación
                                </label>
                                <input id="vencimiento_t_circulacion" type="date" name="vencimiento_t_circulacion"
                                       value="{{ old('vencimiento_t_circulacion') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('vencimiento_t_circulacion')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Cambio de placas --}}
                            <div>
                                <label for="cambio_placas" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Cambio de placas
                                </label>
                                <input id="cambio_placas" type="date" name="cambio_placas"
                                       value="{{ old('cambio_placas') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('cambio_placas')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Póliza HDI --}}
                            <div>
                                <label for="poliza_hdi" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Póliza HDI
                                </label>
                                <input id="poliza_hdi" type="text" name="poliza_hdi"
                                       value="{{ old('poliza_hdi') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('poliza_hdi')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Rendimiento --}}
                            <div>
                                <label for="rend" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Rendimiento (km/l)
                                </label>
                                <input id="rend" type="number" step="0.01" name="rend"
                                       value="{{ old('rend') }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none ring-indigo-300 focus:border-indigo-500 focus:ring dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('rend')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Usa dos decimales. Ej.: 12.50</p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                        <a href="{{ url()->previous() ?: route('vehiculos.index') }}"
                           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            Guardar
                        </button>
                    </div>
                </div>
            </form>

            {{-- Nota para campos de fecha si en BD son VARCHAR --}}
            <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                Nota: si los campos de fecha están almacenados como texto, el selector de fecha enviará el valor en formato <code>YYYY-MM-DD</code>.
                Considera migrarlos a tipo <code>DATE</code> para validaciones y reportes más consistentes.
            </p>
        </div>
    </div>
</x-app-layout>
