@props([
    'id' => 'filtersOffcanvas',
    'title' => 'Filtros',
    'clearUrl' => null,         // URL para "Limpiar filtros"
    'backdrop' => true,         // true|false => data-bs-backdrop
    'scroll' => false,          // true|false => data-bs-scroll
    'submitLabel' => 'Aplicar filtros',
    'closeLabel' => 'Cerrar',
])

<div
    {{ $attributes->merge(['class' => 'offcanvas offcanvas-end']) }}
    tabindex="-1"
    id="{{ $id }}"
    aria-labelledby="{{ $id }}Label"
    data-bs-backdrop="{{ $backdrop ? 'true' : 'false' }}"
    data-bs-scroll="{{ $scroll ? 'true' : 'false' }}"
>
    <div class="offcanvas-header">
        <h2 class="offcanvas-title h4" id="{{ $id }}Label">
            <i class="ti ti-adjustments me-2" aria-hidden="true"></i>{{ $title }}
        </h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>

    <div class="offcanvas-body">
        {{-- Preferencia: slots nombrados. Si no hay, usa el slot por defecto --}}
        @isset($filters)
            {{ $filters }}
        @endisset

        @isset($order)
            {{ $order }}
        @endisset

        @empty($filters)
            @empty($order)
                {{ $slot }}
            @endempty
        @endempty
    </div>

    <div class="offcanvas-footer d-flex justify-content-between align-items-center p-3 border-top">
        @if($clearUrl)
            <a href="{{ $clearUrl }}" class="btn btn-link">Limpiar filtros</a>
        @else
            <span></span>
        @endif

        <div>
            <button type="button" class="btn btn-outline-dark me-2" data-bs-dismiss="offcanvas">{{ $closeLabel }}</button>
            <button type="submit" class="btn btn-danger">
                <i class="ti ti-filter me-1" aria-hidden="true"></i>{{ $submitLabel }}
            </button>
        </div>
    </div>
</div>
