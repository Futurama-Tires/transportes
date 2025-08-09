<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Editar Operador
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto">
            <form method="POST" action="{{ route('operadores.update', $operador->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $operador->nombre) }}"
                           class="w-full border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $operador->apellido_paterno) }}"
                           class="w-full border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Apellido Materno</label>
                    <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $operador->apellido_materno) }}"
                           class="w-full border-gray-300 rounded">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300">Correo</label>
                    <input type="email" name="email" value="{{ old('email', $operador->user->email) }}"
                           class="w-full border-gray-300 rounded" required>
                </div>

                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
