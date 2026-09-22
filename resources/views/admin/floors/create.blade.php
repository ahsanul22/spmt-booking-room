@extends('layouts.base')
@section('title', 'Tambah Lantai')
@section('content')
    <h1>Tambah Lantai</h1>
    <x-form-feedback />
    <form method="POST" action="{{ route('admin.floors.store') }}">
        @csrf
        @include('admin.floors._form', ['editing' => false])
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.floors.index') }}">Batal</a>
    </form>
@endsection
