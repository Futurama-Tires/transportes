<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Editar Vehículo
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">
            <form method="POST" action="{{ route('vehiculos.update', $vehiculo) }}">
                @csrf
                @method('PUT')

                @include('vehiculos.partials.form', ['vehiculo' => $vehiculo])

                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
