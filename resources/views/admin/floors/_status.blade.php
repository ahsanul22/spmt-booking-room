<form method="POST" action="{{ route('admin.floors.status', $floor) }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_active" value="{{ $floor->is_active ? '0' : '1' }}">
    <button type="submit">{{ $floor->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
</form>
