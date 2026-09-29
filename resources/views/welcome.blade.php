@extends('layouts.public')
@section('title', 'Selamat Datang di Booking Room')
@section('content')
<section aria-labelledby="welcome-title" class="mb-12 grid items-center gap-12 overflow-hidden rounded-3xl border border-secondaryLight/50 bg-surface px-6 py-10 sm:px-8 sm:py-12 lg:px-10 lg:py-14 lg:grid-cols-[minmax(0,0.7fr)_minmax(0,1.3fr)]">
    <div class="flex min-w-0 flex-col justify-center text-primaryDark lg:order-2 lg:pl-5">
        <p class="flex items-center gap-3 text-xs sm:text-sm font-bold uppercase tracking-[0.2em] text-primary"><span aria-hidden="true" class="h-px w-8 bg-secondary"></span>Pelindo Multi Terminal</p>
        <h1 id="welcome-title" class="mt-5 text-4xl font-bold leading-[1.12] tracking-tight sm:text-5xl xl:text-6xl">Pertemuan yang baik.<br><span class="text-primary">Dimulai di sini.</span></h1>
        <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Selamat datang di Booking Room PT Pelindo Multi Terminal. Ruang untuk bertukar ide, menyatukan rencana, dan membawa kolaborasi lebih jauh.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('login') }}" class="inline-flex min-h-[48px] items-center justify-center gap-3 rounded-xl bg-primary px-6 py-3 text-base font-semibold text-white transition hover:bg-primaryDark">Login Pegawai<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            <a href="#room-schedule" class="inline-flex min-h-[48px] items-center justify-center gap-3 rounded-xl border border-secondaryLight/60 px-5 py-3 text-base font-semibold text-primaryDark transition hover:bg-background"><x-schedule-icon name="calendar" class="h-4 w-4" />Lihat Jadwal Ruangan</a>
        </div>
        <p class="mt-4 text-sm leading-6 text-primary">Jadwal dapat dilihat langsung. Login untuk melakukan booking.</p>
        <div class="mt-8 border-t border-secondaryLight/60 pt-6">
            <h2 class="text-base font-semibold">Rapat terencana, kerja lebih nyaman.</h2>
            <ol class="mt-4 grid gap-4 text-sm leading-6 sm:grid-cols-3">
                <li><span class="font-semibold text-primary">01 / Masuk</span><span class="mt-1 block text-slate-600">Gunakan akun pegawai Anda.</span></li>
                <li><span class="font-semibold text-primary">02 / Rencanakan</span><span class="mt-1 block text-slate-600">Pilih ruangan, waktu, dan agenda.</span></li>
                <li><span class="font-semibold text-primary">03 / Pantau</span><span class="mt-1 block text-slate-600">Lihat hasil pengajuan di My Booking.</span></li>
            </ol>
            <p class="mt-5 text-sm leading-6 text-primary">Ajukan minimal {{ config('booking.minimum_notice_hours') }} jam sebelum rapat. Ruangan tertentu memerlukan persetujuan PIC.</p>
        </div>
    </div>
    <figure class="relative mx-auto w-full max-w-[420px] overflow-hidden rounded-2xl border border-secondaryLight/40 bg-background lg:order-1">
        <img src="{{ asset('images/dashboard-meeting-room.jpg') }}" alt="Ilustrasi ruang rapat dengan meja kayu, kursi biru, dan pemandangan pelabuhan." width="1122" height="1402" fetchpriority="high" class="h-[240px] w-full object-cover sm:h-[320px] lg:h-[360px]">
        <figcaption class="p-5">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Ruang untuk berkolaborasi</p>
            <p class="mt-2 text-lg font-semibold text-primaryDark">Satu meja. Banyak kemungkinan.</p>
            <p class="mt-2 text-sm text-slate-600">Ilustrasi suasana ruang rapat</p>
        </figcaption>
    </figure>
</section>
<section id="room-schedule" class="scroll-mt-6" aria-labelledby="public-schedule-title">
    <h2 id="public-schedule-title" class="text-2xl font-bold text-primaryDark sm:text-3xl">Jadwal Ruangan</h2>
    <p class="mb-6 mt-3 text-sm leading-6 text-slate-600">Lihat tanggal, ruangan, dan jam penggunaan sebelum membuat booking. Jadwal menampilkan ruangan publik. Semua waktu dalam WIB.</p>
    <livewire:public-room-schedule />
</section>
@endsection
