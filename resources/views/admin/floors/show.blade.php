@extends('layouts.base')
@section('title', 'Detail Lantai')
@section('content')
    <h1>Detail Lantai</h1>
    <x-form-feedback />
    <dl>
        <dt>Nomor Lantai</dt>
        <dd>{{ data_get($floor ?? null, 'floor_number') ?? '—' }}</dd>
        <dt>Nama</dt>
        <dd>{{ data_get($floor ?? null, 'name') ?? '—' }}</dd>
        <dt>Deskripsi</dt>
        <dd>{{ data_get($floor ?? null, 'description') ?? '—' }}</dd>
    </dl>
    <p>Status Aktif: {{ isset($floor) ? ($floor->is_active ? 'Aktif' : 'Nonaktif') : '—' }}</p>
    <a href="{{ route('admin.floors.edit', $floor->id) }}">Edit</a>
    <a href="{{ route('admin.floors.index') }}">Kembali</a>
    @include('admin.floors._status')
@endsection
