@extends('layouts.base')
@section('title', 'Edit Ruangan')
@section('content')
    <h1>Edit Ruangan</h1>
    <x-form-feedback />
    <form method="POST" action="{{ route('admin.rooms.update', $room) }}">
        @csrf
        @method('PUT')
        @include('admin.rooms._form', ['editing' => true])
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.rooms.index') }}">Batal</a>
    </form>
@endsection
