@extends('layouts.public')
@section('title', 'Selamat Datang di Booking Room')
@section('content')
<div class="grid w-full min-w-0 gap-6 sm:gap-8">
<section aria-labelledby="welcome-title" class="grid w-full min-w-0 items-center gap-8 overflow-hidden rounded-3xl border border-secondaryLight/50 bg-surface p-6 sm:p-10 lg:p-12 lg:grid-cols-2 lg:gap-16">
    <div class="flex min-w-0 flex-col items-start justify-center text-primaryDark lg:order-2">
        <p class="flex items-center gap-3 text-base font-bold uppercase tracking-[0.2em] text-primary sm:text-base">Pelindo Multi Terminal</p>
        <h1 id="welcome-title" class="mt-6 text-4xl font-bold leading-[1.12] tracking-tight sm:text-5xl">Pertemuan yang baik.<br><span class="text-primary">Dimulai di sini.</span></h1>
        <p class="mt-6 max-w-lg text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Selamat datang di Booking Room PT Pelindo Multi Terminal. Ruang untuk bertukar ide, menyatukan rencana, dan membawa kolaborasi lebih jauh.</p>
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="{{ route('public.schedule') }}" class="inline-flex min-h-[48px] items-center justify-center gap-3 rounded-xl border border-secondaryLight/60 px-5 py-3 text-base font-semibold text-primaryDark transition hover:bg-background"><x-schedule-icon name="calendar" class="h-4 w-4" />Lihat Jadwal Ruangan</a>
        </div>
        <p class="mt-5 max-w-md text-base leading-7 text-slate-600">Jadwal dapat dilihat di halaman terpisah. Login untuk melakukan booking.</p>
    </div>
    <figure class="relative w-full min-w-0 overflow-hidden rounded-2xl border border-secondaryLight/40 bg-background lg:order-1">
        <img src="{{ asset('images/welcome-collaboration.png') }}" alt="Ilustrasi area diskusi dengan meja bundar dan kursi biru menghadap pelabuhan." width="1254" height="1254" fetchpriority="high" class="h-[240px] w-full object-cover sm:h-[320px] lg:h-[400px]">
        <figcaption class="space-y-3 p-6 sm:p-8">
            <p class="text-base font-bold uppercase tracking-[0.2em] text-primary">Ruang untuk berkolaborasi</p>
            <p class="mt-2 text-xl font-semibold text-primaryDark">Satu meja. Banyak kemungkinan.</p>
            <p class="mt-2 text-base text-slate-600">Ilustrasi suasana ruang rapat</p>
        </figcaption>
    </figure>
</section>

<section aria-labelledby="steps-title" class="w-full min-w-0 rounded-3xl border border-secondaryLight/50 bg-surface p-6 sm:p-10 lg:p-12">
    <div class="text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary">Tiga langkah sederhana</p>
        <h2 id="steps-title" class="mx-auto mt-4 max-w-xl text-2xl font-semibold leading-snug text-primaryDark sm:text-3xl">Rapat terencana, kerja lebih nyaman.</h2>
    </div>
    <ol class="relative mx-auto mt-12 grid max-w-6xl gap-10 md:grid-cols-3 md:gap-8">
        @foreach ([
            ['number' => '01', 'title' => 'Masuk', 'description' => 'Gunakan akun pegawai Anda.'],
            ['number' => '02', 'title' => 'Rencanakan', 'description' => 'Pilih ruangan, waktu, dan agenda.'],
            ['number' => '03', 'title' => 'Pantau', 'description' => 'Lihat hasil pengajuan di My Booking.'],
        ] as $step)
            <li class="relative flex items-center gap-5 md:flex-col md:text-center">
                @unless ($loop->last)
                    <span aria-hidden="true" class="absolute left-14 top-28 h-[calc(100%-4.5rem)] w-px bg-secondaryLight md:left-1/2 md:top-14 md:h-px md:w-[calc(100%+2rem)]"></span>
                @endunless
                <div class="relative z-10 flex h-28 w-28 shrink-0 flex-col items-center justify-center gap-1 rounded-full border border-secondaryLight bg-background ring-8 ring-surface">
                    <span class="text-2xl font-bold text-primary">{{ $step['number'] }}</span>
                    <h3 class="text-sm font-semibold text-primaryDark">{{ $step['title'] }}</h3>
                </div>
                <p class="relative max-w-xs text-base leading-7 text-slate-600 md:mt-3">{{ $step['description'] }}</p>
            </li>
        @endforeach
    </ol>
    <div class="mt-12 flex w-full items-start gap-4 rounded-2xl bg-secondaryLight/20 p-5 sm:p-6">
        <x-schedule-icon name="clock" class="mt-1 h-5 w-5 shrink-0 text-primary" />
        <p class="text-base leading-7 text-primaryDark">Ajukan minimal {{ config('booking.minimum_notice_hours') }} jam sebelum rapat. Ruangan tertentu memerlukan persetujuan PIC.</p>
    </div>
