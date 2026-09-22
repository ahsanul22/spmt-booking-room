@extends('layouts.base')
@section('title', 'Tambah User')
@section('content')
    <h1>Tambah User</h1>
    @include('admin.users._feedback')
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        @include('admin.users._form', ['editing' => false])
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.users.index') }}">Batal</a>
    </form>
@endsection
