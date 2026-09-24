@extends('layouts.schedule')
@section('title', 'Edit Ruangan')
@section('breadcrumb', 'Daftar Ruangan / Edit')
@section('content')
    <x-workspace-heading title="Edit Ruangan" description="Perbarui informasi dan pengaturan ruangan agar sesuai kondisi terkini.">
        <a class="workspace-button-secondary" href="{{ route('admin.rooms.show', $room) }}">Kembali ke Detail</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <div class="mb-6 flex items-start gap-4 rounded-2xl border border-secondaryLight/60 bg-secondaryLight/20 p-5">
        <span class="rounded-xl bg-primaryDark p-3 text-white"><x-schedule-icon name="room" /></span>
        <div class="min-w-0"><p class="text-xs text-slate-600">Ruangan yang diedit</p><p class="mt-1 break-words text-lg font-bold text-primaryDark">{{ $room->name }}</p><p class="mt-1 break-words text-xs text-slate-600">Kode: {{ $room->code ?? 'Belum ditentukan' }}</p></div>
    </div>
    <form method="POST" action="{{ route('admin.rooms.update', $room) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.rooms._edit-form')
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-surface p-5 sm:px-6">
            <p class="text-xs leading-5 text-slate-600">Perubahan diterapkan setelah Anda menyimpan.</p>
            <div class="flex flex-wrap gap-3"><a class="workspace-button-secondary" href="{{ route('admin.rooms.index') }}">Batal</a><button class="workspace-button" type="submit">Simpan Perubahan</button></div>
        </div>
    </form>
@endsection
