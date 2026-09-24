@props(['title', 'description'])
<div class="mb-7 flex flex-wrap items-center justify-between gap-5">
    <div class="min-w-0">
        <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-primary">Administrasi Workspace</p>
        <h1 class="text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">{{ $title }}</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">{{ $description }}</p>
    </div>
    {{ $slot }}
</div>
