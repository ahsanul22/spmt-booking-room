@php
    $stats = ! empty($stats) ? $stats : [
        ['label' => 'Menunggu approval', 'icon' => 'clock', 'value' => 0, 'description' => 'Pengajuan perlu ditinjau'],
        ['label' => 'Ruangan tanggung jawab', 'icon' => 'room', 'value' => 0, 'description' => 'Ruangan ditugaskan'],
        ['label' => 'Jadwal hari ini', 'icon' => 'calendar', 'value' => 0, 'description' => 'Penggunaan hari ini'],
    ];
@endphp

<div role="note" class="mb-6 flex items-start gap-3 rounded-xl border border-secondaryLight/70 bg-secondaryLight/20 px-4 py-3 text-sm leading-5 text-primaryDark">
    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary"></span>
    <p><strong>Dashboard PIC</strong> · Buka Permintaan Approval untuk meninjau dan memutuskan pengajuan ruangan Anda.</p>
</div>

<section aria-label="Ringkasan tugas PIC" class="mb-6 grid gap-4 sm:grid-cols-3">
    @foreach($stats as $stat)
        <div class="rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-base font-medium text-slate-600">{{ $stat['label'] }}</h2>
                <span class="rounded-lg bg-secondaryLight/20 p-2 text-primary"><x-schedule-icon :name="$stat['icon']" /></span>
            </div>
            <p class="mt-4 text-3xl font-bold text-primaryDark">{{ $stat['value'] }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ $stat['description'] }}</p>
        </div>
    @endforeach
</section>

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
    <div class="min-w-0 space-y-6">
        <section aria-labelledby="activity-title" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:px-6">
                <h2 id="activity-title" class="text-lg font-bold text-primaryDark">Menunggu Tinjauan Anda</h2>
                <span class="rounded-md bg-background px-3 py-1 text-sm font-medium text-slate-600">Approval Ruangan</span>
            </div>
            <div class="px-6 py-10 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-background text-primary"><x-schedule-icon name="clock" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-base font-semibold text-primaryDark">Tinjau permintaan approval</h3>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-600">Permintaan untuk ruangan yang ditugaskan kepada Anda tersedia di halaman Permintaan Approval.</p>
                <a href="{{ route('pic.approvals.index') }}" class="mt-5 inline-flex items-center gap-2 text-base font-semibold text-primary hover:underline">Buka permintaan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            </div>
        </section>
    </div>

    <aside class="min-w-0 space-y-5">
        @include('shared.dashboard-account')
    </aside>
</div>
