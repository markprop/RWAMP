@props([
    'name' => 'phone',
    'value' => null,
    'required' => false,
    'id' => null,
    'placeholder' => 'Enter phone number',
    'class' => '',
    'inputClass' => '',
])

@php
    $inputId = $id ?? $name;
    $baseClasses = trim('phone-input ' . ($inputClass ?: $class));
@endphp

<div class="phone-input-wrapper">
    <input
        type="tel"
        class="{{ $baseClasses }}"
        id="{{ $inputId }}"
        autocomplete="tel"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        {{ $attributes->except(['class', 'inputClass', 'placeholder', 'required', 'id', 'name', 'value']) }}
    />
    <input 
        type="hidden" 
        name="{{ $name }}" 
        class="phone-hidden" 
        data-phone-hidden
        value="{{ old($name, $value) }}"
    />
</div>
