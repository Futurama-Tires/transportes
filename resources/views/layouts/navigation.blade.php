{{-- resources/views/layouts/navigation.blade.php --}}
@php
    // Clases base para links de navegación principales (desktop)
    $linkBase = 'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition';
    $linkOff  = 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 dark:text-gray-300 dark:hover:text-white';
    $linkOn   = 'border-blue-600 text-blue-700 dark:text-blue-400';

    // Items del submenú "Gestión"
    $gestionLinks = [
        ['type' => 'route', 'value' => 'vehiculos.index',     'label' => 'Vehículos'],
        ['type' => 'route', 'value' => 'operadores.index',    'label' => 'Operadores'],
        ['type' => 'route', 'value' => 'capturistas.index',   'label' => 'Capturistas'],
        ['type' => 'route', 'value' => 'tarjetas.index',      'label' => 'Tarjetas Si Vale'],
        ['type' => 'route', 'value' => 'verificaciones.index','label' => 'Verificaciones'],
        ['type' => 'url',   'value' => '#',                   'label' => 'Bases de datos'],
        ['type' => 'url',   'value' => '#',                   'label' => 'Reportes y estadísticas'],
    ];
@endphp

<nav x-data="{ mobileOpen:false }"
     class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50"
     aria-label="Barra de navegación principal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- IZQUIERDA: Logo + links --}}
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
                        <img src="{{ asset('images/logoOriginal.png') }}" alt="Futurama Tires" class="h-16 w-auto">
                    </a>
                </div>

                {{-- Links principales --}}
                <div class="hidden sm:-my-px sm:ml-8 sm:flex sm:items-center sm:space-x-6">
                    <a href="{{ route('dashboard') }}"
                       class="{{ request()->routeIs('dashboard') ? "$linkBase $linkOn" : "$linkBase $linkOff" }}">
                        Dashboard
                    </a>

                    {{-- Dropdown Gestión --}}
                    @role('administrador|capturista')
                    <div class="relative" x-data="{ open:false }">
                        <button @click="open=!open"
                                :aria-expanded="open"
                                class="inline-flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white px-2 py-1">
                            Gestión
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-cloak x-show="open" @click.away="open=false"
                             class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black/5">
                            <div class="py-1">
                                @foreach($gestionLinks as $item)
                                    @php
                                        // Ocultar Capturistas y Bases de datos si es capturista
                                        if(auth()->user()->hasRole('capturista') &&
                                           in_array($item['label'], ['Capturistas','Bases de datos'])) {
                                            continue;
                                        }
                                        $href = $item['type'] === 'route' ? route($item['value']) : $item['value'];
                                    @endphp
                                    <a href="{{ $href }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endrole
                </div>
            </div>

            {{-- DERECHA: menú usuario --}}
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <div class="relative" x-data="{ open:false }">
                    <button @click="open=!open"
                            class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white px-2 py-1">
                        {{ Auth::user()->name ?? 'Usuario' }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-cloak x-show="open" @click.away="open=false"
                         class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black/5">
                        <div class="py-1">
                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                                Mi perfil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-700">
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hamburguesa (mobile) --}}
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="mobileOpen = !mobileOpen"
                        class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': mobileOpen, 'inline-flex': !mobileOpen }"
                              class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{ 'hidden': !mobileOpen, 'inline-flex': mobileOpen }"
                              class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menú mobile --}}
    <div x-cloak x-show="mobileOpen" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('dashboard') }}"
               class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('dashboard') ? 'text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200' }}">
                Dashboard
            </a>

            @role('administrador|capturista')
            <div x-data="{ open:false }" class="border-t border-gray-200 dark:border-gray-700">
                <button @click="open = !open" class="w-full text-left pl-3 pr-4 py-2 text-base font-medium text-gray-700 dark:text-gray-200">
                    Gestión
                </button>
                <div x-cloak x-show="open">
                    @foreach($gestionLinks as $item)
                        @php
                            if(auth()->user()->hasRole('capturista') &&
                               in_array($item['label'], ['Capturistas','Bases de datos'])) {
                                continue;
                            }
                            $href = $item['type'] === 'route' ? route($item['value']) : $item['value'];
                        @endphp
                        <a href="{{ $href }}" class="block pl-6 pr-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endrole
        </div>

        <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-700">
            <div class="px-4 text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ Auth::user()->name ?? 'Usuario' }}
            </div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
                    Mi perfil
                </a>
                @role('administrador')
                <a href="{{ route('dashboard.admin') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
                    Dashboard Admin
                </a>
                @endrole
                <form method="POST" action="{{ route('logout') }}" class="px-4">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-red-600 dark:text-red-400">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
