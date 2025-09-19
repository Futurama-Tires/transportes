@php use Illuminate\Support\Str; @endphp
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'id' => null,
    'value' => null,
    'placeholder' => null,
])

@php
    $inputId = $id ?? $name;
    $val = old($name, request()->input($name, $value));
    $labelText = $label ?? Str::headline($name);
@endphp

<div {{ $attributes->merge(['class' => 'col-12']) }}>
    <label class="form-label" for="{{ $inputId }}">{{ $labelText }}</label>
    <input
        id="{{ $inputId }}"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $val }}"
        class="form-control"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
    >
</div>
