@extends('layouts.base')
@section('title', 'Daftar Lantai')
@section('content')
    <h1>Daftar Lantai</h1>
    <x-form-feedback />
    <a href="{{ route('admin.floors.create') }}">Tambah Lantai</a>
    <x-table :headers="['Nomor Lantai', 'Nama', 'Deskripsi', 'Status Aktif', 'Aksi']">
        @forelse($floors ?? [] as $item)
            <tr>
                <td>{{ data_get($item, 'floor_number') ?? '—' }}</td>
                <td>{{ data_get($item, 'name') ?? '—' }}</td>
                <td>{{ data_get($item, 'description') ?? '—' }}</td>
                <td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td>
                    <a href="{{ route('admin.floors.show', $item->id) }}">Detail</a>
                    <a href="{{ route('admin.floors.edit', $item->id) }}">Edit</a>
                    @include('admin.floors._status', ['floor' => $item])
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Belum ada data lantai.</td>
            </tr>
        @endforelse
    </x-table>
    {{ $floors->links() }}
@endsection
