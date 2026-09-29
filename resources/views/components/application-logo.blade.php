@props([
    'variant' => 'dark',
    'class' => 'h-8 sm:h-9 w-auto object-contain',
    'alt' => 'Pelindo Multi Terminal',
])

@php
    $src = $variant === 'light'
        ? asset('images/pelindo-logo.png')
        : asset('images/pelindo-logo-white.png');
@endphp

<img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => $class]) }}>
