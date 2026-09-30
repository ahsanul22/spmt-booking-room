@php
    $stats = ! empty($stats) ? $stats : [
        ['label' => 'Menunggu approval', 'icon' => 'clock', 'value' => 0, 'description' => 'Pengajuan perlu ditinjau'],
        ['label' => 'Ruangan tanggung jawab', 'icon' => 'room', 'value' => 0, 'description' => 'Ruangan ditugaskan'],
        ['label' => 'Jadwal hari ini', 'icon' => 'calendar', 'value' => 0, 'description' => 'Penggunaan hari ini'],
    ];
@endphp

<div role="note" class="mb-6 flex items-start gap-3 rounded-xl border border-secondaryLight/70 bg-secondaryLight/20 px-4 py-3 text-xs leading-5 text-primaryDark">
    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary"></span>
    <p><strong>Pratinjau dashboard PIC</strong> · Buka Permintaan Approval untuk meninjau dan memutuskan pengajuan ruangan Anda.</p>
</div>

<section aria-label="Ringkasan tugas PIC" class="mb-6 grid gap-4 sm:grid-cols-3">
    @foreach($stats as $stat)
        <div class="rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</h2>
                <span class="rounded-lg bg-secondaryLight/20 p-2 text-primary"><x-schedule-icon :name="$stat['icon']" /></span>
            </div>
            <p class="mt-4 text-3xl font-bold text-primaryDark">{{ $stat['value'] }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ $stat['description'] }}</p>
        </div>
    @endforeach
</section>

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
    <div class="min-w-0 space-y-6">
        <section aria-labelledby="management-title" class="rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm sm:p-6">
            <h2 id="management-title" class="text-lg font-bold text-primaryDark">Kelola Pengajuan dan Ruangan</h2>
            <p class="mt-1 text-xs leading-5 text-slate-600">Akses cepat ke peninjauan approval dan ruangan tanggung jawab Anda.</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <x-dashboard-shortcut :href="route('pic.approvals.index')" title="Permintaan Approval" icon="clock">Periksa dan berikan keputusan pengajuan ruangan Anda.</x-dashboard-shortcut>
                <x-dashboard-shortcut :href="route('pic.rooms.index')" title="Ruangan Saya" icon="room">Lihat informasi dan jadwal ruangan yang ditugaskan kepada Anda.</x-dashboard-shortcut>
                <x-dashboard-shortcut :href="route('pic.approvals.history')" title="Riwayat Approval">Lihat keputusan persetujuan dan penolakan sebelumnya.</x-dashboard-shortcut>
                <x-dashboard-shortcut :href="route('schedule.index')" title="Jadwal Ruangan" icon="calendar">Lihat jam penggunaan dan booking ruangan.</x-dashboard-shortcut>
            </div>
        </section>

        <section aria-labelledby="activity-title" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:px-6">
                <h2 id="activity-title" class="text-lg font-bold text-primaryDark">Menunggu Tinjauan Anda</h2>
                <span class="rounded-md bg-background px-3 py-1 text-xs font-medium text-slate-600">Pratinjau</span>
            </div>
            <div class="px-6 py-10 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-background text-primary"><x-schedule-icon name="clock" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-sm font-semibold text-primaryDark">Tinjau permintaan approval</h3>
                <p class="mx-auto mt-2 max-w-sm text-xs leading-6 text-slate-600">Permintaan untuk ruangan yang ditugaskan kepada Anda tersedia di halaman Permintaan Approval.</p>
                <a href="{{ route('pic.approvals.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">Buka permintaan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            </div>
        </section>
    </div>

    <aside class="min-w-0 space-y-5">
        <section class="relative overflow-hidden rounded-2xl bg-primaryDark p-6 text-white" aria-labelledby="welcome-title">
            <div aria-hidden="true" class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full border-[20px] border-secondary/10"></div>
            <span class="relative inline-flex rounded-xl bg-white/10 p-3 text-secondaryLight"><x-schedule-icon name="clock" class="h-6 w-6" /></span>
            <h2 id="welcome-title" class="relative mt-5 text-xl font-semibold leading-7">Tinjau cepat.<br>Persetujuan tepat.</h2>
            <p class="relative mt-3 text-xs leading-6 text-secondaryLight">Sebagai PIC, periksa agenda, pemohon, dan ketersediaan waktu sebelum memberikan keputusan persetujuan.</p>
            <a href="{{ route('pic.approvals.index') }}" class="relative mt-5 inline-flex items-center gap-2 text-sm font-semibold hover:underline">Tinjau permintaan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
        </section>

        <section aria-labelledby="review-guide-title" class="rounded-2xl border border-secondaryLight bg-secondaryLight/20 p-6">
            <h2 id="review-guide-title" class="font-bold text-primaryDark">Saat meninjau pengajuan</h2>
            <ol class="mt-4 list-decimal space-y-3 pl-4 text-sm leading-6 text-slate-600">
                <li>Periksa ruangan, pemohon, dan agenda rapat.</li>
                <li>Tinjau tanggal, waktu, serta jadwal penggunaan.</li>
                <li>Berikan keputusan sesuai pengajuan. Sertakan alasan jika menolak.</li>
            </ol>
            <p class="mt-4 border-t border-secondaryLight pt-4 text-xs leading-5 text-primaryDark">Kewenangan PIC mengikuti penugasan ruangan, bukan seluruh ruangan atau unit kerja.</p>
        </section>

        @include('shared.dashboard-account')
    </aside>
</div>
