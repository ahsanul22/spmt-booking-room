@extends('layouts.schedule')
@section('title', 'Kelola Ruangan')
@section('breadcrumb', 'Daftar Ruangan / Kelola')
@section('content')
    <div class="mx-auto w-full max-w-7xl">
    <x-workspace-heading title="Kelola Ruangan" description="Perbarui informasi dan pengaturan ruangan agar sesuai kondisi terkini.">
        <a class="workspace-button-secondary" href="{{ route('admin.rooms.index') }}">Kembali ke Daftar Ruangan</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <div class="mb-6 rounded-2xl border border-slate-200 bg-surface p-5 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex min-w-0 items-start gap-4">
                <span class="shrink-0 rounded-xl bg-secondaryLight/20 p-3 text-primary"><x-schedule-icon name="room" /></span>
                <div class="min-w-0"><p class="text-sm text-slate-600">Pengaturan ruangan</p><h2 class="mt-1 break-words text-xl font-bold text-primaryDark">{{ $room->name }}</h2><p class="mt-2 break-words text-sm leading-6 text-slate-600">{{ $room->floor?->name ?? 'Lantai belum ditentukan' }} &middot; Kode: {{ $room->code ?? 'Belum ditentukan' }}</p></div>
            </div>
            <x-workspace-status :active="$room->is_active" />
        </div>
        <nav aria-label="Bagian pengaturan ruangan" class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
            @foreach(['room-data' => 'Informasi', 'booking-options' => 'Opsi Lanjutan', 'facilities' => 'Fasilitas', 'pics' => 'PIC', 'access' => 'Akses'] as $section => $label)
                <a href="#{{ $section }}" class="inline-flex min-h-[44px] items-center rounded-lg bg-background px-4 py-2 text-base font-semibold text-primaryDark hover:bg-secondaryLight/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary">{{ $label }}</a>
            @endforeach
        </nav>
    </div>
    <form id="room-data" method="POST" action="{{ route('admin.rooms.update', $room) }}" class="scroll-mt-6 space-y-6">
        @csrf
        @method('PUT')
        @include('admin.rooms._edit-form')
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-surface p-5 sm:px-6">
            <p class="text-sm leading-5 text-slate-600">Simpan informasi, opsi lanjutan booking, fasilitas, dan pengaturan operasional di sini.</p>
            <div class="flex flex-wrap gap-3"><a class="workspace-button-secondary" href="{{ route('admin.rooms.index') }}">Batal</a><button class="workspace-button" type="submit">Simpan Perubahan</button></div>
        </div>
    </form>
    <section id="pics" class="mt-8 scroll-mt-6"><h2 class="text-xl font-bold text-primaryDark">PIC Ruangan</h2><p class="mb-5 mt-2 text-base leading-6 text-slate-600">Tentukan penanggung jawab untuk meninjau pengajuan ruangan ini.</p>@include('admin.rooms._pics')</section>
    {{-- <section id="access" class="mt-8 scroll-mt-6"><h2 class="text-xl font-bold text-primaryDark">Akses Ruangan</h2><p class="mb-5 mt-2 text-base leading-6 text-slate-600">Kelola unit kerja yang diperbolehkan menggunakan ruangan.</p>@include('admin.rooms._access')</section> --}}
    <section class="mt-8 rounded-2xl border border-danger/30 bg-surface p-6">
        <h2 class="text-xl font-bold text-primaryDark">Hapus Ruangan</h2>
        <p class="mt-3 text-base text-slate-600">Ruangan tanpa booking dapat dihapus permanen. Jika sudah memiliki booking, nonaktifkan ruangan agar riwayat tetap tersimpan.</p>
        <button type="button" data-dialog-open="delete-room" class="mt-4 rounded-xl border border-danger px-5 py-3 font-semibold text-danger hover:bg-danger/10">Hapus Ruangan</button>
    </section>
    <dialog id="delete-room" aria-labelledby="delete-room-title" aria-describedby="delete-room-warning" class="m-auto max-h-[90dvh] w-[calc(100%-2rem)] max-w-lg overflow-y-auto rounded-2xl bg-surface p-6 shadow-xl backdrop:bg-primaryDark/50">
        <h2 id="delete-room-title" class="break-words text-xl font-bold text-primaryDark">Hapus {{ $room->name }}?</h2>
        <p id="delete-room-warning" class="mt-4 leading-7">Data ruangan beserta hubungan fasilitas, PIC, dan akses akan dihapus permanen. Tindakan ini tidak dapat dibatalkan. Akun PIC, fasilitas, dan unit organisasi tetap tersedia.</p>
        <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" class="mt-6 flex flex-wrap justify-end gap-3">
            @csrf @method('DELETE')
            <input type="hidden" name="confirm_delete" value="1">
            <button type="button" data-dialog-close class="workspace-button-secondary" autofocus>Batal</button>
            <button type="submit" class="rounded-xl bg-danger px-5 py-3 font-semibold text-white hover:opacity-90">Ya, Hapus Ruangan</button>
        </form>
    </dialog>
    </div>
@endsection
