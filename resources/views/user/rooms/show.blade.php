@extends('layouts.base')
@section('title', 'Detail Ruangan')
@section('content')
    <h1>Detail Ruangan</h1>
    <x-skeleton-notice />
    @include('shared.room-details')
    <a href="{{ route('rooms.index') }}">Kembali</a>
    @can('access-employee')
        <a href="{{ route('my-bookings.create', ['room_id' => $room->id ?? null]) }}">Booking Ruangan</a>
    @endcan
@endsection
