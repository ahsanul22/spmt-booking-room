@props(['href', 'title', 'icon' => 'grid', 'badge' => null])
<a href="{{ $href }}" class="group flex items-start gap-4 rounded-xl border border-slate-200/80 p-4 transition hover:border-secondary hover:bg-secondaryLight/10">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-background text-primary"><x-schedule-icon :name="$icon" /></span>
    <span class="min-w-0 flex-1">
        <span class="flex items-center gap-2">
            <span class="block text-base font-semibold text-primaryDark">{{ $title }}</span>
            @if($badge)
                <span class="inline-flex items-center justify-center rounded-full bg-danger px-2 py-0.5 text-xs font-bold text-white shadow-sm">{{ $badge }}</span>
            @endif
        </span>
        <span class="mt-1 block text-sm leading-5 text-slate-600">{{ $slot }}</span>
    </span>
    <x-schedule-icon name="arrow" class="mt-3 h-4 w-4 shrink-0 text-primary transition group-hover:translate-x-1" />
</a>
