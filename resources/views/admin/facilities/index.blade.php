@extends('layouts.base')
@section('title', 'Daftar Fasilitas')
@section('content')
    <h1>Daftar Fasilitas</h1>
    <x-form-feedback />
    <a href="{{ route('admin.facilities.create') }}">Tambah Fasilitas</a>
    <x-table :headers="['Nama', 'Deskripsi', 'Status Aktif', 'Aksi']">
        @forelse($facilities ?? [] as $item)
            <tr>
                <td>{{ data_get($item, 'name') ?? '—' }}</td>
                <td>{{ data_get($item, 'description') ?? '—' }}</td>
                <td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td>
                    <a href="{{ route('admin.facilities.show', $item->id) }}">Detail</a>
                    <a href="{{ route('admin.facilities.edit', $item->id) }}">Edit</a>
                    @include('admin.facilities._status', ['facility' => $item])
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">Belum ada data fasilitas.</td>
            </tr>
        @endforelse
    </x-table>
    {{ $facilities->links() }}
@endsection
