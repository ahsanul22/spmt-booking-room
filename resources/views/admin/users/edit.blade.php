@extends('layouts.base')
@section('title', 'Edit User')
@section('content')
    <h1>Edit User</h1>
    @include('admin.users._feedback')
    <form method="POST" action="{{ route('admin.users.update', $userRecord) }}">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['editing' => true])
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.users.index') }}">Batal</a>
    </form>
@endsection
