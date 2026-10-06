@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule')
@section('title', 'Booking Ruangan')
@section('breadcrumb', 'Booking Ruangan')
@section('content')
    <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-3xl font-bold text-primaryDark">Booking Ruangan</h1><p class="mt-3 text-base leading-6 text-slate-600">Pilih ruangan dan tentukan jadwal pertemuan.</p></div>
        @can('access-employee')<a href="{{ route('my-bookings.index') }}" class="workspace-button-secondary">Lihat My Booking</a>@endcan
    </div>
    <livewire:room-catalog />
@endsection
