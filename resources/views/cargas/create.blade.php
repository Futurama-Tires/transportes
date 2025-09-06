<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
            Nueva Carga de Combustible
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('cargas.store') }}" class="space-y-4">
                        @csrf
                        @include('cargas._form')
                        <div class="flex items-center gap-3">
                            <a href="{{ route('cargas.index') }}" class="px-4 py-2 rounded-lg bg-slate-200">Cancelar</a>
                            <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Guardar</button>
                        </div>
                    </form>
                    @if ($errors->any())
                        <ul class="mt-4 text-red-600 text-sm list-disc pl-5">
                            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
