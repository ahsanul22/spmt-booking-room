@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.base')
@section('title', 'My Booking')
@section('content')
    <h1>My Booking</h1>
    <x-skeleton-notice />
    <x-skeleton-form>
        <x-select name="status" label="Status">
            <option value="">Pilih Status</option>
            <option value="" @selected(old('status') === '')>Semua</option>
            <option value="pending" @selected(old('status') === 'pending')>Pending</option>
            <option value="approved" @selected(old('status') === 'approved')>Approved</option>
            <option value="completed" @selected(old('status') === 'completed')>Completed</option>
            <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
            <option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
        </x-select>
        <x-pending-action>Terapkan Filter</x-pending-action>
        <a href="{{ route('my-bookings.index') }}">Batal</a>
    </x-skeleton-form>
    <a href="{{ route('my-bookings.create') }}">Ajukan Booking</a>
    <a href="{{ route('my-bookings.show', 'preview') }}">Pratinjau Detail Booking</a>
    <x-table :headers="['Ruangan', 'Agenda', 'Tanggal', 'Jam', 'Status', 'Aksi']">
        @forelse($bookings ?? [] as $item)
            <tr>
                <td>{{ $item->room?->name ?? '—' }}</td>
                <td>{{ $item->agenda }}</td>
                <td>{{ $item->date }}</td>
                <td>{{ ($item->start_time ?? '—').' – '.($item->end_time ?? '—') }}</td>
                <td>{{ $item->status }}</td>
                <td>
                    <a href="{{ route('my-bookings.show', $item->id) }}">Detail</a>
                    <x-pending-action>Batalkan</x-pending-action>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">Belum ada booking.</td>
            </tr>
        @endforelse
    </x-table>
@endsection
