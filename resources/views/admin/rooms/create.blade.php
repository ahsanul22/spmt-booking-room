@extends('layouts.schedule')
@section('title', 'Tambah Ruangan')
@section('breadcrumb', 'Daftar Ruangan / Tambah')
@section('content')
    <x-workspace-heading title="Tambah Ruangan" description="Lengkapi informasi, fasilitas, dan pengaturan penggunaan ruangan baru.">
        <a class="workspace-button-secondary" href="{{ route('admin.rooms.index') }}">Kembali ke Daftar</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <form method="POST" action="{{ route('admin.rooms.store') }}" class="space-y-6">
        @csrf
        @include('admin.rooms._form', ['editing' => false])
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-surface p-5 sm:px-6">
            <p class="text-base leading-6 text-slate-600">Periksa informasi ruangan sebelum menyimpan.</p>
            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                <a class="workspace-button-secondary" href="{{ route('admin.rooms.index') }}">Batal</a>
                <button class="workspace-button" type="submit">Simpan Ruangan</button>
            </div>
        </div>
    </form>
@endsection
