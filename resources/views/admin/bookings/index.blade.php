@extends('layouts.base')
@section('title', 'Semua Booking')
@section('content')
    <h1>Semua Booking</h1>
    <x-skeleton-notice />
    <x-skeleton-form>
        <x-field name="date" label="Tanggal" type="date" :value="old('date')" />
        <x-select name="status" label="Status">
            <option value="">Pilih Status</option>
            <option value="" @selected(old('status') === '')>Semua</option>
            <option value="pending" @selected(old('status') === 'pending')>Pending</option>
            <option value="approved" @selected(old('status') === 'approved')>Approved</option>
            <option value="completed" @selected(old('status') === 'completed')>Completed</option>
            <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
            <option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
        </x-select>
        <x-select name="room_id" label="Ruangan">
            <option value="">Pilih Ruangan</option>
            @forelse($rooms ?? [] as $option)
                <option value="{{ $option->id }}" @selected((string) (old('room_id')) === (string) $option->id)>{{ $option->name }}</option>
            @empty
                <option disabled>Belum ada pilihan tersedia.</option>
            @endforelse
        </x-select>
        <x-select name="organizational_unit_id" label="Unit Kerja">
            <option value="">Pilih Unit Kerja</option>
            @forelse($units ?? [] as $option)
                <option value="{{ $option->id }}" @selected((string) (old('organizational_unit_id')) === (string) $option->id)>{{ $option->name }}</option>
            @empty
                <option disabled>Belum ada pilihan tersedia.</option>
            @endforelse
        </x-select>
        <x-pending-action>Terapkan Filter</x-pending-action>
        <a href="{{ route('admin.bookings.index') }}">Batal</a>
    </x-skeleton-form>
    <a href="{{ route('admin.bookings.show', 'preview') }}">Pratinjau Detail Booking</a>
    <x-table :headers="['Pemohon', 'Unit', 'Ruangan', 'Agenda', 'Tanggal', 'Waktu', 'Status', 'Aksi']">
        @forelse($bookings ?? [] as $item)
            <tr>
                <td>{{ $item->applicant?->name ?? '—' }}</td>
                <td>{{ $item->organizationalUnit?->name ?? '—' }}</td>
                <td>{{ $item->room?->name ?? '—' }}</td>
                <td>{{ $item->agenda }}</td>
                <td>{{ $item->date }}</td>
                <td>{{ ($item->start_time ?? '—').' – '.($item->end_time ?? '—') }}</td>
                <td>{{ $item->status }}</td>
                <td>
                    <a href="{{ route('admin.bookings.show', $item->id) }}">Detail</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Belum ada booking.</td>
            </tr>
        @endforelse
    </x-table>
@endsection
