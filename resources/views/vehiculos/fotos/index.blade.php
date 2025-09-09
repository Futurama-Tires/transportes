{{-- resources/views/vehiculos/fotos/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Vehículos</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                    Fotos del vehículo: {{ $vehiculo->unidad ?? "#{$vehiculo->id}" }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Placa: {{ $vehiculo->placa ?? '—' }} · Serie: {{ $vehiculo->serie ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vehiculos.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                    Volver al listado
                </a>
                <a href="{{ route('vehiculos.edit', $vehiculo) }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700">
                    Editar vehículo
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            {{-- Flash --}}
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-emerald-100 px-4 py-3 text-emerald-800 ring-1 ring-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Subir fotos --}}
            <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Subir nuevas fotos</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Formatos: JPG, JPEG, PNG, WEBP. Máx. 8MB por imagen.</p>
                </div>
                <form method="POST" action="{{ route('vehiculos.fotos.store', $vehiculo) }}" enctype="multipart/form-data" class="px-6 py-5">
                    @csrf
                    <input type="file" name="fotos[]" accept="image/*" multiple
                           class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                    @error('fotos')      <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    @error('fotos.*')    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

                    <div class="mt-4 flex items-center justify-end">
                        <button type="submit"
                                class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                            Subir
                        </button>
                    </div>
                </form>
            </div>

            {{-- Galería --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Fotos actuales ({{ $vehiculo->fotos->count() }})</h3>
                </div>

                @if($vehiculo->fotos->isEmpty())
                    <p class="px-6 py-10 text-center text-slate-500">Aún no hay fotos para este vehículo.</p>
                @else
                    <div class="grid grid-cols-2 gap-4 p-6 sm:grid-cols-3 md:grid-cols-4">
                        @foreach($vehiculo->fotos as $foto)
                            <div class="group relative rounded-xl border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <a href="{{ route('vehiculos.fotos.show', $foto) }}" target="_blank" title="Ver en tamaño completo">
                                    <img src="{{ route('vehiculos.fotos.show', $foto) }}"
                                         class="h-40 w-full rounded-lg object-cover transition-all group-hover:opacity-90"
                                         alt="Foto del vehículo">
                                </a>

                                <form method="POST" action="{{ route('vehiculos.fotos.destroy', [$vehiculo, $foto]) }}"
                                      onsubmit="return confirm('¿Eliminar esta foto?')" class="absolute right-2 top-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-full bg-rose-600/90 px-2 py-1 text-xs font-medium text-white shadow hover:bg-rose-700">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                Las fotos se almacenan de forma privada y se sirven bajo autenticación. No se exponen URLs públicas.
            </p>
        </div>
    </div>
</x-app-layout>
