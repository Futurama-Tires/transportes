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
                        {{-- Icon: Clipboard Document --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-5 4h4m-7 8h8a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1.5a2 2 0 0 0-2-2h-1a2 2 0 0 0-2 2H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/>
                        </svg>
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
                        {{-- Icon: Users --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14a4 4 0 1 0-8 0m8 0v3a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2v-3m8 0a4 4 0 1 1-8 0M12 7a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                        </svg>
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
                        {{-- Icon: Pencil Square --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 3.487 20.513 7.14a2 2 0 0 1 0 2.829l-8.94 8.94A2 2 0 0 1 10.68 19H7a1 1 0 0 1-1-1v-3.68a2 2 0 0 1 .586-1.414l8.94-8.94a2 2 0 0 1 2.829 0z"/>
                        </svg>
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
                        {{-- Icon: Truck --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16V6a2 2 0 0 1 2-2h9v12m-2 0h7a2 2 0 0 0 2-2v-3h-5l-2-3H12M5 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm12 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-amber-600 dark:text-slate-100 dark:group-hover:text-amber-400">
                        Gestión de Vehículos
                    </h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Control de flota, registros y actualizaciones.</p>
                </a>
                @endhasanyrole

                @hasanyrole('administrador|capturista')
                <a href="{{ route('verificaciones.index') }}"
                   class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm ring-indigo-300 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 dark:border-slate-700 dark:bg-slate-800"
                   aria-label="Verificaciones">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                        {{-- Icon: Check Badge --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4m5 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
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
                        {{-- Icon: Credit Card --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="5" width="18" height="14" rx="2" ry="2" />
                            <path d="M3 10h18"/>
                        </svg>
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
                        {{-- Icon: Server Stack --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <ellipse cx="12" cy="5" rx="9" ry="3" />
                            <path d="M3 5v6a9 3 0 0 0 18 0V5M3 11v6a9 3 0 0 0 18 0v-6"/>
                        </svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m5 14V9m5 10V7m5 12V3"/>
                        </svg>
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
