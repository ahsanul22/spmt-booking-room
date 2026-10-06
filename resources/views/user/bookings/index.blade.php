@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule')
@section('title', 'My Booking')
@section('breadcrumb', 'My Booking')
@section('content')
    <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-3xl font-bold text-primaryDark">My Booking</h1><p class="mt-3 text-base leading-6 text-slate-600">Pengajuan dan riwayat rapat Anda. Pending menunggu keputusan; Approved sudah dikonfirmasi.</p></div>
        <a href="{{ route('rooms.index') }}" class="workspace-button">Booking Ruangan<x-schedule-icon name="plus" class="h-4 w-4" /></a>
    </div>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <x-workspace-panel title="Pengajuan Saya">
        @include('shared.bookings-table', ['detailRoute' => 'my-bookings.show'])
        @if($bookings->isEmpty())<div class="pb-6 text-center"><a href="{{ route('rooms.index') }}" class="workspace-button">Pilih ruangan untuk rapat</a></div>@endif
    </x-workspace-panel>
@endsection
