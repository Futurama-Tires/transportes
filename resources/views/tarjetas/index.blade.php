{{-- resources/views/tarjetas/index.blade.php --}}
<x-app-layout>
    <style>[x-cloak]{display:none!important}</style>

    {{-- Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                Gestión de Tarjetas SiVale
            </h2>
            <a href="{{ route('tarjetas.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                <span class="material-symbols-outlined"> add_box </span>
                Nueva Tarjeta
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

            {{-- Barra superior: búsqueda + orden + exportaciones --}}
            <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                {{-- Buscador + orden (crece más) --}}
                <form method="GET" action="{{ route('tarjetas.index') }}" class="w-full lg:w-3/4 xl:w-4/5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        {{-- Buscador redondeado --}}
                        <div class="flex w-full sm:flex-1 items-center rounded-full bg-white px-4 py-2 shadow-md ring-1 ring-gray-200 focus-within:ring dark:bg-slate-800 dark:ring-slate-700">
                            <span class="material-symbols-outlined"> search </span>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Buscar por número, NIP o vencimiento…"
                                class="ml-3 w-full flex-1 border-0 bg-transparent text-sm outline-none placeholder:text-gray-400 dark:placeholder:text-slate-400"
                                aria-label="Buscar tarjetas"
                            />
                            @if(request('search'))
                                <a href="{{ route('tarjetas.index') }}"
                                   class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600"
                                   title="Limpiar búsqueda">
                                    Limpiar
                                </a>
                            @endif
                        </div>

                        {{-- Botón Buscar --}}
                        <button type="submit"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <span class="material-symbols-outlined"> search </span>
                            Buscar
                        </button>

                        {{-- Selects de orden --}}
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                            <div class="relative w-full sm:w-48">
                                <select
                                    name="sort_by"
                                    class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    onchange="this.form.submit()"
                                    title="Ordenar por"
                                >
                                    <option value="numero_tarjeta" @selected(request('sort_by','numero_tarjeta')==='numero_tarjeta')>Número de Tarjeta</option>
                                    <option value="fecha_vencimiento" @selected(request('sort_by')==='fecha_vencimiento')>Fecha de Vencimiento</option>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </div>

                            <div class="relative w-full sm:w-36">
                                <select
                                    name="sort_dir"
                                    class="block h-10 w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    onchange="this.form.submit()"
                                    title="Dirección"
                                >
                                    <option value="asc"  @selected(request('sort_dir','asc')==='asc')>Ascendente</option>
                                    <option value="desc" @selected(request('sort_dir')==='desc')>Descendente</option>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Botones de exportación (placeholders) --}}
                <div class="flex flex-wrap items-center gap-2">
                    <a href="#"
                       class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                       title="Exportar a Excel">
                        <span class="material-symbols-outlined"> grid_on </span>
                        Excel
                    </a>

                    <a href="#"
                       class="inline-flex items-center gap-2 rounded-lg border border-rose-300 bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400"
                       title="Exportar a PDF">
                        <span class="material-symbols-outlined"> picture_as_pdf </span>
                        PDF
                    </a>
                </div>
            </div>

            {{-- Resumen (cuando hay búsqueda) --}}
            @if(request('search'))
                <div class="mb-4 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-700 dark:text-slate-100">Filtro</span>
                        <span class="font-medium">“{{ request('search') }}”</span>
                    </div>

                    @php
                        $total = $tarjetas->total();
                        $first = $tarjetas->firstItem();
                        $last  = $tarjetas->lastItem();
                        $current = $tarjetas->currentPage();
                        $lastPage = $tarjetas->lastPage();
                    @endphp

                    <div class="text-sm text-slate-600 dark:text-slate-300">
                        @if($total === 1)
                            Resultado <span class="font-semibold">(1 de 1)</span>
                        @elseif($total > 1)
                            Página <span class="font-semibold">{{ $current }}</span> de <span class="font-semibold">{{ $lastPage }}</span> —
                            Mostrando <span class="font-semibold">{{ $first }}–{{ $last }}</span> de <span class="font-semibold">{{ $total }}</span> resultados
                        @else
                            Sin resultados para la búsqueda.
                        @endif
                    </div>
                </div>
            @endif

            {{-- Tabla --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 dark:bg-slate-900/40 dark:text-slate-300">
                            <tr class="text-xs uppercase tracking-wide">
                                <th scope="col" class="sticky left-0 z-10 border-b border-slate-200 px-4 py-3 font-semibold bg-slate-50 dark:bg-slate-900/40 dark:border-slate-700">
                                    Número de Tarjeta
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">
                                    NIP
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">
                                    Fecha de Vencimiento
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold dark:border-slate-700">
                                    Estado
                                </th>
                                <th scope="col" class="border-b border-slate-200 px-4 py-3 font-semibold text-right dark:border-slate-700">
                                    <span class="sr-only">Acciones</span>Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($tarjetas as $tarjeta)
                                @php
                                    $fv = $tarjeta->fecha_vencimiento ? \Carbon\Carbon::parse($tarjeta->fecha_vencimiento) : null;
                                    $hoy = \Carbon\Carbon::today();
                                    $estado = '—';
                                    $badgeClasses = 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-100';
                                    if ($fv) {
                                        if ($fv->isPast()) {
                                            $estado = 'Vencida';
                                            $badgeClasses = 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-100';
                                        } elseif ($fv->diffInDays($hoy, false) >= -30 && $fv->greaterThanOrEqualTo($hoy)) {
                                            $estado = 'Por vencer';
                                            $badgeClasses = 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-100';
                                        } else {
                                            $estado = 'Vigente';
                                            $badgeClasses = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-100';
                                        }
                                    }
                                    $nip = $tarjeta->nip ?? '—';
                                    $hasNip = $nip !== '—' && $nip !== '';
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-800 dark:text-slate-100 font-mono tracking-wider">
                                        {{ $tarjeta->numero_tarjeta }}
                                    </td>

                                    {{-- NIP + toggle por fila --}}
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        <div class="flex items-center gap-3">
                                            <span class="nip-field font-mono" data-real="{{ $hasNip ? e($nip) : '' }}">
                                                {{ $hasNip ? '••••' : '—' }}
                                            </span>
                                            @if($hasNip)
                                                <button
                                                    type="button"
                                                    class="toggle-nip inline-flex items-center gap-1 text-xs px-2 py-1 rounded border border-slate-300 hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700"
                                                    aria-label="Mostrar NIP" title="Mostrar NIP">
                                                    <span class="material-symbols-outlined text-sm"> visibility </span>
                                                    Mostrar
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Vencimiento --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ $fv ? $fv->format('d/m/Y') : '—' }}
                                    </td>

                                    {{-- Estado con badge --}}
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClasses }}">
                                            {{ $estado }}
                                        </span>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Editar --}}
                                            <a href="{{ route('tarjetas.edit', $tarjeta) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                                               aria-label="Editar tarjeta {{ $tarjeta->id }}" title="Editar">
                                                <span class="material-symbols-outlined"> edit </span>
                                                Editar
                                            </a>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('tarjetas.destroy', $tarjeta) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('¿Seguro que quieres eliminar esta tarjeta?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400"
                                                        aria-label="Eliminar tarjeta {{ $tarjeta->id }}" title="Eliminar">
                                                    <span class="material-symbols-outlined"> delete </span>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-300">
                                        @if(request('search'))
                                            No se encontraron resultados para <span class="font-semibold">“{{ request('search') }}”</span>.
                                            <a href="{{ route('tarjetas.index') }}" class="text-indigo-600 hover:text-indigo-800">Limpiar búsqueda</a>
                                        @else
                                            No hay tarjetas registradas.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación + contador --}}
            @if(method_exists($tarjetas, 'links'))
                <div class="mt-6 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                    @php
                        $totalAll = $tarjetas->total();
                        $firstAll = $tarjetas->firstItem();
                        $lastAll  = $tarjetas->lastItem();
                        $currentAll = $tarjetas->currentPage();
                        $lastPageAll = $tarjetas->lastPage();
                    @endphp

                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        @if($totalAll === 0)
                            Mostrando 0 resultados
                        @elseif($totalAll === 1)
                            Resultado <span class="font-semibold">(1 de 1)</span>
                        @else
                            Página <span class="font-semibold">{{ $currentAll }}</span> de <span class="font-semibold">{{ $lastPageAll }}</span> —
                            Mostrando <span class="font-semibold">{{ $firstAll }}–{{ $lastAll }}</span> de <span class="font-semibold">{{ $totalAll }}</span> resultados
                        @endif
                    </p>

                    <div class="w-full sm:w-auto">
                        {{ $tarjetas->appends([
                            'search'   => request('search'),
                            'sort_by'  => request('sort_by'),
                            'sort_dir' => request('sort_dir'),
                        ])->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <footer class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
        </div>
    </footer>

    {{-- Script: toggle por fila (oculto = siempre "••••") --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                    btn.innerHTML = '<span class="material-symbols-outlined text-sm"> visibility_off </span> Ocultar';
                    btn.setAttribute("aria-label", "Ocultar NIP");
                    btn.setAttribute("title", "Ocultar NIP");
                } else {
                    field.textContent = "••••";
                    btn.innerHTML = '<span class="material-symbols-outlined text-sm"> visibility </span> Mostrar';
                    btn.setAttribute("aria-label", "Mostrar NIP");
                    btn.setAttribute("title", "Mostrar NIP");
                }
            });
        });
    </script>
</x-app-layout>
