@extends('layouts.base')
@section('title', 'Edit Fasilitas')
@section('content')
    <h1>Edit Fasilitas</h1>
    <x-form-feedback />
    <form method="POST" action="{{ route('admin.facilities.update', $facility) }}">
        @csrf
        @method('PUT')
        @include('admin.facilities._form', ['editing' => true])
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.facilities.index') }}">Batal</a>
    </form>
@endsection
