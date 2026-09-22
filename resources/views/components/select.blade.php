@props(['name', 'label'])
<p>
    <label for="{{ $name }}">{{ $label }}</label>
    <br>
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes }}>{{ $slot }}</select>
</p>
