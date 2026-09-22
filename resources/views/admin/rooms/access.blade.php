@extends('layouts.base')
@section('title', 'Kelola Akses Ruangan')
@section('content')
    <h1>Kelola Akses: {{ $room->name }}</h1>
    <x-form-feedback />
    <p>Access Type: {{ $room->access_type }}</p>
    @if($room->access_type === 'all')
        <p>Akses terbuka untuk semua unit. Daftar unit tersimpan di bawah tidak digunakan.</p>
        <a href="{{ route('admin.rooms.edit', $room) }}">Edit tipe akses ruangan</a>
    @endif
    <h2>Unit Tersimpan</h2>
    <ul>
        @forelse($room->allowedOrganizationalUnits as $unit)
            <li>{{ $unit->name }} ({{ $unit->type }})</li>
        @empty
            <li>Belum ada unit akses.</li>
        @endforelse
    </ul>
    @if($room->access_type === 'restricted')
        <p>Daftar menyimpan unit yang dipilih secara eksplisit. Aturan pewarisan akses ke unit turunan akan ditentukan pada tahap Booking.</p>
        <form method="POST" action="{{ route('admin.rooms.access.update', $room) }}">
            @csrf
            @method('PUT')
            <fieldset>
                <legend>Unit yang Diperbolehkan</legend>
                @forelse($units as $unit)
                    <label><input type="checkbox" name="organizational_unit_ids[]" value="{{ $unit->id }}" @checked(in_array($unit->id, (array) old('organizational_unit_ids', session()->hasOldInput() ? [] : $selectedUnitIds)))> {{ $unit->name }} ({{ $unit->type }}){{ $unit->is_active ? '' : ' - Nonaktif' }}</label>
                @empty
                    <p>Belum ada unit organisasi yang dapat dipilih.</p>
                @endforelse
            </fieldset>
            <button type="submit">Simpan Unit Akses</button>
        </form>
    @endif
    <a href="{{ route('admin.rooms.show', $room) }}">Kembali ke Detail Ruangan</a>
@endsection
