@extends('layouts.public')
@section('title', 'Selamat Datang di Booking Room')
@section('content')
<section aria-labelledby="welcome-title" class="mb-8 grid items-center gap-8 overflow-hidden rounded-3xl border border-secondaryLight/50 bg-surface px-6 py-8 sm:px-8 sm:py-10 lg:px-10 lg:py-10 lg:grid-cols-[minmax(0,0.7fr)_minmax(0,1.3fr)]">
    <div class="flex min-w-0 flex-col justify-center text-primaryDark lg:order-2 lg:pl-5">
        <p class="flex items-center gap-3 text-sm font-bold uppercase tracking-[0.2em] text-primary sm:text-base">Pelindo Multi Terminal</p>
        <h1 id="welcome-title" class="mt-5 text-4xl font-bold leading-[1.12] tracking-tight sm:text-5xl xl:text-6xl">Pertemuan yang baik.<br><span class="text-primary">Dimulai di sini.</span></h1>
        <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Selamat datang di Booking Room PT Pelindo Multi Terminal. Ruang untuk bertukar ide, menyatukan rencana, dan membawa kolaborasi lebih jauh.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('login') }}" class="inline-flex min-h-[48px] items-center justify-center gap-3 rounded-xl bg-primary px-6 py-3 text-base font-semibold text-white transition hover:bg-primaryDark">Login<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            <a href="{{ route('public.schedule') }}" class="inline-flex min-h-[48px] items-center justify-center gap-3 rounded-xl border border-secondaryLight/60 px-5 py-3 text-base font-semibold text-primaryDark transition hover:bg-background"><x-schedule-icon name="calendar" class="h-4 w-4" />Lihat Jadwal Ruangan</a>
        </div>
        <p class="mt-4 text-lg leading-relaxed text-primary">Jadwal dapat dilihat di halaman terpisah. Login untuk melakukan booking.</p>
        <div class="mt-8 border-t border-secondaryLight/60 pt-6">
            <h2 class="text-xl font-semibold text-primaryDark">Rapat terencana, kerja lebih nyaman.</h2>
            <ol class="mt-5 grid gap-4 text-lg leading-relaxed sm:grid-cols-3">
                <li><span class="font-semibold text-primary">01 / Masuk</span><span class="mt-1.5 block text-slate-600">Gunakan akun pegawai Anda.</span></li>
                <li><span class="font-semibold text-primary">02 / Rencanakan</span><span class="mt-1.5 block text-slate-600">Pilih ruangan, waktu, dan agenda.</span></li>
                <li><span class="font-semibold text-primary">03 / Pantau</span><span class="mt-1.5 block text-slate-600">Lihat hasil pengajuan di My Booking.</span></li>
            </ol>
            <p class="mt-6 text-lg leading-relaxed text-primary">Ajukan minimal {{ config('booking.minimum_notice_hours') }} jam sebelum rapat. Ruangan tertentu memerlukan persetujuan PIC.</p>
        </div>
    </div>
    <figure class="relative mx-auto w-full max-w-[420px] overflow-hidden rounded-2xl border border-secondaryLight/40 bg-background lg:order-1">
        <img src="{{ asset('images/dashboard-meeting-room.jpg') }}" alt="Ilustrasi ruang rapat dengan meja kayu, kursi biru, dan pemandangan pelabuhan." width="1122" height="1402" fetchpriority="high" class="h-[240px] w-full object-cover sm:h-[320px] lg:h-[360px]">
        <figcaption class="p-5">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-primary">Ruang untuk berkolaborasi</p>
            <p class="mt-2 text-xl font-semibold text-primaryDark">Satu meja. Banyak kemungkinan.</p>
            <p class="mt-2 text-base text-slate-600">Ilustrasi suasana ruang rapat</p>
        </figcaption>
    </figure>
</section>

<!-- Features Section -->
<section class="mb-8 mt-12" aria-labelledby="features-title">
    <div class="mb-10 text-center">
        <h2 id="features-title" class="text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">Fasilitas & Keunggulan</h2>
        <p class="mx-auto mt-4 max-w-3xl text-lg text-slate-600 sm:text-xl">Kami merancang platform ini untuk memberikan pengalaman pemesanan ruang rapat yang modern, ringkas, dan transparan.</p>
    </div>
    
    <div class="grid gap-6 md:grid-cols-3">
        <!-- Feature 1 -->
        <div class="rounded-3xl border border-slate-200/60 bg-white p-8 shadow-sm transition-shadow hover:shadow-lg">
            <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-primary">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-primaryDark">Informasi Real-time</h3>
            <p class="mt-4 text-lg leading-relaxed text-slate-600 sm:text-xl">Cek ketersediaan jadwal seketika tanpa harus menghubungi pengelola ruangan. Semua tersinkronisasi akurat.</p>
        </div>
        <!-- Feature 2 -->
        <div class="rounded-3xl border border-slate-200/60 bg-white p-8 shadow-sm transition-shadow hover:shadow-lg">
            <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-primaryDark">Booking Cepat</h3>
            <p class="mt-4 text-lg leading-relaxed text-slate-600 sm:text-xl">Proses pengajuan yang ringkas dan terarah. Dalam beberapa klik, jadwal Anda langsung tercatat di sistem.</p>
        </div>
        <!-- Feature 3 -->
        <div class="rounded-3xl border border-slate-200/60 bg-white p-8 shadow-sm transition-shadow hover:shadow-lg">
            <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-primaryDark">Persetujuan Terpusat</h3>
            <p class="mt-4 text-lg leading-relaxed text-slate-600 sm:text-xl">Pantau langsung status persetujuan dari pengelola ruangan melalui halaman riwayat My Booking Anda.</p>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="relative mb-8 mt-12 overflow-hidden rounded-3xl bg-gradient-to-br from-primaryDark to-primary px-6 py-12 shadow-xl sm:px-12 sm:py-16">
    <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-20 -right-20 h-64 w-64 rounded-full bg-blue-400/20 blur-3xl"></div>
    
    <div class="relative z-10 flex flex-col items-center justify-center text-center">
        <h2 class="max-w-4xl text-3xl font-bold leading-tight tracking-tight text-white sm:text-4xl md:text-[44px]">
            Transformasi Cara Kita Berkolaborasi.
        </h2>
        <div class="mt-8 max-w-4xl space-y-5 text-lg leading-relaxed text-blue-50 sm:text-[22px] sm:leading-9">
            <p>
                Di PT Pelindo Multi Terminal, kami menyadari bahwa setiap keputusan penting, strategi brilian, dan inovasi masa depan selalu berawal dari sebuah diskusi yang produktif di dalam ruang rapat.
            </p>
            <p>
                Melalui platform Booking Room terpadu ini, kami menghadirkan ekosistem kerja cerdas yang mengeliminasi kerumitan administratif. Hilangkan waktu yang terbuang untuk mencocokkan jadwal, dan tuangkan seluruh fokus Anda pada hal yang benar-benar esensial: <strong class="font-semibold text-white">Berbagi ide dan menciptakan solusi bersama.</strong>
            </p>
        </div>
        <div class="mt-10 flex w-full max-w-2xl items-center justify-center border-t border-white/20 pt-8">
            <p class="text-[13px] font-bold uppercase tracking-[0.3em] text-blue-100 sm:text-[15px]">
                Langkah Cerdas &bull; Hasil Maksimal
            </p>
        </div>
    </div>
</section>

@endsection
