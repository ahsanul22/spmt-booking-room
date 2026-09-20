@extends('layouts.base')

@section('title', $title)

@section('content')
    <h1>{{ $title }}</h1>
    <dl>
        <dt>Login sebagai</dt>
        <dd>{{ $user->name }}</dd>
        <dt>Role</dt>
        <dd>{{ $user->role }}</dd>
        <dt>Unit Kerja</dt>
        <dd>{{ $user->organizationalUnit?->name ?? 'Belum ditentukan' }}</dd>
    </dl>

@endsection
