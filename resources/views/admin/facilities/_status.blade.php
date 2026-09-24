<form method="POST" action="{{ route('admin.facilities.status', $facility) }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_active" value="{{ $facility->is_active ? '0' : '1' }}">
    <button type="submit" aria-label="{{ $facility->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $facility->name }}">{{ $facility->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
</form>
