@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])
<div class="min-w-0">
    <label for="{{ $name }}" class="mb-2 block text-sm font-semibold text-primaryDark">{{ $label }}@if($attributes->get('required')) <span aria-hidden="true" class="text-danger">*</span>@endif</label>
    @php
        $controlAttributes = $attributes->class(['workspace-control', 'border-danger' => $errors->has($name)])
            ->merge(['aria-invalid' => $errors->has($name) ? 'true' : 'false']);
        $descriptionIds = trim(($hint ? $name.'-hint ' : '').($errors->has($name) ? $name.'-error' : ''));
    @endphp
    @if($type === 'select')
        <select id="{{ $name }}" name="{{ $name }}" @if($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif {{ $controlAttributes }}>{{ $slot }}</select>
    @elseif($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="5" @if($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif {{ $controlAttributes }}>{{ $value }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($type !== 'password') value="{{ $value }}" @endif @if($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif {{ $controlAttributes }}>
    @endif
    @if($hint)<p id="{{ $name }}-hint" class="mt-2 text-xs leading-5 text-slate-600">{{ $hint }}</p>@endif
    @error($name)<p id="{{ $name }}-error" class="mt-2 text-sm text-danger">{{ $message }}</p>@enderror
</div>
