@extends('layouts.base')
@section('title', 'Detail Fasilitas')
@section('content')
    <h1>Detail Fasilitas</h1>
    <x-form-feedback />
    <dl>
        <dt>Nama</dt>
        <dd>{{ data_get($facility ?? null, 'name') ?? '—' }}</dd>
        <dt>Deskripsi</dt>
        <dd>{{ data_get($facility ?? null, 'description') ?? '—' }}</dd>
    </dl>
    <p>Status Aktif: {{ isset($facility) ? ($facility->is_active ? 'Aktif' : 'Nonaktif') : '—' }}</p>
    <a href="{{ route('admin.facilities.edit', $facility->id) }}">Edit</a>
    <a href="{{ route('admin.facilities.index') }}">Kembali</a>
    @include('admin.facilities._status')
@endsection
