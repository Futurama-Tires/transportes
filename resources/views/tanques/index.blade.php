{{-- resources/views/tanques/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between relative z-[60]">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanque</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                    Vehículo: {{ $vehiculo->unidad ?? 's/u' }} ({{ $vehiculo->placa ?? 's/p' }})
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vehiculos.index') }}"
                   class="relative z-[60] inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    Volver a Vehículos
                </a>

                {{-- Botón para abrir modal de precios de combustible --}}
                <button type="button" id="btn-open-precios"
                        class="relative z-[60] inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    Precios de combustible
                </button>

                @if(!$tanque)
                    <a href="{{ route('vehiculos.tanques.create', $vehiculo) }}"
                       class="relative z-[60] inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                        Agregar tanque
                    </a>
                @else
                    <a href="{{ route('vehiculos.tanques.edit', [$vehiculo, $tanque]) }}"
                       class="relative z-[60] inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
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

    {{-- ===== Modal: Precios de Combustible ===== --}}
    {{-- Cerrado: hidden + pointer-events-none para NO bloquear clics del header --}}
    <div id="modal-precios" class="fixed inset-0 z-[70] hidden pointer-events-none">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        <!-- Panel agrandado -->
        <div class="relative mx-auto mt-24 w-full max-w-3xl rounded-2xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Precios de combustible</h3>
                    <p class="mt-1 text-base text-slate-600 dark:text-slate-400">MXN por litro (Magna, Premium, Diesel)</p>
                </div>
                <button type="button" id="btn-close-precios"
                        class="rounded-md p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700"
                        aria-label="Cerrar">
                    ✕
                </button>
            </div>

            <div class="mt-6 space-y-5">
                {{-- Campo Magna --}}
                <div class="grid grid-cols-4 items-center gap-4">
                    <label for="precio-magna" class="text-base font-medium text-slate-700 dark:text-slate-200">
                        Magna
                    </label>
                    <div class="col-span-3">
                        <input id="precio-magna" type="number" step="0.001" min="0" inputmode="decimal"
                               class="w-full h-12 rounded-xl border border-slate-300 bg-white px-4 py-3 text-lg text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100" />
                    </div>
                </div>

                {{-- Campo Premium --}}
                <div class="grid grid-cols-4 items-center gap-4">
                    <label for="precio-premium" class="text-base font-medium text-slate-700 dark:text-slate-200">
                        Premium
                    </label>
                    <div class="col-span-3">
                        <input id="precio-premium" type="number" step="0.001" min="0" inputmode="decimal"
                               class="w-full h-12 rounded-xl border border-slate-300 bg-white px-4 py-3 text-lg text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100" />
                    </div>
                </div>

                {{-- Campo Diesel --}}
                <div class="grid grid-cols-4 items-center gap-4">
                    <label for="precio-diesel" class="text-base font-medium text-slate-700 dark:text-slate-200">
                        Diesel
                    </label>
                    <div class="col-span-3">
                        <input id="precio-diesel" type="number" step="0.001" min="0" inputmode="decimal"
                               class="w-full h-12 rounded-xl border border-slate-300 bg-white px-4 py-3 text-lg text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100" />
                    </div>
                </div>

                {{-- Recalcular --}}
                <label class="mt-2 inline-flex items-center gap-3 text-base text-slate-700 dark:text-slate-300 select-none">
                    <input id="chk-recalcular" type="checkbox"
                           class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800" />
                    Recalcular <span class="font-semibold">costo_tanque_lleno</span> de todos los tanques
                </label>

                <div id="precios-msg" class="hidden rounded-xl border px-4 py-3 text-base"></div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-3">
                <button type="button" id="btn-cancel-precios"
                        class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-base font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    Cancelar
                </button>
                <button type="button" id="btn-save-precios"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-base font-semibold text-white shadow hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="spin-precios" class="hidden animate-spin">⏳</span>
                    Guardar
                </button>
            </div>
        </div>
    </div>

    {{-- ===== Toast ===== --}}
    <div id="toast" class="fixed bottom-6 left-1/2 z-[75] hidden -translate-x-1/2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-800 shadow-lg dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"></div>

    {{-- Script inline (espera DOM listo) --}}
    <script>
    window.addEventListener('DOMContentLoaded', function () {
        const modal      = document.getElementById('modal-precios');
        const btnOpen    = document.getElementById('btn-open-precios');
        const btnClose   = document.getElementById('btn-close-precios');
        const btnCancel  = document.getElementById('btn-cancel-precios');
        const btnSave    = document.getElementById('btn-save-precios');
        const spin       = document.getElementById('spin-precios');
        const msg        = document.getElementById('precios-msg');
        const toast      = document.getElementById('toast');

        const inputMagna   = document.getElementById('precio-magna');
        const inputPremium = document.getElementById('precio-premium');
        const inputDiesel  = document.getElementById('precio-diesel');
        const chkRecalc    = document.getElementById('chk-recalcular');

        const ROUTE_CURRENT = "{{ route('precios-combustible.current') }}";
        const ROUTE_BULK    = "{{ route('precios-combustible.bulk') }}";

        // CSRF desde meta o servidor (fallback)
        const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')) || "{{ csrf_token() }}";

        function openModal() {
            modal.classList.remove('hidden', 'pointer-events-none');
            document.body.classList.add('overflow-hidden');
            msg.className = 'hidden';
            btnSave.disabled = false;
            spin.classList.add('hidden');
            loadPrecios();
        }
        function closeModal() {
            modal.classList.add('hidden', 'pointer-events-none');
            document.body.classList.remove('overflow-hidden');
        }

        function showToast(text, ok = true) {
            toast.textContent = text;
            toast.className = 'fixed bottom-6 left-1/2 z-[75] -translate-x-1/2 rounded-lg px-4 py-2 text-sm shadow-lg ' +
                (ok
                    ? 'border border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/40 dark:text-green-100'
                    : 'border border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-800 dark:bg-rose-900/40 dark:text-rose-100');
            toast.classList.remove('hidden');
            setTimeout(()=> toast.classList.add('hidden'), 2500);
        }
        function showMsg(text, ok = true) {
            msg.textContent = text;
            msg.className = 'mt-2 rounded-xl border px-4 py-3 text-base ' +
                (ok
                    ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-100'
                    : 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-100');
        }

        async function loadPrecios() {
            try {
                btnSave.disabled = true;
                spin.classList.remove('hidden');

                const res  = await fetch(ROUTE_CURRENT, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
                const data = await res.json();

                const map = {};
                (data?.data || []).forEach(x => { map[(x.combustible || '').toLowerCase()] = x.precio_por_litro; });

                inputMagna.value   = (map['magna']   ?? '').toString();
                inputPremium.value = (map['premium'] ?? '').toString();
                inputDiesel.value  = (map['diesel']  ?? '').toString();

                btnSave.disabled = false;
                spin.classList.add('hidden');
            } catch {
                btnSave.disabled = false;
                spin.classList.add('hidden');
                showMsg('No se pudieron cargar los precios actuales.', false);
            }
        }

        function sanitizeNumber(v) {
            if (typeof v !== 'string') v = String(v ?? '');
            v = v.replace(',', '.').trim();
            const num = Number(v);
            return isFinite(num) ? num : NaN;
        }

        async function savePrecios() {
            const magna   = sanitizeNumber(inputMagna.value);
            const premium = sanitizeNumber(inputPremium.value);
            const diesel  = sanitizeNumber(inputDiesel.value);

            if (isNaN(magna) || isNaN(premium) || isNaN(diesel)) {
                showMsg('Verifica que todos los precios sean números válidos (usa punto decimal).', false);
                return;
            }

            const payload = {
                items: [
                    { combustible: 'Magna',   precio_por_litro: Number(magna.toFixed(3)) },
                    { combustible: 'Premium', precio_por_litro: Number(premium.toFixed(3)) },
                    { combustible: 'Diesel',  precio_por_litro: Number(diesel.toFixed(3)) },
                ],
                recalcular_tanques: chkRecalc.checked ? true : false
            };

            try {
                btnSave.disabled = true;
                spin.classList.remove('hidden');

                const res = await fetch(ROUTE_BULK, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const out = await res.json();

                btnSave.disabled = false;
                spin.classList.add('hidden');

                if (!res.ok || !out?.ok) {
                    showMsg('No se pudo guardar. Revisa los datos e inténtalo de nuevo.', false);
                    return;
                }

                showToast('Precios actualizados correctamente.', true);

                if (chkRecalc.checked) {
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    closeModal();
                }
            } catch {
                btnSave.disabled = false;
                spin.classList.add('hidden');
                showMsg('Ocurrió un error al guardar.', false);
            }
        }

        // Listeners
        btnOpen?.addEventListener('click', openModal);
        btnClose?.addEventListener('click', closeModal);
        btnCancel?.addEventListener('click', closeModal);
        btnSave?.addEventListener('click', savePrecios);

        // Cerrar con ESC
        document.addEventListener('keydown', (ev) => {
            if (!modal.classList.contains('hidden') && ev.key === 'Escape') closeModal();
        });
        // Cerrar si clic fuera del panel
        modal.addEventListener('click', (ev) => {
            if (ev.target === modal) closeModal();
        });
    });
    </script>
</x-app-layout>
