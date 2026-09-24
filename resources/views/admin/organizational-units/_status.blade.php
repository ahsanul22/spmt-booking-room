<form method="POST" action="{{ route('admin.organizational-units.status', $unit) }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_active" value="{{ $unit->is_active ? '0' : '1' }}">
    <button type="submit" aria-label="{{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $unit->name }}">{{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
</form>
