<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanque</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                    Vehículo #{{ $vehiculo->id }} — {{ $vehiculo->unidad ?? 's/u' }} ({{ $vehiculo->placa ?? 's/p' }})
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vehiculos.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    Volver a Vehículos
                </a>
                @if(!$tanque)
                    <a href="{{ route('vehiculos.tanques.create', $vehiculo) }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                        Agregar tanque
                    </a>
                @else
                    <a href="{{ route('vehiculos.tanques.edit', [$vehiculo, $tanque]) }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                        Editar tanque
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800 ring-1 ring-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-[900px] w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Cantidad de tanques</th>
                            <th class="px-4 py-3">Tipo combustible</th>
                            <th class="px-4 py-3">Capacidad (L)</th>
                            <th class="px-4 py-3">Rend. (km/L)</th>
                            <th class="px-4 py-3">Km recorre</th>
                            <th class="px-4 py-3">Costo tanque lleno</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-sm dark:divide-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        @if($tanque)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="px-4 py-3 font-medium">{{ $tanque->cantidad_tanques ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $tanque->tipo_combustible ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $tanque->capacidad_litros !== null ? number_format($tanque->capacidad_litros, 2) : '—' }}</td>
                                <td class="px-4 py-3">{{ $tanque->rendimiento_estimado !== null ? number_format($tanque->rendimiento_estimado, 2) : '—' }}</td>
                                <td class="px-4 py-3">{{ $tanque->km_recorre !== null ? number_format($tanque->km_recorre, 2) : '—' }}</td>
                                <td class="px-4 py-3">
                                    {{ $tanque->costo_tanque_lleno !== null ? ('$'.number_format($tanque->costo_tanque_lleno,2)) : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('vehiculos.tanques.edit', [$vehiculo, $tanque]) }}"
                                           class="inline-flex items-center rounded-full bg-indigo-600 px-2.5 py-1 text-xs font-medium text-white shadow hover:bg-indigo-700">
                                            Editar
                                        </a>
                                        <form action="{{ route('vehiculos.tanques.destroy', [$vehiculo, $tanque]) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Eliminar el tanque de este vehículo?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-full bg-rose-600 px-2.5 py-1 text-xs font-medium text-white shadow hover:bg-rose-700">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-slate-500">
                                    Este vehículo no tiene tanque. 
                                    <a class="text-indigo-600 hover:underline" href="{{ route('vehiculos.tanques.create', $vehiculo) }}">Crear ahora</a>.
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

                {{-- ya no hay paginación (1:1) --}}
            </div>
        </div>
    </div>
</x-app-layout>
