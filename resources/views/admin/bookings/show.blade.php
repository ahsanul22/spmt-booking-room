@extends('layouts.base')
@section('title', 'Detail Booking')
@section('content')
    <h1>Detail Booking</h1>
    <x-skeleton-notice />
    @include('shared.booking-details')
    <a href="{{ route('admin.bookings.index') }}">Kembali</a>
@endsection
