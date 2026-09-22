@extends('layouts.base')
@section('title', 'Tambah Fasilitas')
@section('content')
    <h1>Tambah Fasilitas</h1>
    <x-form-feedback />
    <form method="POST" action="{{ route('admin.facilities.store') }}">
        @csrf
        @include('admin.facilities._form', ['editing' => false])
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.facilities.index') }}">Batal</a>
    </form>
@endsection
