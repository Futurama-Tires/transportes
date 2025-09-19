@php use Illuminate\Support\Str; @endphp
@props([
    'name',
    'label' => null,
    'options' => [],   // ['valor' => 'Etiqueta'] o ['Toyota','Nissan',...]
    'id' => null,
    'value' => null,   // valor seleccionado por defecto (si no, request(name))
    'empty' => null,   // ej: 'Todas' / 'Todos' (muestra opción vacía)
])

@php
    $selectId = $id ?? $name;
    $selected = old($name, request()->input($name, $value));
    $labelText = $label ?? Str::headline($name);
@endphp

<div {{ $attributes->merge(['class' => 'col-12']) }}>
    <label class="form-label" for="{{ $selectId }}">{{ $labelText }}</label>
    <select id="{{ $selectId }}" name="{{ $name }}" class="form-select">
        @if(!is_null($empty))
            <option value="">{{ $empty }}</option>
        @endif

        @foreach($options as $key => $text)
            @php
                $optValue = is_int($key) ? $text : $key;
                $optLabel = $text;
            @endphp
            <option value="{{ $optValue }}" @selected((string)$selected === (string)$optValue)>{{ $optLabel }}</option>
        @endforeach
    </select>
</div>
