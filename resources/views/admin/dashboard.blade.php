@extends('layouts.schedule')

@section('title', $title)
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="mb-7 flex flex-wrap items-center justify-between gap-5">
        <div>
            <p class="mb-2 text-sm font-bold uppercase tracking-[0.2em] text-primary">Administrasi Workspace</p>
            <h1 class="text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">{{ $title }}</h1>
            <p class="mt-3 text-base leading-6 text-slate-600">Kelola ruang dan dukung kolaborasi dari satu tempat.</p>
        </div>
        <a href="{{ route('admin.rooms.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primaryDark"><x-schedule-icon name="plus" />Tambah Ruangan</a>
    </div>

    @if(session('status'))
        <div role="status" class="mb-6 rounded-xl border border-secondaryLight bg-surface p-4 text-base text-primaryDark">{{ session('status') }}</div>
    @endif

<section aria-label="Ringkasan booking" class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach($stats as $stat)
            <div class="rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3"><h2 class="text-base font-medium text-slate-600">{{ $stat['label'] }}</h2><span class="rounded-lg bg-secondaryLight/20 p-2 text-primary"><x-schedule-icon :name="$stat['icon']" /></span></div>
                <p class="mt-4 text-3xl font-semibold text-primaryDark">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Berdasarkan data booking saat ini</p>
            </div>
        @endforeach
    </section>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
        <div class="min-w-0 space-y-6">
            <section aria-labelledby="management-title" class="rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm sm:p-6">
                <h2 id="management-title" class="text-lg font-bold text-primaryDark">Kelola Workspace</h2>
                <p class="mt-1 text-sm leading-5 text-slate-600">Akses cepat ke data dan pengaturan ruang rapat.</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <x-dashboard-shortcut :href="route('admin.rooms.index')" title="Kelola Ruangan" icon="room">Status, PIC, dan akses ruangan.</x-dashboard-shortcut>
                    <x-dashboard-shortcut :href="route('admin.users.index')" title="Kelola User">Akun pegawai, role, dan status akses.</x-dashboard-shortcut>
                    <x-dashboard-shortcut :href="route('admin.organizational-units.index')" title="Unit Organisasi">Susunan direktorat, divisi, dan departemen.</x-dashboard-shortcut>
                    <x-dashboard-shortcut :href="route('admin.facilities.index')" title="Kelola Fasilitas">Perlengkapan pendukung ruang rapat.</x-dashboard-shortcut>
                    <x-dashboard-shortcut :href="route('admin.schedule.index')" title="Jadwal Ruangan" icon="calendar">Lihat jadwal penggunaan ruang rapat.</x-dashboard-shortcut>
                </div>
            </section>

            <section aria-labelledby="activity-title" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:px-6">
                    <h2 id="activity-title" class="text-lg font-bold text-primaryDark">Aktivitas Booking</h2>
                    <a href="{{ route('admin.reports.index') }}" class="font-semibold text-primary hover:underline">Laporan Bulanan</a>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($recentBookings as $booking)
                        <li class="flex flex-wrap items-center justify-between gap-3 p-5">
                            <div class="min-w-0"><a class="break-words font-semibold text-primary hover:underline" href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->agenda }}</a><p class="mt-1 break-words text-sm text-slate-600">{{ $booking->room->name }} · {{ $booking->date }} · {{ $booking->applicant->name }}</p></div>
                            <x-booking-status :status="$booking->status" />
                        </li>
                    @empty
                        <li><x-workspace-empty title="Belum ada booking." description="Pengajuan yang masuk akan muncul di sini." /></li>
                    @endforelse
                </ul>
                <div class="border-t border-slate-100 p-5"><a class="workspace-button-secondary" href="{{ route('admin.bookings.index') }}">Lihat Semua Booking</a></div>
            </section>
        </div>

        <aside class="min-w-0 space-y-5">
            <section class="relative overflow-hidden rounded-2xl bg-primaryDark p-6 text-white" aria-labelledby="welcome-title">
                <div aria-hidden="true" class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full border-[20px] border-secondary/10"></div>
                <span class="relative inline-flex rounded-xl bg-white/10 p-3 text-secondaryLight"><x-schedule-icon name="room" class="h-6 w-6" /></span>
                <h2 id="welcome-title" class="relative mt-5 text-xl font-semibold leading-7">Ruang tertata.<br>Kolaborasi terjaga.</h2>
                <p class="relative mt-3 text-sm leading-6 text-secondaryLight">Pastikan informasi ruangan, fasilitas, dan penanggung jawab selalu sesuai kebutuhan tim.</p>
                <a href="{{ route('admin.rooms.index') }}" class="relative mt-5 inline-flex items-center gap-2 text-base font-semibold hover:underline">Tinjau ruangan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm" aria-labelledby="account-title">
                <h2 id="account-title" class="font-bold text-primaryDark">Akun Anda</h2>
                <dl class="mt-5 space-y-4 text-base">
                    <div><dt class="text-sm text-slate-500">Login sebagai</dt><dd class="mt-1 break-words font-semibold">{{ $user->name }}</dd></div>
                    <div><dt class="text-sm text-slate-500">Role</dt><dd class="mt-1">{{ $user->roleLabel() }}</dd></div>
                    <div><dt class="text-sm text-slate-500">Unit Kerja</dt><dd class="mt-1 break-words">{{ $user->organizationalUnit?->name ?? 'Belum ditentukan' }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
@endsection
