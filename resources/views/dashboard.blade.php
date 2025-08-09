<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p>{{ __("You're logged in!") }}</p>

                    @role('administrador')
                        <p class="mt-4 text-green-500">Tienes rol de administrador</p>
                    @endrole

                    @role('capturista')
                        <p class="mt-4 text-blue-500">Bienvenido capturista</p>
                    @endrole

                    @role('operador')
                        <p class="mt-4 text-yellow-500">Modo operador activado</p>
                    @endrole
                </div>

            </div>
        </div>
    </div>

    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
            Panel de Administración
        </h2>
        <br>  
    </x-slot>
    

    <div div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Gestión de Cargas (Operador y Capturista) -->
            @hasanyrole('operador|capturista')
            <a href="https://www.google.com" class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition duration-300 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Gestión de cargas</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Registrar y consultar reportes de carga.</p>
            </a>
            @endhasanyrole

            <!-- Gestión de Operadores (Administrador y Capturista) -->
            @hasanyrole('administrador|capturista')
            <a href="{{ route('operadores.index') }}"  
            class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition duration-300 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Operadores</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                    Gestionar los operadores del sistema.
                </p>
            </a>
            @endhasanyrole


            <!-- Capturistas (Solo Administrador) -->
            @role('administrador')
            <a href="{{ route('capturistas.index') }}" 
            class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition duration-300 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Capturistas</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                    Gestionar los capturistas del sistema.
                </p>
            </a>
            @endrole

            <!-- Gestión de Vehículos -->
            @hasanyrole('administrador|capturista')
            <a href="{{ route('vehiculos.index') }}" 
            class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition duration-300 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Gestión de Vehículos</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                    Control de flota, registros y actualizaciones.
                </p>
            </a>
            @endhasanyrole

            <!-- Verificaciones (Administrador) -->
            @hasanyrole('administrador|capturista')
            <a href="https://www.google.com" class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition duration-300 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Verificaciones</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Registrar y consultar verificaciones.</p>
            </a>
            @endhasanyrole

            <!-- Bases de datos (Solo Administrador) -->
            @role('administrador')
            <a href="https://www.google.com" class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition duration-300 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Bases de datos</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Respaldo y restauración de la base de datos.</p>
            </a>
            @endrole

            <!-- Reportes (Administrador y Capturista) -->
            @hasanyrole('administrador|capturista')
            <a href="https://www.google.com" class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition duration-300 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Reportes y estadísticas</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Reportes y estadísticas sobre la información del sistema.</p>
            </a>
            @endhasanyrole


        </div>
    </div>
</x-app-layout>
