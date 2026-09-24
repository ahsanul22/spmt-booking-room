@props(['title', 'description' => null])
<section {{ $attributes->class(['min-w-0 overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm']) }}>
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:px-6">
        <div><h2 class="text-lg font-bold text-primaryDark">{{ $title }}</h2>@if($description)<p class="mt-1 text-xs leading-5 text-slate-600">{{ $description }}</p>@endif</div>
        {{ $headingActions ?? '' }}
    </div>
    {{ $slot }}
</section>
