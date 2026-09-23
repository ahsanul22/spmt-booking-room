@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.base')
@section('title', 'Form Booking')
@section('content')
    <h1>Form Booking</h1>
    <x-skeleton-notice />
    <x-skeleton-form>
        <x-select name="room_id" label="Ruangan">
            <option value="">Pilih Ruangan</option>
            @forelse($rooms ?? [] as $option)
                <option value="{{ $option->id }}" @selected((string) (old('room_id')) === (string) $option->id)>{{ $option->name }}</option>
            @empty
                <option disabled>Belum ada pilihan tersedia.</option>
            @endforelse
        </x-select>
        <x-field name="date" label="Tanggal" type="date" :value="old('date')" required />
        <x-field name="start_time" label="Jam Mulai" type="time" :value="old('start_time')" required />
        <x-field name="end_time" label="Jam Selesai" type="time" :value="old('end_time')" required />
        <x-field name="participant_count" label="Jumlah Peserta" type="number" :value="old('participant_count')" required min="1" />
        <x-field name="agenda" label="Judul / Agenda Rapat" type="text" :value="old('agenda')" required />
        <x-textarea name="notes" label="Catatan (opsional)" :value="old('notes')" />
        <x-pending-action>Ajukan Booking</x-pending-action>
        <a href="{{ route('my-bookings.index') }}">Batal</a>
    </x-skeleton-form>
@endsection
