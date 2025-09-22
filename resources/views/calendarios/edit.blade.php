<x-app-layout>
    <div class="max-w-2xl mx-auto p-6">
        <h1 class="text-xl font-semibold mb-4">Editar regla</h1>
        <form method="post" action="{{ route('calendarios.update',$calendario) }}" class="space-y-3">
            @csrf @method('PUT')
            @include('calendarios.form-fields', ['model'=>$calendario])
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Actualizar</button>
        </form>
    </div>
</x-app-layout>
