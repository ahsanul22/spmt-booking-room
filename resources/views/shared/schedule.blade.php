@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule')
@section('title', 'Jadwal Ruangan')
@section('breadcrumb', 'Jadwal Ruangan')
@section('content')
    <div data-schedule>
        <div class="mb-7 flex flex-wrap items-center justify-between gap-5">
            <div><p class="mb-2 text-sm font-bold uppercase tracking-[0.2em] text-primary">Rencanakan pertemuan Anda</p><h1 class="text-3xl font-bold text-primaryDark sm:text-4xl">Jadwal Ruangan</h1><p class="mt-3 text-base leading-6 text-slate-600">Lihat tanggal, ruangan, dan jam penggunaan sebelum membuat booking. Semua waktu dalam WIB.</p></div>
            @if(auth()->user()->role === \App\Models\User::ROLE_USER)<a href="{{ route('rooms.index') }}" class="workspace-button"><x-schedule-icon name="plus" />Booking Ruangan</a>@endif
        </div>
        <livewire:room-schedule />
    </div>
@endsection
