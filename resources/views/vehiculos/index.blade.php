{{-- resources/views/vehiculos/index.blade.php --}}
<x-app-layout>
    {{-- ======= Header ======= --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Vehículos
        </h2>
    </x-slot>

    @php
        // ======= Clases reutilizables (evita repetir cadenas largas) =======
        $th = 'px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase';
        $td = 'px-4 py-2 text-sm text-gray-700 dark:text-gray-300';
        $tableBase = 'min-w-[1700px] whitespace-nowrap divide-y divide-gray-200 dark:divide-gray-700';
        // Columna de Acciones fija a la derecha (sticky) para que no se pierda al hacer scroll
        $thActions = $th . ' text-right sticky right-0 z-20 bg-gray-50 dark:bg-gray-700';
        $tdActions = 'px-4 py-2 text-right text-sm font-medium sticky right-0 z-10 bg-white dark:bg-gray-800 whitespace-nowrap border-l border-gray-200 dark:border-gray-700';
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ======= Mensajes de éxito ======= --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ======= Botón crear ======= --}}
            <div class="mb-4">
                <a href="{{ route('vehiculos.create') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Agregar Vehículo
                </a>
            </div>

            {{-- ======= Tabla con scroll horizontal ======= --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 overflow-x-auto">
                    <table class="{{ $tableBase }}">
                        <caption class="sr-only">Listado de vehículos</caption>

                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="{{ $th }}">ID</th>
                                <th scope="col" class="{{ $th }}">Ubicación</th>
                                <th scope="col" class="{{ $th }}">Propietario</th>
                                <th scope="col" class="{{ $th }}">Unidad</th>
                                <th scope="col" class="{{ $th }}">Marca</th>
                                <th scope="col" class="{{ $th }}">Año</th>
                                <th scope="col" class="{{ $th }}">Serie</th>
                                <th scope="col" class="{{ $th }}">Motor</th>
                                <th scope="col" class="{{ $th }}">Placa</th>
                                <th scope="col" class="{{ $th }}">Estado</th>
                                <th scope="col" class="{{ $th }}">Fec. Venc.</th>
                                <th scope="col" class="{{ $th }}">Venc. T. Circulación</th>
                                <th scope="col" class="{{ $th }}">Cambio Placas</th>
                                <th scope="col" class="{{ $th }}">Póliza HDI</th>
                                <th scope="col" class="{{ $th }}">Rend</th>
                                <th scope="col" class="{{ $th }}">Tarjeta Si Vale ID</th>
                                <th scope="col" class="{{ $thActions }}">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($vehiculos as $vehiculo)
                                <tr>
                                    <td class="{{ $td }}">{{ $vehiculo->id }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->ubicacion ?? '—' }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->propietario ?? '—' }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->unidad ?? '—' }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->marca ?? '—' }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->anio ?? '—' }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->serie ?? '—' }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->motor ?? '—' }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->placa ?? '—' }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->estado ?? '—' }}</td>

                                    {{-- Estos tres campos son strings en DB; si luego los migras a date, se podrían formatear --}}
                                    <td class="{{ $td }}">{{ $vehiculo->fec_vencimiento ?? '—' }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->vencimiento_t_circulacion ?? '—' }}</td>
                                    <td class="{{ $td }}">{{ $vehiculo->cambio_placas ?? '—' }}</td>

                                    <td class="{{ $td }}">{{ $vehiculo->poliza_hdi ?? '—' }}</td>
                                    <td class="{{ $td }}">
                                        {{ !is_null($vehiculo->rend) ? number_format($vehiculo->rend, 2) : '—' }}
                                    </td>
                                    <td class="{{ $td }}">
                                        {{ $vehiculo->tarjeta_si_vale_id ?? '—' }}
                                    </td>

                                    {{-- Acciones (Editar / Eliminar) --}}
                                    <td class="{{ $tdActions }}">
                                        <a href="{{ route('vehiculos.edit', $vehiculo) }}"
                                           class="text-yellow-500 hover:text-yellow-700">Editar</a>

                                        @role('administrador')
                                            <form action="{{ route('vehiculos.destroy', $vehiculo) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('¿Seguro que deseas eliminar este vehículo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ml-2 text-red-500 hover:text-red-700">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endrole
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- 16 columnas de datos + 1 de acciones = 17 --}}
                                    <td colspan="17" class="px-4 py-2 text-center text-gray-500 dark:text-gray-300">
                                        No hay vehículos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ======= Paginación ======= --}}
            <div class="mt-4">
                {{ $vehiculos->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
