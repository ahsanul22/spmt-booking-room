@extends('layouts.base')
@section('title', 'Daftar Unit Organisasi')
@section('content')
    <h1>Daftar Unit Organisasi</h1>
    @include('admin.organizational-units._feedback')
    <a href="{{ route('admin.organizational-units.create') }}">Tambah Unit Organisasi</a>
    <p>Struktur: Direktorat → Divisi → Departemen.</p>
    <x-table :headers="['Nama', 'Type', 'Parent', 'Status Aktif', 'Aksi']">
        @forelse($units ?? [] as $item)
            <tr>
                <td>{{ data_get($item, 'name') ?? '—' }}</td>
                <td>{{ data_get($item, 'type') ?? '—' }}</td>
                <td>{{ data_get($item, 'parent.name') ?? '—' }}</td>
                <td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td>
                    <a href="{{ route('admin.organizational-units.show', $item->id) }}">Detail</a>
                    <a href="{{ route('admin.organizational-units.edit', $item->id) }}">Edit</a>
                    @include('admin.organizational-units._status', ['unit' => $item])
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Belum ada data unit organisasi.</td>
            </tr>
        @endforelse
    </x-table>
    {{ $units->links() }}
@endsection
