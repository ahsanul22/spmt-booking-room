<form method="POST" action="{{ route('admin.rooms.status', $room) }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_active" value="{{ $room->is_active ? '0' : '1' }}">
    <button type="submit" aria-label="{{ $room->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $room->name }}">{{ $room->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
</form>
