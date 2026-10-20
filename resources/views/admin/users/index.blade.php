@extends('layouts.schedule')
@section('breadcrumb', 'Pengguna')
@section('title', 'Pengguna')
@section('content')
    <x-workspace-heading title="Pengguna" description="Akun pegawai, unit kerja, dan hak akses aplikasi.">
        <a class="workspace-button" href="{{ route('admin.users.create') }}"><x-schedule-icon name="plus" />Tambah Pengguna</a>
    </x-workspace-heading>
    <div class="workspace-feedback">@include('admin.users._feedback')</div>
    <livewire:listing-filters destination="admin.users.index" />
    <x-workspace-panel title="Daftar Pengguna" description="Akun pegawai, unit kerja, dan hak akses aplikasi.">
        <x-slot:headingActions><span class="rounded-lg bg-background px-3 py-2 text-sm font-semibold text-slate-600">{{ $users->total() }} akun</span></x-slot:headingActions>
        @if($users->count())
        <div class="workspace-table" role="region" aria-label="Daftar Pengguna, geser untuk melihat semua kolom" tabindex="0">
        <x-table :headers="['Nama', 'Email', 'Unit Kerja', 'Role', 'Status Aktif', 'Aksi']">
            @foreach($users ?? [] as $item)
                <tr>
                    <td><span class="font-semibold text-primaryDark">{{ $item->name }}</span></td>
                    <td>{{ data_get($item, 'email') ?? '—' }}</td>
                    <td>{{ data_get($item, 'organizationalUnit.name') ?? '—' }}</td>
                    <td>{{ $item->roleLabel() }}@if($item->is_receptionist)<p class="mt-1 text-sm font-semibold text-primary">Petugas resepsionis</p>@endif</td>
                    <td><x-workspace-status :active="$item->is_active" /></td>
                    <td><div class="workspace-record-actions">
                        <a href="{{ route('admin.users.edit', $item->id) }}">Kelola<span class="sr-only"> {{ $item->name }}</span></a>
                    </div></td>
                </tr>
            @endforeach
        </x-table>
        </div>
        @else
            <x-workspace-empty icon="grid" title="Belum ada data pengguna." description="Gunakan tombol Tambah Pengguna untuk melengkapi data administrasi." />
        @endif
        @if($users->hasPages())<div class="border-t border-slate-100 p-5 sm:px-6">{{ $users->links() }}</div>@endif
    </x-workspace-panel>
@endsection
