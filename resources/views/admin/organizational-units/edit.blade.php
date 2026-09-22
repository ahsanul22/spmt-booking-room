@extends('layouts.base')
@section('title', 'Edit Unit Organisasi')
@section('content')
    <h1>Edit Unit Organisasi</h1>
    @include('admin.organizational-units._feedback')
    <form method="POST" action="{{ route('admin.organizational-units.update', $unit) }}">
        @csrf
        @method('PUT')
        @include('admin.organizational-units._form', ['editing' => true])
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.organizational-units.index') }}">Batal</a>
    </form>
@endsection
