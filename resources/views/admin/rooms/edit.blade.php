@extends('layouts.schedule')
@section('title', 'Detail / Edit Ruangan')
@section('breadcrumb', 'Daftar Ruangan / Detail')
@section('content')
    <x-workspace-heading title="Detail / Edit Ruangan" description="Perbarui informasi dan pengaturan ruangan agar sesuai kondisi terkini.">
        <a class="workspace-button-secondary" href="{{ route('admin.rooms.index') }}">Kembali ke Daftar Ruangan</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <div class="mb-6 flex items-start gap-4 rounded-2xl border border-secondaryLight/60 bg-secondaryLight/20 p-5">
        <span class="rounded-xl bg-primaryDark p-3 text-white"><x-schedule-icon name="room" /></span>
        <div class="min-w-0"><p class="text-sm text-slate-600">Ruangan yang diedit</p><p class="mt-1 break-words text-lg font-bold text-primaryDark">{{ $room->name }}</p><p class="mt-1 break-words text-sm text-slate-600">Kode: {{ $room->code ?? 'Belum ditentukan' }}</p></div>
    </div>
    <form id="room-data" method="POST" action="{{ route('admin.rooms.update', $room) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.rooms._edit-form')
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-surface p-5 sm:px-6">
            <p class="text-sm leading-5 text-slate-600">Perubahan diterapkan setelah Anda menyimpan.</p>
            <div class="flex flex-wrap gap-3"><a class="workspace-button-secondary" href="{{ route('admin.rooms.index') }}">Batal</a><button class="workspace-button" type="submit">Simpan Perubahan</button></div>
        </div>
    </form>
    <section id="pics" class="mt-8 scroll-mt-6"><h2 class="mb-4 text-xl font-bold text-primaryDark">PIC Ruangan</h2>@include('admin.rooms._pics')</section>
    <section id="access" class="mt-8 scroll-mt-6"><h2 class="mb-4 text-xl font-bold text-primaryDark">Akses Ruangan</h2>@include('admin.rooms._access')</section>
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
@endsection
