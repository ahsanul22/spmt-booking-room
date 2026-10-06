@extends('layouts.public')
@section('title', 'Jadwal Ruangan Publik - Booking Room')
@section('content')
<section aria-labelledby="public-schedule-title" class="mb-8 rounded-3xl border border-secondaryLight/50 bg-surface px-6 py-8 sm:px-8 sm:py-10 lg:px-10">
    <div class="mb-6">
        <a href="{{ route('home') }}" class="group inline-flex items-center gap-2 text-base font-semibold text-slate-500 transition-colors hover:text-primary">
            <svg class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Beranda
        </a>
    </div>

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 id="public-schedule-title" class="text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">
                Jadwal Ketersediaan Ruangan
            </h1>
            <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">
                Lihat tanggal, ruangan, dan jam penggunaan sebelum Anda login untuk membuat pengajuan booking. Semua waktu ditampilkan dalam WIB.
            </p>
        </div>
    </div>
    
    <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-200/60 sm:p-6">
        <livewire:public-room-schedule />
    </div>
</section>
@endsection
