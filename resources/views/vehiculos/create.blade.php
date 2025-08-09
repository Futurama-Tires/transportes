<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Agregar Vehículo
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">
            <form method="POST" action="{{ route('vehiculos.store') }}">
                @csrf

                @include('vehiculos.partials.form', ['vehiculo' => null])

                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
