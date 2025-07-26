<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
            Panel de Administración
        </h2>
        <br>  
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Gestión de Usuarios -->
            <a href="https://www.google.com" class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition duration-300 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Gestión de Usuarios</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Ver, crear y modificar usuarios del sistema.</p>
            </a>

            <!-- Gestión de Vehículos -->
            <a href="https://www.google.com" class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition duration-300 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Gestión de Vehículos</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Control de flota, registros y actualizaciones.</p>
            </a>

            <!-- Gestión de Cargas -->
            <a href="https://www.google.com" class="block bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition duration-300 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Gestión de Cargas</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Registrar y consultar reportes de carga.</p>
            </a>

        </div>
    </div>
</x-app-layout>
