@extends('layouts.schedule')
@section('title', 'Detail Booking')
@section('breadcrumb', 'Semua Booking / Detail')
@section('content')
    <x-workspace-heading title="Detail Booking" description="Informasi pengajuan dan status ruangan."><a href="{{ route('admin.bookings.index') }}" class="workspace-button-secondary">Kembali</a></x-workspace-heading>
    @include('shared.booking-details')
@endsection
