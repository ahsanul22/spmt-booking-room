@props(['title', 'description', 'icon' => 'room'])
<div class="px-5 py-14 text-center">
    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-background text-primary"><x-schedule-icon :name="$icon" class="h-7 w-7" /></span>
    <h3 class="mt-5 text-base font-semibold text-primaryDark">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">{{ $description }}</p>
    {{ $slot }}
</div>
