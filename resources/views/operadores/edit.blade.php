<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Operadores</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
                    Editar Operador
                </h2>
            </div>
            <a href="{{ route('operadores.index') }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900/40 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900/40 dark:bg-rose-900/30 dark:text-rose-100">
                    Revisa los campos marcados y vuelve a intentar.
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Datos del operador</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Actualiza la información básica del operador.</p>
                </div>

                <form method="POST" action="{{ route('operadores.update', $operador->id) }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="grid gap-6 px-6 py-6 md:grid-cols-2">
                        {{-- Nombre --}}
                        <div>
                            <x-input-label for="nombre" value="Nombre *" />
                            <x-text-input id="nombre"
                                          name="nombre"
                                          type="text"
                                          class="mt-1 block w-full"
                                          :value="old('nombre', $operador->nombre)"
                                          required
                                          autocomplete="given-name" />
                            <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                        </div>

                        {{-- Apellido paterno --}}
                        <div>
                            <x-input-label for="apellido_paterno" value="Apellido paterno *" />
                            <x-text-input id="apellido_paterno"
                                          name="apellido_paterno"
                                          type="text"
                                          class="mt-1 block w-full"
                                          :value="old('apellido_paterno', $operador->apellido_paterno)"
                                          required
                                          autocomplete="family-name" />
                            <x-input-error class="mt-2" :messages="$errors->get('apellido_paterno')" />
                        </div>

                        {{-- Apellido materno --}}
                        <div>
                            <x-input-label for="apellido_materno" value="Apellido materno" />
                            <x-text-input id="apellido_materno"
                                          name="apellido_materno"
                                          type="text"
                                          class="mt-1 block w-full"
                                          :value="old('apellido_materno', $operador->apellido_materno)"
                                          autocomplete="additional-name" />
                            <x-input-error class="mt-2" :messages="$errors->get('apellido_materno')" />
                        </div>

                        {{-- Correo --}}
                        <div>
                            <x-input-label for="email" value="Correo electrónico *" />
                            <x-text-input id="email"
                                          name="email"
                                          type="email"
                                          class="mt-1 block w-full"
                                          :value="old('email', $operador->user->email)"
                                          required
                                          autocomplete="email"
                                          aria-describedby="email_help" />
                            <p id="email_help" class="mt-1 text-xs text-slate-500 dark:text-slate-400">Usa un correo válido y único.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                        @if (class_exists(\Laravel\Jetstream\Jetstream::class))
                            {{-- Botones Jetstream/Breeze (si existen) --}}
                            <x-secondary-button onclick="window.location='{{ url()->previous() ?: route('operadores.index') }}'">
                                Cancelar
                            </x-secondary-button>
                            <x-primary-button>
                                Guardar cambios
                            </x-primary-button>
                        @else
                            {{-- Fallback simple si no tienes esos componentes --}}
                            <a href="{{ url()->previous() ?: route('operadores.index') }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2 text-sm font-semibold rounded-md bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Guardar cambios
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
