<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
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
</x-app-layout>
