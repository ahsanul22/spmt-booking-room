<form method="POST" action="{{ route('admin.rooms.status', $room) }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_active" value="{{ $room->is_active ? '0' : '1' }}">
    <button type="submit">{{ $room->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
</form>
