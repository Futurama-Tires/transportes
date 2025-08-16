<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de Tarjetas SiVale
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Botón agregar --}}
            <div class="mb-4">
                <a href="{{ route('tarjetas.create') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                   + Nueva Tarjeta
                </a>
            </div>

            {{-- Tabla --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Número de Tarjeta
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    NIP
                                </th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Fecha de Vencimiento
                                </th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($tarjetas as $tarjeta)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 font-mono tracking-wider">
                                        {{ $tarjeta->numero_tarjeta }}
                                    </td>

                                    {{-- NIP + toggle por fila --}}
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        @php
                                            $nip = $tarjeta->nip ?? '—';
                                            $hasNip = $nip !== '—' && $nip !== '';
                                        @endphp
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="nip-field font-mono"
                                                data-real="{{ $hasNip ? e($nip) : '' }}">
                                                {{ $hasNip ? '••••' : '—' }}
                                            </span>

                                            @if($hasNip)
                                                <button
                                                    type="button"
                                                    class="toggle-nip text-xs px-2 py-1 rounded border border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                                                    aria-label="Mostrar NIP">
                                                    Mostrar
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $tarjeta->fecha_vencimiento ? \Carbon\Carbon::parse($tarjeta->fecha_vencimiento)->format('d/m/Y') : '—' }}
                                    </td>

                                    <td class="px-4 py-2 text-right text-sm font-medium">
                                        <a href="{{ route('tarjetas.edit', $tarjeta) }}"
                                           class="text-yellow-500 hover:text-yellow-700">
                                            Editar
                                        </a>
                                        <form action="{{ route('tarjetas.destroy', $tarjeta) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('¿Seguro que quieres eliminar esta tarjeta?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-red-500 hover:text-red-700">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-center text-gray-500 dark:text-gray-300">
                                        No hay tarjetas registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            @if(method_exists($tarjetas, 'links'))
                <div class="mt-4">
                    {{ $tarjetas->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Script: toggle por fila (oculto = siempre "••••") --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Delegación de eventos para manejar cualquier botón "Mostrar/Ocultar"
            document.body.addEventListener("click", function(e) {
                const btn = e.target.closest(".toggle-nip");
                if (!btn) return;

                const container = btn.closest("td");
                const field = container.querySelector(".nip-field");
                const real = field?.dataset.real;

                if (!field || !real) return;

                const isHidden = field.textContent.trim() === "••••";
                if (isHidden) {
                    field.textContent = real;
                    btn.textContent = "Ocultar";
                    btn.setAttribute("aria-label", "Ocultar NIP");
                } else {
                    field.textContent = "••••";
                    btn.textContent = "Mostrar";
                    btn.setAttribute("aria-label", "Mostrar NIP");
                }
            });
        });
    </script>
</x-app-layout>
