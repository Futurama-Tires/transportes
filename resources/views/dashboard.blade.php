<x-app-layout>
    {{-- Header --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="min-w-0">
                <nav class="mb-1 text-xs text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2">
                        <li>
                            <a href="{{ route('dashboard') }}"
                               class="hover:text-slate-700 dark:hover:text-slate-200">Inicio</a>
                        </li>
                        <li aria-hidden="true" class="text-slate-400">/</li>
                        <li class="text-slate-700 dark:text-slate-200">Panel</li>
                    </ol>
                </nav>
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18m-7 5h7"/>
                    </svg>
                    <h2 class="truncate text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                        Panel de Administración
                    </h2>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Accesos rápidos y métricas clave del sistema.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Mensajes por rol (discretos) --}}
            <div class="mb-6 grid gap-3">
                @role('administrador')
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-900/30 dark:text-emerald-100">
                        Perfil: Administrador
                    </div>
                @endrole
                @role('capturista')
                    <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm text-indigo-800 dark:border-indigo-900/40 dark:bg-indigo-900/30 dark:text-indigo-100">
                        Perfil: Capturista
                    </div>
                @endrole
                @role('operador')
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/30 dark:text-amber-100">
                        Perfil: Operador
                    </div>
                @endrole
            </div>


            {{-- Grid de accesos --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

                @hasanyrole('administrador|capturista')
                <a href="{{ route('cargas.index') }}"
                   class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm ring-indigo-300 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 dark:border-slate-700 dark:bg-slate-800"
                   aria-label="Gestión de cargas">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                        <span class="material-symbols-outlined"> local_gas_station </span>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-indigo-600 dark:text-slate-100 dark:group-hover:text-indigo-400">
                        Gestión de cargas
                    </h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Registrar y consultar reportes de carga.</p>
                </a>
                @endhasanyrole

                @hasanyrole('administrador|capturista')
                <a href="{{ route('operadores.index') }}"
                   class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm ring-indigo-300 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 dark:border-slate-700 dark:bg-slate-800"
                   aria-label="Operadores">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                        <span class="material-symbols-outlined"> face </span>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-violet-600 dark:text-slate-100 dark:group-hover:text-violet-400">
                        Operadores
                    </h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Gestionar los operadores del sistema.</p>
                </a>
                @endhasanyrole

                @role('administrador')
                <a href="{{ route('capturistas.index') }}"
                   class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm ring-indigo-300 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 dark:border-slate-700 dark:bg-slate-800"
                   aria-label="Capturistas">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                        <span class="material-symbols-outlined"> checkbook </span>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-emerald-600 dark:text-slate-100 dark:group-hover:text-emerald-400">
                        Capturistas
                    </h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Gestionar los capturistas del sistema.</p>
                </a>
                @endrole

                @hasanyrole('administrador|capturista')
                <a href="{{ route('vehiculos.index') }}"
                class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm ring-indigo-300 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 dark:border-slate-700 dark:bg-slate-800"
                aria-label="Gestión de Vehículos">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                        <span class="material-symbols-outlined text-3xl">local_shipping</span>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-amber-600 dark:text-slate-100 dark:group-hover:text-amber-400">
                        Gestión de Vehículos
                    </h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        Control de flota, registros y actualizaciones.
                    </p>
                </a>
                @endhasanyrole


                @hasanyrole('administrador|capturista')
                <a href="{{ route('verificaciones.index') }}"
                   class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm ring-indigo-300 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 dark:border-slate-700 dark:bg-slate-800"
                   aria-label="Verificaciones">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                        <span class="material-symbols-outlined"> garage_check </span>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-rose-600 dark:text-slate-100 dark:group-hover:text-rose-400">
                        Verificaciones
                    </h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Registrar y consultar verificaciones.</p>
                </a>
                @endhasanyrole

                @hasanyrole('administrador|capturista')
                <a href="{{ route('tarjetas.index') }}"
                   class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm ring-indigo-300 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 dark:border-slate-700 dark:bg-slate-800"
                   aria-label="Tarjetas SiVale">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                        <span class="material-symbols-outlined"> credit_card </span>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-indigo-600 dark:text-slate-100 dark:group-hover:text-indigo-400">
                        Tarjetas SiVale
                    </h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Registrar y consultar tarjetas SiVale.</p>
                </a>
                @endhasanyrole

                @role('administrador')
                <a href="https://www.google.com"
                   class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm ring-indigo-300 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 dark:border-slate-700 dark:bg-slate-800"
                   aria-label="Bases de datos">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-300">
                        <span class="material-symbols-outlined"> database </span>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-fuchsia-600 dark:text-slate-100 dark:group-hover:text-fuchsia-400">
                        Bases de datos
                    </h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Respaldo y restauración de la base de datos.</p>
                </a>
                @endrole

                @hasanyrole('administrador|capturista')
                <a href="https://www.google.com"
                   class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm ring-indigo-300 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 dark:border-slate-700 dark:bg-slate-800"
                   aria-label="Reportes y estadísticas">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-700 dark:bg-slate-900/30 dark:text-slate-300">
                        {{-- Icon: Chart Bar --}}
                        <span class="material-symbols-outlined"> query_stats </span>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-slate-700 dark:text-slate-100 dark:group-hover:text-slate-300">
                        Reportes y estadísticas
                    </h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Reportes y estadísticas del sistema.</p>
                </a>
                @endhasanyrole

            </div>
        </div>
    </div>
</x-app-layout>
