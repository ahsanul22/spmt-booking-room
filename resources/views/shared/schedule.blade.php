@extends('layouts.schedule')
@section('content')
<div data-schedule>
    <div class="mb-7 flex flex-wrap items-center justify-between gap-5">
        <div><p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-primary">Plan your collaboration</p><h1 class="text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">Jadwal Ruangan</h1><p class="mt-3 text-sm text-slate-500">Temukan waktu yang tepat untuk ide besar berikutnya.</p></div>
        @can('access-employee')
            <a href="{{ route('rooms.search') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primaryDark"><x-schedule-icon name="plus" />Cari Ruangan</a>
        @else
            <a href="{{ route('admin.rooms.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primaryDark"><x-schedule-icon name="room" />Kelola Ruangan</a>
        @endcan
    </div>
    <div class="mb-6 flex items-center gap-3 rounded-xl border border-secondaryLight/70 bg-secondaryLight/20 px-4 py-3 text-xs text-primaryDark" role="note"><span class="h-2 w-2 shrink-0 rounded-full bg-primary"></span><p><strong>Pratinjau desain</strong> · Data booking belum terhubung. Kalender ini belum menunjukkan ketersediaan ruangan.</p></div>
    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
        <section aria-label="Kalender ruangan" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-5 sm:p-6">
                <div class="flex items-center gap-3"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-background text-primary"><x-schedule-icon /></span><div><h2 data-month-label aria-live="polite" class="text-lg font-bold text-primaryDark">Kalender bulanan</h2><p class="mt-0.5 text-xs text-slate-500">Atur waktu, bangun kolaborasi</p></div></div>
                <div class="flex items-center gap-2"><button data-today type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold hover:border-primary hover:text-primary">Hari Ini</button><div class="flex rounded-lg border border-slate-200"><button data-prev type="button" class="p-2 hover:bg-background" aria-label="Bulan sebelumnya"><x-schedule-icon name="arrow" class="h-4 w-4 rotate-180" /></button><button data-next type="button" class="border-l border-slate-200 p-2 hover:bg-background" aria-label="Bulan berikutnya"><x-schedule-icon name="arrow" class="h-4 w-4" /></button></div></div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4"><span class="inline-flex items-center gap-2 text-xs font-medium text-slate-600"><x-schedule-icon name="room" class="h-4 w-4" />Semua ruangan</span><span class="rounded-md bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary">Bulanan</span></div>
            <div class="overflow-x-auto"><div class="min-w-[490px]"><div class="grid grid-cols-7 border-b border-slate-100 bg-background/50 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-500">@foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)<div class="py-3">{{ $day }}</div>@endforeach</div><div data-calendar class="grid grid-cols-7"></div></div></div>
            <noscript><p class="p-6 text-sm">Aktifkan JavaScript untuk melihat kalender dan memilih tanggal.</p></noscript>
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 text-[11px] text-slate-500"><span>Klik tanggal untuk melihat agenda</span><div class="flex items-center gap-4"><span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-primary"></span>Hari ini</span><span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-primary bg-secondaryLight/30"></span>Tanggal dipilih</span></div></div>
        </section>
        <aside class="space-y-5">
            <section class="rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm" aria-label="Agenda tanggal dipilih">
                <div class="flex items-center justify-between"><h2 class="font-bold text-primaryDark">Agenda Ruangan</h2><x-schedule-icon name="clock" class="h-5 w-5 text-primary" /></div><p data-selected-label class="mt-2 text-xs text-slate-500" aria-live="polite">Pilih tanggal pada kalender</p>
                <div class="mt-5 border-t border-slate-100 py-10 text-center"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-background text-secondary"><x-schedule-icon class="h-8 w-8" /></div><h3 class="mt-5 text-sm font-semibold text-primaryDark">Belum ada jadwal ruangan.</h3><p class="mx-auto mt-2 max-w-[210px] text-xs leading-6 text-slate-500">Agenda rapat akan muncul di sini setelah data booking terhubung.</p></div>
                <div class="rounded-lg bg-background px-3 py-3 text-center text-[11px] leading-5 text-slate-500">Tanggal kosong belum berarti ruangan tersedia.</div>
            </section>
            <section class="relative overflow-hidden rounded-2xl bg-primaryDark p-6 text-white"><div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full border-[20px] border-secondary/10"></div><span class="relative inline-flex rounded-lg bg-white/10 p-2.5 text-secondaryLight"><x-schedule-icon name="room" /></span><h2 class="relative mt-4 text-lg font-semibold">Ruang yang tepat.<br>Diskusi lebih produktif.</h2><p class="relative mt-3 text-xs leading-6 text-secondaryLight">Kenali kapasitas dan fasilitas ruangan sebelum merencanakan pertemuan.</p><a href="{{ route(auth()->user()->can('access-admin') ? 'admin.rooms.index' : 'rooms.index') }}" class="relative mt-5 inline-flex items-center gap-2 text-xs font-semibold text-white hover:underline">Jelajahi ruangan<x-schedule-icon name="arrow" class="h-3 w-3" /></a></section>
        </aside>
    </div>
</div>
@endsection
