@extends('layouts.base')
@section('title', 'Cari Ruangan')
@section('content')
    <h1>Cari Ruangan</h1>
    <x-skeleton-notice />
    <x-skeleton-form>
        <x-field name="date" label="Tanggal" type="date" :value="old('date')" required />
        <x-field name="start_time" label="Jam Mulai" type="time" :value="old('start_time')" required />
        <x-field name="end_time" label="Jam Selesai" type="time" :value="old('end_time')" required />
        <x-field name="participant_count" label="Jumlah Peserta" type="number" :value="old('participant_count')" required min="1" />
        <x-pending-action>Cari Ruangan</x-pending-action>
        <a href="{{ route('rooms.index') }}">Batal</a>
    </x-skeleton-form>
    <h2>Hasil Pencarian</h2>
    <x-table :headers="['Nama Ruangan', 'Lantai', 'Kapasitas', 'Fasilitas', 'Status Ketersediaan', 'Aksi']">
        @forelse($rooms ?? [] as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->floor?->name ?? '—' }}</td>
                <td>{{ $item->capacity }}</td>
                <td>{{ $item->facilities->pluck('name')->join(', ') }}</td>
                <td>{{ $item->availability_label ?? 'Belum diperiksa' }}</td>
                <td>
                    <a href="{{ route('rooms.show', $item->id) }}">Detail</a>
                    <a href="{{ route('my-bookings.create') }}">Pilih / Booking</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">Belum ada hasil pencarian.</td>
            </tr>
        @endforelse
    </x-table>
@endsection
