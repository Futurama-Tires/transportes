@props([
    'nameMin',
    'nameMax',
    'label' => null,
    'placeholderMin' => 'mín',
    'placeholderMax' => 'máx',
    'idMin' => null,
    'idMax' => null,
    'step' => '1',
])

@php
    $valMin = old($nameMin, request()->input($nameMin));
    $valMax = old($nameMax, request()->input($nameMax));
@endphp

<div {{ $attributes }}>
    @if($label)
        <div class="text-secondary text-uppercase fw-semibold small mb-2">{{ $label }}</div>
    @endif

    <div class="row g-2">
        <div class="col-6">
            <input
                id="{{ $idMin ?? $nameMin }}"
                type="number"
                name="{{ $nameMin }}"
                value="{{ $valMin }}"
                step="{{ $step }}"
                class="form-control"
                placeholder="{{ $placeholderMin }}"
            >
        </div>
        <div class="col-6">
            <input
                id="{{ $idMax ?? $nameMax }}"
                type="number"
                name="{{ $nameMax }}"
                value="{{ $valMax }}"
                step="{{ $step }}"
                class="form-control"
                placeholder="{{ $placeholderMax }}"
            >
        </div>
    </div>
</div>
