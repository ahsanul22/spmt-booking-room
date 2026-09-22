@extends('layouts.base')
@section('title', 'Detail Unit Organisasi')
@section('content')
    <h1>Detail Unit Organisasi</h1>
    @include('admin.organizational-units._feedback')
    <dl>
        <dt>Nama</dt>
        <dd>{{ data_get($unit ?? null, 'name') ?? '—' }}</dd>
        <dt>Type</dt>
        <dd>{{ data_get($unit ?? null, 'type') ?? '—' }}</dd>
        <dt>Parent</dt>
        <dd>
            @if($unit->parent)
                <a href="{{ route('admin.organizational-units.show', $unit->parent) }}">{{ $unit->parent->name }}</a>
            @else
                —
            @endif
        </dd>
    </dl>
    <p>Status Aktif: {{ isset($unit) ? ($unit->is_active ? 'Aktif' : 'Nonaktif') : '—' }}</p>
    @include('admin.organizational-units._status')
    <h2>Unit di Bawahnya</h2>
    <ul>
        @forelse($unit->children ?? [] as $child)
            <li><a href="{{ route('admin.organizational-units.show', $child) }}">{{ $child->name }}</a> ({{ $child->type }}) — {{ $child->is_active ? 'Aktif' : 'Nonaktif' }}</li>
        @empty
            <li>Belum ada unit di bawahnya.</li>
        @endforelse
    </ul>
    <a href="{{ route('admin.organizational-units.edit', $unit->id) }}">Edit</a>
    <a href="{{ route('admin.organizational-units.index') }}">Kembali</a>
@endsection
