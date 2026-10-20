@extends('layouts.schedule')
@section('breadcrumb', 'Kelola User')
@section('title', 'Kelola User')
@section('content')
    <x-workspace-heading title="Kelola User" description="Atur identitas, status aktif, dan izin akses kantor atau cabang pengguna.">
        <a class="workspace-button-secondary" href="{{ route('admin.users.index') }}">Kembali ke Kelola User</a>
    </x-workspace-heading>
    <div class="workspace-feedback">@include('admin.users._feedback')</div>
    <p class="mb-5 text-base text-slate-600">{{ $userRecord->name }} · {{ $userRecord->roleLabel() }} · Status Aktif: {{ $userRecord->is_active ? 'Aktif' : 'Nonaktif' }}</p>
    <x-workspace-panel title="Informasi Akun" description="Kolom bertanda * wajib diisi.">
        <form class="workspace-form" method="POST" action="{{ route('admin.users.update', $userRecord) }}">
            @csrf
            @method('PUT')
            @include('admin.users._form', ['editing' => true])
            <div class="workspace-form-actions">
                <button class="workspace-button" type="submit">Simpan</button>
                <a class="workspace-button-secondary" href="{{ route('admin.users.index') }}">Batal</a>
            </div>
        </form>
    </x-workspace-panel>
    <div class="mt-6">
        <x-workspace-panel title="Reset Password" description="Tentukan password baru untuk login berikutnya.">
            <form method="POST" action="{{ route('admin.users.reset-password', $userRecord) }}" class="space-y-5 p-5 sm:p-6">
                @csrf
                @method('PATCH')
                <div class="workspace-field"><label for="reset-password" class="block text-base font-semibold text-primaryDark">Password Baru *</label><input id="reset-password" name="password" type="password" required minlength="8" autocomplete="new-password" class="workspace-control"></div>
                <div class="workspace-field"><label for="reset-password-confirmation" class="block text-base font-semibold text-primaryDark">Konfirmasi Password Baru *</label><input id="reset-password-confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="workspace-control"></div>
                <button class="workspace-button" type="submit">Reset Password</button>
            </form>
        </x-workspace-panel>
    </div>
@endsection
