<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanques</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                    Editar tanque — Vehículo {{ $vehiculo->unidad ?? '#'.$vehiculo->id }}
                </h2>
            </div>
            <a href="{{ route('vehiculos.tanques.index', $vehiculo) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('vehiculos.tanques.update', [$vehiculo, $tanque]) }}" novalidate>
                @csrf @method('PUT')

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-rose-800 ring-1 ring-rose-200">
                        Revisa los campos marcados y vuelve a intentar.
                    </div>
                @endif

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Datos del tanque</h3>
                    </div>

                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="numero_tanque" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Número de tanque</label>
                                <input id="numero_tanque" type="number" name="numero_tanque" min="1" max="255"
                                       value="{{ old('numero_tanque', $tanque->numero_tanque) }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('numero_tanque') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="tipo_combustible" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Tipo de combustible <span class="text-rose-600">*</span></label>
                                <select id="tipo_combustible" name="tipo_combustible" required
                                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                    <option value="">-- Selecciona --</option>
                                    @foreach(['Magna','Diesel','Premium'] as $c)
                                        <option value="{{ $c }}" @selected(old('tipo_combustible', $tanque->tipo_combustible) === $c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_combustible') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="capacidad_litros" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Capacidad (L) <span class="text-rose-600">*</span></label>
                                <input id="capacidad_litros" type="number" step="0.01" name="capacidad_litros" required
                                       value="{{ old('capacidad_litros', $tanque->capacidad_litros) }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('capacidad_litros') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="rendimiento_estimado" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Rendimiento estimado (km/L)</label>
                                <input id="rendimiento_estimado" type="number" step="0.01" name="rendimiento_estimado"
                                       value="{{ old('rendimiento_estimado', $tanque->rendimiento_estimado) }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('rendimiento_estimado') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="costo_tanque_lleno" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Costo tanque lleno</label>
                                <input id="costo_tanque_lleno" type="number" step="0.01" name="costo_tanque_lleno"
                                       value="{{ old('costo_tanque_lleno', $tanque->costo_tanque_lleno) }}"
                                       class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                @error('costo_tanque_lleno') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">Km que recorre (capacidad × rendimiento)</label>
                                <input type="text" value="{{ number_format((old('capacidad_litros',$tanque->capacidad_litros ?? 0) * old('rendimiento_estimado',$tanque->rendimiento_estimado ?? 0)), 2) }}"
                                       class="block w-full cursor-not-allowed rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300" readonly>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Se recalcula al guardar.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                        <a href="{{ route('vehiculos.tanques.index', $vehiculo) }}"
                           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">Cancelar</a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">Guardar cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
