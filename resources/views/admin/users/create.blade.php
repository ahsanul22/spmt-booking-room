@extends('layouts.schedule')
@section('breadcrumb', 'Kelola User')
@section('title', 'Tambah User')
@section('content')
    <x-workspace-heading title="Tambah User" description="Lengkapi identitas dan pengaturan akses pengguna.">
        <a class="workspace-button-secondary" href="{{ route('admin.users.index') }}">Kembali ke Kelola User</a>
    </x-workspace-heading>
    <div class="workspace-feedback">@include('admin.users._feedback')</div>
    <x-workspace-panel title="Informasi Akun" description="Kolom bertanda * wajib diisi.">
        <form class="workspace-form" method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form', ['editing' => false])
            <div class="workspace-form-actions">
                <button class="workspace-button" type="submit">Simpan</button>
                <a class="workspace-button-secondary" href="{{ route('admin.users.index') }}">Batal</a>
            </div>
        </form>
    </x-workspace-panel>
@endsection
