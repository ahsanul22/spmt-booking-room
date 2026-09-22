@extends('layouts.base')
@section('title', 'Detail User')
@section('content')
    <h1>Detail User</h1>
    @include('admin.users._feedback')
    <dl>
        <dt>Nama</dt>
        <dd>{{ data_get($userRecord ?? null, 'name') ?? '—' }}</dd>
        <dt>Email</dt>
        <dd>{{ data_get($userRecord ?? null, 'email') ?? '—' }}</dd>
        <dt>Unit Kerja</dt>
        <dd>{{ data_get($userRecord ?? null, 'organizationalUnit.name') ?? '—' }}</dd>
        <dt>Role</dt>
        <dd>{{ data_get($userRecord ?? null, 'role') ?? '—' }}</dd>
    </dl>
    <p>Status Aktif: {{ isset($userRecord) ? ($userRecord->is_active ? 'Aktif' : 'Nonaktif') : '—' }}</p>
    <a href="{{ route('admin.users.edit', $userRecord->id) }}">Edit</a>
    <a href="{{ route('admin.users.index') }}">Kembali</a>
    @include('admin.users._status')
    <h2>Reset Password</h2>
    <form method="POST" action="{{ route('admin.users.reset-password', $userRecord) }}">
        @csrf
        @method('PATCH')
        <x-field name="password" label="Password Baru" type="password" required minlength="8" autocomplete="new-password" />
        <x-field name="password_confirmation" label="Konfirmasi Password Baru" type="password" required autocomplete="new-password" />
        <button type="submit">Reset Password</button>
    </form>
@endsection
