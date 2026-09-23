@props(['href', 'title', 'icon' => 'grid'])
<a href="{{ $href }}" class="group flex items-start gap-4 rounded-xl border border-slate-200/80 p-4 transition hover:border-secondary hover:bg-secondaryLight/10">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-background text-primary"><x-schedule-icon :name="$icon" /></span>
    <span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-primaryDark">{{ $title }}</span><span class="mt-1 block text-xs leading-5 text-slate-600">{{ $slot }}</span></span>
    <x-schedule-icon name="arrow" class="mt-3 h-4 w-4 shrink-0 text-primary transition group-hover:translate-x-1" />
</a>
