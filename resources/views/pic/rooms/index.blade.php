@extends('layouts.base')
@section('title', 'Ruangan Saya')
@section('content')
    <h1>Ruangan Saya</h1>
    <x-skeleton-notice />
    <x-table :headers="['Nama', 'Lantai', 'Kapasitas', 'Status', 'Approval', 'Aksi']">
        @forelse($rooms ?? [] as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->floor?->name ?? '—' }}</td>
                <td>{{ $item->capacity }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->requires_approval ? 'Ya' : 'Tidak' }}</td>
                <td>
                    <a href="{{ route('rooms.show', $item->id) }}">Detail</a>
                    <a href="{{ route('schedule.index', ['room_id' => $item->id]) }}">Lihat Jadwal</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">Belum ada ruangan yang ditugaskan.</td>
            </tr>
        @endforelse
    </x-table>
@endsection
