@extends('layouts.base')
@section('title', 'Edit Lantai')
@section('content')
    <h1>Edit Lantai</h1>
    <x-form-feedback />
    <form method="POST" action="{{ route('admin.floors.update', $floor) }}">
        @csrf
        @method('PUT')
        @include('admin.floors._form', ['editing' => true])
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.floors.index') }}">Batal</a>
    </form>
@endsection