</section>


<!-- Features Section -->
<section class="w-full min-w-0" aria-labelledby="features-title">
    <div class="mb-10 max-w-2xl sm:mb-12">
        <h2 id="features-title" class="text-2xl font-bold tracking-tight text-primaryDark sm:text-3xl">Fasilitas & Keunggulan</h2>
        <p class="mt-5 max-w-xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Kami merancang platform ini untuk memberikan pengalaman pemesanan ruang rapat yang modern, ringkas, dan transparan.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Feature 1 -->
        <div class="rounded-2xl border border-secondaryLight/50 bg-surface p-6 sm:p-10 lg:p-12">
            <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-secondaryLight/20 text-primary">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-primaryDark">Informasi Real-time</h3>
            <p class="mt-5 max-w-sm text-base leading-8 text-slate-600">Cek ketersediaan jadwal seketika tanpa harus menghubungi pengelola ruangan. Semua tersinkronisasi akurat.</p>
        </div>
        <!-- Feature 2 -->
        <div class="rounded-2xl border border-secondaryLight/50 bg-surface p-6 sm:p-10 lg:p-12">
            <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-secondaryLight/20 text-primary">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-primaryDark">Booking Cepat</h3>
            <p class="mt-5 max-w-sm text-base leading-8 text-slate-600">Proses pengajuan yang ringkas dan terarah. Dalam beberapa klik, jadwal Anda langsung tercatat di sistem.</p>
        </div>
        <!-- Feature 3 -->
        <div class="rounded-2xl border border-secondaryLight/50 bg-surface p-6 sm:p-10 lg:p-12">
            <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-secondaryLight/20 text-primary">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-primaryDark">Persetujuan Terpusat</h3>
            <p class="mt-5 max-w-sm text-base leading-8 text-slate-600">Pantau langsung status persetujuan dari pengelola ruangan melalui halaman riwayat My Booking Anda.</p>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="relative w-full min-w-0 overflow-hidden rounded-3xl bg-primaryDark p-6 sm:p-10 lg:p-12">
    <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-20 -right-20 h-64 w-64 rounded-full bg-secondary/20 blur-3xl"></div>

    <div class="relative z-10 grid gap-10 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:gap-x-16">
        <h2 class="max-w-md text-3xl font-bold leading-tight tracking-tight text-white sm:text-4xl">
            Transformasi Cara Kita Berkolaborasi.
        </h2>
        <div class="max-w-xl space-y-8 text-base leading-8 text-white/90 sm:text-lg sm:leading-9">
            <p>
                Di PT Pelindo Multi Terminal, kami menyadari bahwa setiap keputusan penting, strategi brilian, dan inovasi masa depan selalu berawal dari sebuah diskusi yang produktif di dalam ruang rapat.
            </p>
            <p>
                Melalui platform Booking Room terpadu ini, kami menghadirkan ekosistem kerja cerdas yang mengeliminasi kerumitan administratif. Hilangkan waktu yang terbuang untuk mencocokkan jadwal, dan tuangkan seluruh fokus Anda pada hal yang benar-benar esensial: <strong class="font-semibold text-white">Berbagi ide dan menciptakan solusi bersama.</strong>
            </p>
        </div>
        <div class="border-t border-white/20 pt-8 lg:col-span-2">
            <p class="text-sm font-bold uppercase tracking-[0.3em] text-secondaryLight sm:text-[15px]">
                Langkah Cerdas &bull; Hasil Maksimal
            </p>
        </div>
    </div>
</section>

</div>
@endsection
