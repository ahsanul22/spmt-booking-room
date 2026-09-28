@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule')
@section('title', 'Rencanakan Booking')
@section('breadcrumb', 'Booking Ruangan / Detail Pertemuan')
@section('content')
    <livewire:booking-preparation-form :room-id="$room->id" />
@endsection
