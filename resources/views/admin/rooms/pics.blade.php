@extends('layouts.base')
@section('title', 'Kelola PIC')
@section('content')
    <h1>Kelola PIC: {{ $room->name }}</h1>
    <x-form-feedback />
    <h2>PIC Saat Ini</h2>
    <ul>
        @forelse($room->pics as $pic)
            <li>
                {{ $pic->name }} ({{ $pic->email }}) - {{ $pic->role }}{{ $pic->is_active ? '' : ' - Nonaktif' }}
                <form method="POST" action="{{ route('admin.rooms.pics.destroy', [$room, $pic]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Lepas PIC</button>
                </form>
            </li>
        @empty
            <li>Belum ada PIC.</li>
        @endforelse
    </ul>
    <form method="POST" action="{{ route('admin.rooms.pics.update', $room) }}">
        @csrf
        @method('PUT')
        <x-select name="pic_ids[]" label="Pilih PIC (role room_pic)" multiple>
            @foreach($eligiblePics as $pic)
                <option value="{{ $pic->id }}" @selected(in_array($pic->id, (array) old('pic_ids', session()->hasOldInput() ? [] : $selectedPicIds)))>{{ $pic->name }} ({{ $pic->email }}){{ $pic->is_active ? '' : ' - Nonaktif' }}</option>
            @endforeach
        </x-select>
        <p>Pilih seluruh PIC yang ingin dipertahankan. Kosongkan pilihan untuk melepas semua PIC. Akun dan role user tidak berubah.</p>
        <button type="submit">Simpan PIC</button>
    </form>
    <a href="{{ route('admin.rooms.show', $room) }}">Kembali ke Detail Ruangan</a>
@endsection
