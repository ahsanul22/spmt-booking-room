@extends('layouts.base')
@section('title', 'Kelola User')
@section('content')
    <h1>Kelola User</h1>
    @include('admin.users._feedback')
    <a href="{{ route('admin.users.create') }}">Tambah User</a>
    <x-table :headers="['Nama', 'Email', 'Unit Kerja', 'Role', 'Status Aktif', 'Aksi']">
        @forelse($users ?? [] as $item)
            <tr>
                <td>{{ data_get($item, 'name') ?? '—' }}</td>
                <td>{{ data_get($item, 'email') ?? '—' }}</td>
                <td>{{ data_get($item, 'organizationalUnit.name') ?? '—' }}</td>
                <td>{{ data_get($item, 'role') ?? '—' }}</td>
                <td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td>
                    <a href="{{ route('admin.users.show', $item->id) }}">Detail</a>
                    <a href="{{ route('admin.users.edit', $item->id) }}">Edit</a>
                    @include('admin.users._status', ['userRecord' => $item])
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">Belum ada data user.</td>
            </tr>
        @endforelse
    </x-table>
    {{ $users->links() }}
@endsection
