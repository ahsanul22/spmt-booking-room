@extends('layouts.schedule')

@section('title', $title)
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="mb-7 flex flex-wrap items-center justify-between gap-5">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-primary">Administrasi Workspace</p>
            <h1 class="text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">{{ $title }}</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">Kelola ruang dan dukung kolaborasi dari satu tempat.</p>
        </div>
        <a href="{{ route('admin.rooms.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primaryDark"><x-schedule-icon name="plus" />Tambah Ruangan</a>
    </div>

    @if(session('status'))
        <div role="status" class="mb-6 rounded-xl border border-secondaryLight bg-surface p-4 text-sm text-primaryDark">{{ session('status') }}</div>
    @endif

    <div role="note" class="mb-6 flex items-start gap-3 rounded-xl border border-secondaryLight/70 bg-secondaryLight/20 px-4 py-3 text-xs leading-5 text-primaryDark">
        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary"></span>
        <p><strong>Pratinjau dashboard</strong> · Statistik dan aktivitas booking belum terhubung. Pengelolaan master data sudah dapat digunakan.</p>
    </div>

    <section aria-label="Ringkasan booking dalam pratinjau" class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach(['Booking hari ini' => 'calendar', 'Menunggu approval' => 'clock', 'Ruangan digunakan' => 'room'] as $label => $icon)
            <div class="rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3"><h2 class="text-sm font-medium text-slate-600">{{ $label }}</h2><span class="rounded-lg bg-secondaryLight/20 p-2 text-primary"><x-schedule-icon :name="$icon" /></span></div>
                <p class="mt-4 text-3xl font-semibold text-primaryDark" aria-label="Data belum tersedia">—</p>
                <p class="mt-2 text-xs text-slate-500">Belum terhubung</p>
            </div>
        @endforeach
    </section>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
        <div class="min-w-0 space-y-6">
            <section aria-labelledby="management-title" class="rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm sm:p-6">
                <h2 id="management-title" class="text-lg font-bold text-primaryDark">Kelola Workspace</h2>
                <p class="mt-1 text-xs leading-5 text-slate-600">Akses cepat ke data dan pengaturan ruang rapat.</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <x-dashboard-shortcut :href="route('admin.rooms.index')" title="Kelola Ruangan" icon="room">Kapasitas, status, PIC, dan akses ruangan.</x-dashboard-shortcut>
                    <x-dashboard-shortcut :href="route('admin.users.index')" title="Kelola User">Akun pegawai, role, dan status akses.</x-dashboard-shortcut>
                    <x-dashboard-shortcut :href="route('admin.organizational-units.index')" title="Unit Organisasi">Susunan direktorat, divisi, dan departemen.</x-dashboard-shortcut>
                    <x-dashboard-shortcut :href="route('admin.facilities.index')" title="Kelola Fasilitas">Perlengkapan pendukung ruang rapat.</x-dashboard-shortcut>
                    <x-dashboard-shortcut :href="route('admin.schedule.index')" title="Jadwal Ruangan" icon="calendar">Buka pratinjau kalender ruang rapat.</x-dashboard-shortcut>
                </div>
            </section>

            <section aria-labelledby="activity-title" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:px-6">
                    <h2 id="activity-title" class="text-lg font-bold text-primaryDark">Aktivitas Booking</h2>
                    <span class="rounded-md bg-background px-3 py-1 text-xs font-medium text-slate-600">Pratinjau</span>
                </div>
                <div class="px-6 py-10 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-background text-primary"><x-schedule-icon name="clock" class="h-7 w-7" /></span>
                    <h3 class="mt-4 text-sm font-semibold text-primaryDark">Aktivitas booking belum tersedia</h3>
                    <p class="mx-auto mt-2 max-w-sm text-xs leading-6 text-slate-600">Ringkasan pengajuan dan status rapat akan muncul setelah data booking terhubung.</p>
                    <a href="{{ route('admin.bookings.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">Pratinjau Semua Booking<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
                </div>
            </section>
        </div>

        <aside class="min-w-0 space-y-5">
            <section class="relative overflow-hidden rounded-2xl bg-primaryDark p-6 text-white" aria-labelledby="welcome-title">
                <div aria-hidden="true" class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full border-[20px] border-secondary/10"></div>
                <span class="relative inline-flex rounded-xl bg-white/10 p-3 text-secondaryLight"><x-schedule-icon name="room" class="h-6 w-6" /></span>
                <h2 id="welcome-title" class="relative mt-5 text-xl font-semibold leading-7">Ruang tertata.<br>Kolaborasi terjaga.</h2>
                <p class="relative mt-3 text-xs leading-6 text-secondaryLight">Pastikan informasi ruangan, fasilitas, dan penanggung jawab selalu sesuai kebutuhan tim.</p>
                <a href="{{ route('admin.rooms.index') }}" class="relative mt-5 inline-flex items-center gap-2 text-sm font-semibold hover:underline">Tinjau ruangan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm" aria-labelledby="account-title">
                <h2 id="account-title" class="font-bold text-primaryDark">Akun Anda</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div><dt class="text-xs text-slate-500">Login sebagai</dt><dd class="mt-1 break-words font-semibold">{{ $user->name }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Role</dt><dd class="mt-1">{{ $user->role }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Unit Kerja</dt><dd class="mt-1 break-words">{{ $user->organizationalUnit?->name ?? 'Belum ditentukan' }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
@endsection
