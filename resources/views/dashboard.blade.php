<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7h18M3 12h18m-7 5h7"/>
            </svg>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200">
                Panel de Administración
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Mensajes según el rol --}}
            <div class="mb-6">
                @role('administrador')
                    <div class="p-4 bg-green-100 text-green-800 rounded-lg">Tienes rol de Administrador</div>
                @endrole
                @role('capturista')
                    <div class="p-4 bg-blue-100 text-blue-800 rounded-lg">Bienvenido Capturista</div>
                @endrole
                @role('operador')
                    <div class="p-4 bg-yellow-100 text-yellow-800 rounded-lg">Modo Operador activado</div>
                @endrole
            </div>

            {{-- Grid de opciones --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                @hasanyrole('operador|capturista')
                <a href="https://www.google.com"
                   class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-center h-12 w-12 bg-blue-100 rounded-full mb-4">
                        📦
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-500">
                        Gestión de cargas
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Registrar y consultar reportes de carga.</p>
                </a>
                @endhasanyrole

                @hasanyrole('administrador|capturista')
                <a href="{{ route('operadores.index') }}"
                   class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-center h-12 w-12 bg-purple-100 rounded-full mb-4">
                        👨‍🔧
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-purple-500">
                        Operadores
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Gestionar los operadores del sistema.</p>
                </a>
                @endhasanyrole

                @role('administrador')
                <a href="{{ route('capturistas.index') }}"
                   class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-center h-12 w-12 bg-green-100 rounded-full mb-4">
                        📝
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-green-500">
                        Capturistas
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Gestionar los capturistas del sistema.</p>
                </a>
                @endrole

                @hasanyrole('administrador|capturista')
                <a href="{{ route('vehiculos.index') }}"
                   class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-center h-12 w-12 bg-yellow-100 rounded-full mb-4">
                        🚚
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-yellow-500">
                        Gestión de Vehículos
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Control de flota, registros y actualizaciones.</p>
                </a>
                @endhasanyrole

                @hasanyrole('administrador|capturista')
                <a href="{{ route('verificaciones.index') }}"
                   class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-center h-12 w-12 bg-red-100 rounded-full mb-4">
                        ✅
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-red-500">
                        Verificaciones
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Registrar y consultar verificaciones.</p>
                </a>
                @endhasanyrole

                @hasanyrole('administrador|capturista')
                <a href="{{ route('tarjetas.index') }}"
                   class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-center h-12 w-12 bg-indigo-100 rounded-full mb-4">
                        💳
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-indigo-500">
                        Tarjetas SiVale
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Registrar y consultar tarjetas SiVale.</p>
                </a>
                @endhasanyrole

                @role('administrador')
                <a href="https://www.google.com"
                   class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-center h-12 w-12 bg-pink-100 rounded-full mb-4">
                        🗄️
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-pink-500">
                        Bases de datos
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Respaldo y restauración de la base de datos.</p>
                </a>
                @endrole

                @hasanyrole('administrador|capturista')
                <a href="https://www.google.com"
                   class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-center h-12 w-12 bg-gray-100 rounded-full mb-4">
                        📊
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-gray-500">
                        Reportes y estadísticas
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Reportes y estadísticas sobre la información del sistema.</p>
                </a>
                @endhasanyrole

            </div>
        </div>
    </div>
</x-app-layout>
