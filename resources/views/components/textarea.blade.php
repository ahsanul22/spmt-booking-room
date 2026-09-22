@props(['name', 'label', 'value' => null])
<p>
    <label for="{{ $name }}">{{ $label }}</label>
    <br>
    <textarea id="{{ $name }}" name="{{ $name }}" {{ $attributes }}>{{ $value }}</textarea>
</p>
