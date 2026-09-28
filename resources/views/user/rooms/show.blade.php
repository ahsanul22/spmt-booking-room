@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.base')
@section('title', 'Detail Ruangan')
@section('content')
    <h1>Detail Ruangan</h1>
    <x-skeleton-notice />
    @include('shared.room-details')
    <a href="{{ route('rooms.index') }}">Kembali</a>
    @can('access-employee')
        <a href="{{ isset($room) ? route('my-bookings.create', ['room_id' => $room->id]) : route('rooms.index') }}">Booking Ruangan</a>
    @endcan
@endsection
