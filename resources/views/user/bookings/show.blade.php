@extends('layouts.base')
@section('title', 'Detail Booking')
@section('content')
    <h1>Detail Booking</h1>
    <x-skeleton-notice />
    @include('shared.booking-details')
    <x-pending-action>Batalkan Booking</x-pending-action>
    <a href="{{ route('my-bookings.index') }}">Kembali</a>
@endsection
