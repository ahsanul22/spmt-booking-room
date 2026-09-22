@extends('layouts.base')
@section('title', 'Tambah Ruangan')
@section('content')
    <h1>Tambah Ruangan</h1>
    <x-form-feedback />
    <form method="POST" action="{{ route('admin.rooms.store') }}">
        @csrf
        @include('admin.rooms._form', ['editing' => false])
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.rooms.index') }}">Batal</a>
    </form>
@endsection
