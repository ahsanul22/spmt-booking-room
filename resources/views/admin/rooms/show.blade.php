@extends('layouts.schedule')
@section('title', 'Detail Ruangan')
@section('breadcrumb', 'Daftar Ruangan / Detail')
@section('content')
    <x-workspace-heading title="Detail Ruangan" description="Informasi lengkap ruangan, fasilitas, dan pengaturan penggunaannya.">
        <div class="flex flex-wrap gap-3"><a class="workspace-button-secondary" href="{{ route('admin.rooms.index') }}">Kembali</a><a class="workspace-button" href="{{ route('admin.rooms.edit', $room) }}">Edit Ruangan</a></div>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <section class="mb-6 overflow-hidden rounded-2xl border border-secondaryLight/60 bg-gradient-to-br from-secondaryLight/30 via-surface to-surface p-5 shadow-sm sm:p-7" aria-labelledby="room-name">
        <div class="flex flex-wrap items-start justify-between gap-5">
            <div class="flex min-w-0 items-start gap-4">
                <span class="shrink-0 rounded-2xl bg-primaryDark p-4 text-white"><x-schedule-icon name="room" class="h-7 w-7" /></span>
                <div class="min-w-0"><p class="break-words text-xs font-semibold text-slate-600">Kode ruang: {{ $room->code ?? 'Belum ditentukan' }}</p><h2 id="room-name" class="mt-2 break-words text-2xl font-bold tracking-tight text-primaryDark sm:text-3xl">{{ $room->name }}</h2></div>
            </div>
            <x-workspace-status :active="$room->is_active" />
        </div>
        <dl class="mt-6 grid gap-5 border-t border-secondaryLight/50 pt-5 sm:grid-cols-3">
            <div><dt class="text-xs text-slate-500">Lantai</dt><dd class="mt-2 break-words font-semibold text-primaryDark">{{ $room->floor?->name ?? 'Belum ditentukan' }}</dd></div>
            <div><dt class="text-xs text-slate-500">Kapasitas</dt><dd class="mt-1 text-primaryDark"><span class="text-3xl font-bold tabular-nums">{{ $room->capacity }}</span> <span class="text-sm">orang</span></dd></div>
            <div><dt class="text-xs text-slate-500">Status Operasional</dt><dd class="mt-2 font-semibold text-primaryDark" title="{{ $room->status }}">{{ ['available' => 'Operasional', 'maintenance' => 'Dalam perawatan', 'unavailable' => 'Tidak dapat digunakan'][$room->status] ?? $room->status }}</dd></div>
        </dl>
    </section>
    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-6">
            <x-workspace-panel title="Tentang Ruangan">
                <div class="p-5 sm:p-6"><p class="whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $room->description ?? 'Belum ada deskripsi ruangan.' }}</p></div>
            </x-workspace-panel>
            <x-workspace-panel title="Fasilitas">
                <x-slot:headingActions><span class="text-xs text-slate-500">{{ $room->facilities->count() }} fasilitas</span></x-slot:headingActions>
                <div class="flex flex-wrap gap-3 p-5 sm:p-6">
                    @forelse($room->facilities as $facility)
                        <span class="max-w-full break-words rounded-xl border border-slate-200 bg-background/50 px-4 py-3 text-sm text-slate-700">{{ $facility->name }}@if(! $facility->is_active)<span class="ml-1 text-xs text-slate-500">(Nonaktif)</span>@endif</span>
                    @empty
                        <p class="text-sm text-slate-600">Belum ada fasilitas.</p>
                    @endforelse
                </div>
            </x-workspace-panel>
            <x-workspace-panel title="PIC Ruangan" description="Penanggung jawab yang terhubung ke ruangan ini.">
                <x-slot:headingActions><a class="inline-flex min-h-[44px] items-center text-sm font-semibold text-primary hover:underline" href="{{ route('admin.rooms.pics', $room) }}">Kelola PIC</a></x-slot:headingActions>
                <ul class="divide-y divide-slate-100">
                    @forelse($room->pics as $pic)
                        <li class="flex flex-wrap items-center justify-between gap-3 p-5 sm:px-6"><span class="min-w-0 break-words text-sm font-semibold text-primaryDark">{{ $pic->name }}</span><x-workspace-status :active="$pic->is_active" /></li>
                    @empty
                        <li class="p-5 text-sm text-slate-600 sm:px-6">Belum ada PIC.</li>
                    @endforelse
                </ul>
            </x-workspace-panel>
            <x-workspace-panel :title="$room->access_type === 'all' ? 'Unit Akses Tersimpan' : 'Unit yang Diperbolehkan (Restricted)'">
                <x-slot:headingActions><a class="inline-flex min-h-[44px] items-center text-sm font-semibold text-primary hover:underline" href="{{ route('admin.rooms.access', $room) }}">Kelola Akses</a></x-slot:headingActions>
                <div class="p-5 sm:p-6">
                    <p class="mb-4 rounded-xl bg-background p-4 text-xs leading-6 text-slate-600">{{ $room->access_type === 'all' ? 'Akses terbuka untuk semua unit. Daftar unit tersimpan di bawah tidak digunakan.' : 'Daftar unit dipilih secara eksplisit. Pilihan unit ini tidak otomatis mencakup unit turunannya.' }}</p>
                    <ul class="space-y-3">
                        @forelse($room->allowedOrganizationalUnits as $unit)
                            <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-3"><span class="min-w-0 break-words text-sm font-medium">{{ $unit->name }}</span><x-workspace-status :active="$unit->is_active" /></li>
                        @empty
                            <li class="text-sm text-slate-600">Belum ada unit akses.</li>
                        @endforelse
                    </ul>
                </div>
            </x-workspace-panel>
        </div>
        <aside class="min-w-0 space-y-6">
            <x-workspace-panel title="Pengaturan Ruangan">
                <div class="space-y-5 p-5 sm:p-6">
                    <dl class="space-y-5 text-sm">
                        <div><dt class="text-xs text-slate-500">Jenis Akses</dt><dd class="mt-1 font-semibold text-primaryDark" title="{{ $room->access_type }}">{{ $room->access_type === 'all' ? 'Semua pegawai' : 'Unit tertentu' }}</dd></div>
                        <div><dt class="text-xs text-slate-500">Perlu Approval</dt><dd class="mt-1 font-semibold text-primaryDark" aria-label="Requires Approval: {{ $room->requires_approval ? 'Ya' : 'Tidak' }}">{{ $room->requires_approval ? 'Ya, melalui PIC ruangan' : 'Tidak' }}</dd></div>
                    </dl>
                    <p class="border-t border-slate-100 pt-4 text-sm text-slate-600">Status Aktif: {{ $room->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                    <div class="workspace-record-actions">@include('admin.rooms._status')</div>
                </div>
            </x-workspace-panel>
            <section class="rounded-2xl bg-primaryDark p-6 text-white" aria-labelledby="schedule-note">
                <x-schedule-icon name="calendar" class="h-7 w-7 text-secondaryLight" />
                <h2 id="schedule-note" class="mt-4 text-lg font-semibold">Jadwal Ruangan</h2>
                <p class="mt-3 text-sm leading-6 text-secondaryLight">Status operasional tidak menunjukkan ketersediaan jadwal. Kalender masih dalam pratinjau dan belum terhubung ke data booking.</p>
                <a class="mt-4 inline-flex min-h-[44px] items-center gap-2 text-sm font-semibold hover:underline" href="{{ route('admin.schedule.index') }}">Buka Jadwal<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            </section>
        </aside>
    </div>
@endsection
