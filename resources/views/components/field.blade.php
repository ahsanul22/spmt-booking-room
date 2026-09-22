@props(['name', 'label', 'type' => 'text', 'value' => null])
<p>
    <label for="{{ $name }}">{{ $label }}</label>
    <br>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($type !== 'password') value="{{ $value }}" @endif {{ $attributes }}>
</p>
