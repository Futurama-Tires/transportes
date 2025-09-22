<x-app-layout>
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Calendario de Verificación — Reglas</h1>
            <a href="{{ route('calendarios.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded-md">Nueva regla</a>
        </div>

        @if (session('ok'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('ok') }}</div>
        @endif

        <form class="mb-4 grid grid-cols-3 gap-3">
            <input type="text" name="estado" value="{{ request('estado') }}" placeholder="Estado" class="border rounded px-2 py-1">
            <input type="number" name="terminacion" value="{{ request('terminacion') }}" placeholder="Terminación (0-9)" class="border rounded px-2 py-1">
            <button class="px-3 py-2 border rounded">Filtrar</button>
        </form>

        <div class="overflow-x-auto bg-white shadow rounded">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 text-left">Estado</th>
                        <th class="p-2 text-left">Termin.</th>
                        <th class="p-2 text-left">Periodo</th>
                        <th class="p-2 text-left">Semestre</th>
                        <th class="p-2 text-left">Frecuencia</th>
                        <th class="p-2 text-left">Vigencia</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($items as $it)
                    <tr class="border-t">
                        <td class="p-2">{{ $it->estado }}</td>
                        <td class="p-2">{{ $it->terminacion }}</td>
                        <td class="p-2">{{ $it->periodo_label }}</td>
                        <td class="p-2">{{ $it->semestre ?? '—' }}</td>
                        <td class="p-2">{{ $it->frecuencia }}</td>
                        <td class="p-2">
                            {{ $it->vigente_desde?->format('Y-m-d') ?? '—' }} —
                            {{ $it->vigente_hasta?->format('Y-m-d') ?? '—' }}
                        </td>
                        <td class="p-2 text-right">
                            <a href="{{ route('calendarios.edit',$it) }}" class="px-2 py-1 border rounded mr-2">Editar</a>
                            <form action="{{ route('calendarios.destroy',$it) }}" method="post" class="inline"
                                  onsubmit="return confirm('¿Eliminar regla?');">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 border rounded text-red-600">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-4" colspan="7">Sin reglas</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</x-app-layout>
