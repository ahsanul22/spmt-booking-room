<form method="POST" action="{{ route('admin.users.status', $userRecord) }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_active" value="{{ $userRecord->is_active ? '0' : '1' }}">
    <button type="submit">{{ $userRecord->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
</form>
