@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule')
@section('title', 'Detail Booking')
@section('breadcrumb', 'My Booking / Detail')
@section('content')
    <div class="mb-7 flex flex-wrap items-center justify-between gap-4"><h1 class="text-3xl font-bold text-primaryDark">Detail Booking</h1><a href="{{ route('my-bookings.index') }}" class="workspace-button-secondary">Kembali ke My Booking</a></div>
    <div class="workspace-feedback"><x-form-feedback /></div>
    @include('shared.booking-details')
@endsection
