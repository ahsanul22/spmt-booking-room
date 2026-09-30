@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule')
@section('title', 'Booking Ruangan')
@section('breadcrumb', 'Booking Ruangan')
@section('content')
    <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
        <div><p class="mb-2 text-sm font-semibold text-primary">Langkah 1 dari 2 &middot; Pilih ruangan</p><h1 class="text-3xl font-bold text-primaryDark">Booking Ruangan</h1><p class="mt-3 text-base leading-6 text-slate-600">Pilih ruang yang sesuai, lalu tentukan waktu dan agenda pertemuan.</p></div>
        @can('access-employee')<a href="{{ route('my-bookings.index') }}" class="workspace-button-secondary">Lihat My Booking</a>@endcan
    </div>
    <div class="mb-6 text-sm leading-6 text-slate-600">Rencanakan rapat minimal {{ config('booking.minimum_notice_hours') }} jam sebelumnya. Semua waktu menggunakan WIB. Ketersediaan diperiksa kembali saat pengajuan dikirim.</div>
    <livewire:room-catalog />
@endsection
