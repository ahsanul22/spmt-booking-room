@extends('layouts.schedule')
@section('breadcrumb', 'Kelola User')
@section('title', 'Kelola User')
@section('content')
    <x-workspace-heading title="Kelola User" description="Akun pegawai, unit kerja, dan hak akses aplikasi.">
        <a class="workspace-button" href="{{ route('admin.users.create') }}"><x-schedule-icon name="plus" />Tambah User</a>
    </x-workspace-heading>
    <div class="workspace-feedback">@include('admin.users._feedback')</div>
    <x-workspace-panel title="Daftar User" description="Akun pegawai, unit kerja, dan hak akses aplikasi.">
        <x-slot:headingActions><span class="rounded-lg bg-background px-3 py-2 text-xs font-semibold text-slate-600">{{ $users->total() }} akun</span></x-slot:headingActions>
        @if($users->count())
        <div class="workspace-table" role="region" aria-label="Daftar User, geser untuk melihat semua kolom" tabindex="0">
        <x-table :headers="['Nama', 'Email', 'Unit Kerja', 'Role', 'Status Aktif', 'Aksi']">
            @foreach($users ?? [] as $item)
                <tr>
                    <td><span class="font-semibold text-primaryDark">{{ $item->name }}</span></td>
                    <td>{{ data_get($item, 'email') ?? '—' }}</td>
                    <td>{{ data_get($item, 'organizationalUnit.name') ?? '—' }}</td>
                    <td>{{ data_get($item, 'role') ?? '—' }}</td>
                    <td><x-workspace-status :active="$item->is_active" /></td>
                    <td><div class="workspace-record-actions">
                        <a href="{{ route('admin.users.show', $item->id) }}">Detail<span class="sr-only"> {{ $item->name }}</span></a>
                        <a href="{{ route('admin.users.edit', $item->id) }}">Edit<span class="sr-only"> {{ $item->name }}</span></a>
                        @include('admin.users._status', ['userRecord' => $item])
                    </div></td>
                </tr>
            @endforeach
        </x-table>
        </div>
        @else
            <x-workspace-empty icon="grid" title="Belum ada data user." description="Gunakan tombol Tambah User untuk melengkapi data administrasi." />
        @endif
        @if($users->hasPages())<div class="border-t border-slate-100 p-5 sm:px-6">{{ $users->links() }}</div>@endif
    </x-workspace-panel>
@endsection
