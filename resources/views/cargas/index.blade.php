<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                Gestión de Cargas de Combustible
            </h2>
            <a href="{{ route('cargas.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                + Nueva Carga
            </a>
        </div>
    </x-slot>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
          <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
              {{ session('success') }}
          </div>
        @endif

        {{-- Filtros básicos --}}
        <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <select name="ubicacion" class="rounded-lg border-gray-300">
                <option value="">Ubicación (todas)</option>
                @foreach($ubicaciones as $u)
                    <option value="{{ $u }}" @selected(request('ubicacion')===$u)>{{ $u }}</option>
                @endforeach
            </select>
            <select name="tipo" class="rounded-lg border-gray-300">
                <option value="">Tipo (todos)</option>
                @foreach(['Magna','Diesel','Premium'] as $t)
                    <option value="{{ $t }}" @selected(request('tipo')===$t)>{{ $t }}</option>
                @endforeach
            </select>
            <input type="text" name="mes" value="{{ request('mes') }}" placeholder="Mes (Enero, Febrero...)"
                   class="rounded-lg border-gray-300">
            <button class="rounded-lg bg-slate-800 text-white px-4">Filtrar</button>
        </form>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-4 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-slate-50 dark:bg-slate-900/40">
                <tr class="text-left text-slate-600 dark:text-slate-300">
                  <th class="px-3 py-2">Fecha</th>
                  <th class="px-3 py-2">Mes</th>
                  <th class="px-3 py-2">Ubicación</th>
                  <th class="px-3 py-2">Tipo</th>
                  <th class="px-3 py-2">Precio</th>
                  <th class="px-3 py-2">Litros</th>
                  <th class="px-3 py-2">Total</th>
                  <th class="px-3 py-2">Operador</th>
                  <th class="px-3 py-2">Unidad</th>
                  <th class="px-3 py-2">Placa</th>
                  <th class="px-3 py-2">KM Ini</th>
                  <th class="px-3 py-2">KM Fin</th>
                  <th class="px-3 py-2">Recorrido</th>
                  <th class="px-3 py-2">Rend</th>
                  <th class="px-3 py-2">Dif $</th>
                  <th class="px-3 py-2">Destino</th>
                  <th class="px-3 py-2">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($cargas as $c)
                  <tr class="text-slate-800 dark:text-slate-100">
                    <td class="px-3 py-2">{{ optional($c->fecha)->format('Y-m-d') }}</td>
                    <td class="px-3 py-2">{{ $c->mes }}</td>
                    <td class="px-3 py-2">{{ $c->ubicacion ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $c->tipo_combustible }}</td>
                    <td class="px-3 py-2">${{ number_format($c->precio, 2) }}</td>
                    <td class="px-3 py-2">{{ number_format($c->litros, 3) }}</td>
                    <td class="px-3 py-2 font-semibold">${{ number_format($c->total, 2) }}</td>
                    <td class="px-3 py-2">
                        {{ $c->operador?->nombre }} {{ $c->operador?->apellido_paterno }}
                    </td>
                    <td class="px-3 py-2">{{ $c->vehiculo?->unidad }}</td>
                    <td class="px-3 py-2">{{ $c->vehiculo?->placa ?? $c->vehiculo?->placas }}</td>
                    <td class="px-3 py-2">{{ $c->km_inicial ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $c->km_final ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $c->recorrido ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $c->rendimiento ?? '—' }}</td>
                    <td class="px-3 py-2">{{ isset($c->diferencia) ? '$'.number_format($c->diferencia,2) : '—' }}</td>
                    <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($c->destino, 20) }}</td>
                    <td class="px-3 py-2">
                      <div class="flex items-center gap-2">
                        <a class="rounded-md bg-yellow-500 text-white px-2 py-1 text-xs hover:bg-yellow-600"
                           href="{{ route('cargas.edit', $c) }}">Editar</a>
                        <form method="POST" action="{{ route('cargas.destroy', $c->id) }}"
      onsubmit="return confirm('¿Eliminar esta carga?')">
  @csrf
  @method('DELETE')
  <button class="rounded-md bg-red-600 text-white px-2 py-1 text-xs hover:bg-red-700">
    Eliminar
  </button>
</form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr><td class="px-3 py-6 text-center text-slate-500" colspan="17">Sin registros</td></tr>
                @endforelse
              </tbody>
            </table>

            <div class="mt-4">
                {{ $cargas->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
</x-app-layout>
