{{-- resources/views/vehiculos/index.blade.php --}}
<x-app-layout>
    {{-- Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                Gestión de Vehículos
            </h2>
            <a href="{{ route('vehiculos.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                {{-- plus icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                </svg>
                Agregar Vehículo
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Flash éxito --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900/40 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Barra superior: búsqueda --}}
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form method="GET" action="{{ route('vehiculos.index') }}" class="w-full sm:w-auto">
                    <div class="flex w-full items-center rounded-full border border-slate-300 bg-white px-3 py-2 shadow-sm ring-indigo-300 focus-within:ring dark:border-slate-700 dark:bg-slate-800">
                        {{-- search icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Buscar por placa, marca, unidad, propietario…"
                            class="w-full bg-transparent text-sm placeholder-slate-400 focus:outline-none dark:text-slate-100"
                            aria-label="Buscar vehículos"
                        />
                        @if(request('search'))
                            <a href="{{ route('vehiculos.index') }}"
                               class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600"
                               title="Limpiar búsqueda">
                                Limpiar
                            </a>
                        @endif
                        <button type="submit"
                                class="ml-2 inline-flex items-center rounded-full bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="overflow-x-auto">
                    {{-- Ancho mínimo amplio para que no se amontonen las columnas; conserva scroll horizontal --}}
                    <table class="min-w-[1700px] text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 dark:bg-slate-900/40 dark:text-slate-300">
                            <tr class="text-xs uppercase tracking-wide">
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">ID</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Ubicación</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Propietario</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Unidad</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Marca</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Año</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Serie</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Motor</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Placa</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Estado</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Fec. Venc.</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Venc. T. Circulación</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Cambio Placas</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Póliza HDI</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Rend</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">Tarjeta Si Vale ID</th>
                                <th class="border-b border-slate-200 px-4 py-3 font-semibold text-right dark:border-slate-700">
                                    <span class="sr-only">Acciones</span>Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($vehiculos as $vehiculo)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-800 dark:text-slate-100">{{ $vehiculo->id }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->ubicacion ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->propietario ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->unidad ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->marca ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->anio ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->serie ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->motor ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->placa ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->estado ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->fec_vencimiento ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->vencimiento_t_circulacion ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->cambio_placas ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">{{ $vehiculo->poliza_hdi ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ !is_null($vehiculo->rend) ? number_format($vehiculo->rend, 2) : '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ optional($vehiculo->tarjetaSiVale)->id ?? ($vehiculo->tarjeta_si_vale_id ?? '—') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Editar --}}
                                            <a href="{{ route('vehiculos.edit', $vehiculo) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                                               aria-label="Editar vehículo #{{ $vehiculo->id }}">
                                                {{-- pencil icon --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232 18.768 8.768M4 20l4.586-1.146a2 2 0 0 0 .894-.514l9.94-9.94a2 2 0 0 0 0-2.828l-1.792-1.792a2 2 0 0 0-2.828 0l-9.94 9.94a2 2 0 0 0-.514.894L4 20z"/>
                                                </svg>
                                                Editar
                                            </a>

                                            {{-- Eliminar --}}
                                            @role('administrador')
                                                <form action="{{ route('vehiculos.destroy', $vehiculo) }}"
                                                      method="POST"
                                                      class="inline"
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar este vehículo?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400"
                                                            aria-label="Eliminar vehículo #{{ $vehiculo->id }}">
                                                        {{-- trash icon --}}
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-1-3H10a1 1 0 0 0-1 1v2h8V5a1 1 0 0 0-1-1z"/>
                                                        </svg>
                                                        Eliminar
                                                    </button>
                                                </form>
                                            @endrole
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="px-4 py-8 text-center text-slate-500 dark:text-slate-300">
                                        @if(request('search'))
                                            No se encontraron resultados para
                                            <span class="font-semibold">“{{ request('search') }}”</span>.
                                            <a href="{{ route('vehiculos.index') }}" class="text-indigo-600 hover:text-indigo-800">
                                                Limpiar búsqueda
                                            </a>
                                        @else
                                            No hay vehículos registrados.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            @if(method_exists($vehiculos, 'links'))
                <div class="mt-6 flex justify-center">
                    {{ $vehiculos->appends(['search' => request('search')])->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
