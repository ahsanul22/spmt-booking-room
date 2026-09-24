@extends('layouts.schedule')
@section('title', 'Detail User')
@section('breadcrumb', 'Kelola User / Detail')
@section('content')
    <x-workspace-heading title="Detail User" description="Tinjau identitas, akses, dan pengaturan akun pegawai.">
        <a class="workspace-button-secondary" href="{{ route('admin.users.index') }}">Kembali</a>
    </x-workspace-heading>
    <div class="workspace-feedback">@include('admin.users._feedback')</div>
    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,380px)]">
        <x-workspace-panel title="Informasi Akun">
            <x-slot:headingActions><x-workspace-status :active="$userRecord->is_active" /></x-slot:headingActions>
            <div class="p-5 sm:p-6">
                <dl class="workspace-detail">
                    <div><dt>Nama</dt><dd>{{ $userRecord->name }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $userRecord->email }}</dd></div>
                    <div><dt>Unit Kerja</dt><dd>{{ $userRecord->organizationalUnit?->name ?? 'Tanpa Unit Kerja' }}</dd></div>
                    <div><dt>Role</dt><dd>{{ $userRecord->role }}</dd></div>
                </dl>
                <p class="mt-5 text-sm text-slate-600">Status Aktif: {{ $userRecord->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
                    <a class="workspace-button" href="{{ route('admin.users.edit', $userRecord->id) }}">Edit</a>
                    <div class="workspace-record-actions">@include('admin.users._status')</div>
                </div>
            </div>
        </x-workspace-panel>
        <x-workspace-panel title="Reset Password" description="Tentukan password baru untuk login berikutnya. Kolom bertanda * wajib diisi.">
            <form method="POST" action="{{ route('admin.users.reset-password', $userRecord) }}" class="space-y-5 p-5 sm:p-6">
                @csrf
                @method('PATCH')
                <x-workspace-field name="password" label="Password Baru" type="password" required minlength="8" autocomplete="new-password" hint="Gunakan minimal 8 karakter." />
                <x-workspace-field name="password_confirmation" label="Konfirmasi Password Baru" type="password" required autocomplete="new-password" />
                <button class="workspace-button w-full" type="submit">Reset Password</button>
            </form>
        </x-workspace-panel>
    </div>
@endsection
