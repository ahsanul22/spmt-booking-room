@extends('layouts.base')
@section('title', 'Detail Ruangan')
@section('content')
    <h1>Detail Ruangan</h1>
    <x-form-feedback />
    @include('shared.room-details')
    <p>Status Aktif: {{ isset($room) ? ($room->is_active ? 'Aktif' : 'Nonaktif') : '—' }}</p>
    <h2>PIC</h2>
    <ul>
        @forelse($room->pics ?? [] as $pic)
            <li>{{ $pic->name }}</li>
        @empty
            <li>Belum ada PIC.</li>
        @endforelse
    </ul>
    @if($room->access_type === 'all')
        <h2>Unit Akses Tersimpan</h2>
        <p>Akses terbuka untuk semua unit. Daftar unit tersimpan di bawah tidak digunakan.</p>
    @else
        <h2>Unit yang Diperbolehkan (Restricted)</h2>
        <p>Daftar unit dipilih secara eksplisit. Aturan pewarisan akses akan ditentukan pada tahap Booking.</p>
    @endif
    <ul>
        @forelse($room->allowedOrganizationalUnits ?? [] as $unit)
            <li>{{ $unit->name }}</li>
        @empty
            <li>Belum ada unit akses.</li>
        @endforelse
    </ul>
    <a href="{{ route('admin.rooms.pics', $room->id) }}">Kelola PIC</a>
    <a href="{{ route('admin.rooms.access', $room->id) }}">Kelola Akses</a>
    <a href="{{ route('admin.rooms.edit', $room->id) }}">Edit</a>
    <a href="{{ route('admin.rooms.index') }}">Kembali</a>
    @include('admin.rooms._status')
@endsection
