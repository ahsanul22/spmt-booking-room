@extends('layouts.base')
@section('title', 'Daftar Ruangan')
@section('content')
    <h1>Daftar Ruangan</h1>
    <x-form-feedback />
    <a href="{{ route('admin.rooms.create') }}">Tambah Ruangan</a>
    <x-table :headers="['Nama', 'Kode', 'Lantai', 'Kapasitas', 'Access Type', 'Requires Approval', 'Status Operasional', 'Status Aktif', 'Fasilitas', 'Aksi']">
        @forelse($rooms ?? [] as $item)
            <tr>
                <td>{{ data_get($item, 'name') ?? '—' }}</td>
                <td>{{ data_get($item, 'code') ?? '—' }}</td>
                <td>{{ data_get($item, 'floor.name') ?? '—' }}</td>
                <td>{{ data_get($item, 'capacity') ?? '—' }}</td>
                <td>{{ data_get($item, 'access_type') ?? '—' }}</td>
                <td>{{ $item->requires_approval ? 'Ya' : 'Tidak' }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td>{{ $item->facilities->pluck('name')->join(', ') ?: 'Belum ada fasilitas.' }}</td>
                <td>
                    <a href="{{ route('admin.rooms.show', $item->id) }}">Detail</a>
                    <a href="{{ route('admin.rooms.edit', $item->id) }}">Edit</a>
                    <a href="{{ route('admin.rooms.pics', $item->id) }}">Kelola PIC</a>
                    <a href="{{ route('admin.rooms.access', $item->id) }}">Kelola Akses</a>
                    @include('admin.rooms._status', ['room' => $item])
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10">Belum ada data ruangan.</td>
            </tr>
        @endforelse
    </x-table>
    {{ $rooms->links() }}
@endsection
