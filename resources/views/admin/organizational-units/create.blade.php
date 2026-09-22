@extends('layouts.base')
@section('title', 'Tambah Unit Organisasi')
@section('content')
    <h1>Tambah Unit Organisasi</h1>
    @include('admin.organizational-units._feedback')
    <form method="POST" action="{{ route('admin.organizational-units.store') }}">
        @csrf
        @include('admin.organizational-units._form', ['editing' => false])
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.organizational-units.index') }}">Batal</a>
    </form>
@endsection
