<section aria-labelledby="booking-intro" class="mb-6 overflow-hidden rounded-3xl border border-secondaryLight/50 bg-surface p-6 sm:p-8 lg:p-12">
    <div class="grid min-w-0 items-center gap-8 lg:grid-cols-2 lg:gap-16">
    <div class="flex min-w-0 flex-col justify-center">
        <p class="flex items-center gap-3 text-sm sm:text-base font-bold uppercase tracking-[0.1em] text-primary">Ruang untuk berkolaborasi</p>
        <p class="mt-3 break-words text-base font-medium leading-7 text-slate-600">Selamat datang, {{ $user->name }}.</p>
        <h2 id="booking-intro" class="mt-6 text-4xl font-bold leading-[1.15] tracking-tight text-primaryDark sm:text-5xl xl:text-6xl">Ruang yang tepat.<br><span class="text-primary">Ide yang hebat.</span></h2>
        <p class="mt-6 max-w-xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Temukan ruang untuk pertemuan Anda, tentukan waktu dan agenda, lalu ajukan booking. Pantau status pengajuan melalui My Booking agar rencana rapat tetap jelas.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('rooms.index') }}" class="workspace-button px-6 py-3.5 text-base">Booking Ruangan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            <a href="{{ route('my-bookings.index') }}" class="workspace-button-secondary px-6 py-3.5 text-base">My Booking<x-schedule-icon name="clock" class="h-4 w-4" /></a>
        </div>
        <div class="mt-8 flex max-w-xl items-start gap-3 border-t border-secondaryLight/60 pt-5">
            <x-schedule-icon name="clock" class="mt-1 h-5 w-5 shrink-0 text-primary" />
            <p class="text-base leading-7 text-slate-600">Ajukan minimal <span class="font-semibold text-primaryDark">{{ config('booking.minimum_notice_hours') }} jam sebelum rapat</span>. Ruangan tertentu memerlukan persetujuan PIC.</p>
        </div>
    </div>
    <figure class="relative w-full min-w-0 overflow-hidden rounded-2xl border border-secondaryLight/40 bg-background">
        <img src="{{ asset('images/dashboard-meeting-room.jpg') }}" alt="Ilustrasi ruang rapat modern dengan kursi biru dan pemandangan pelabuhan." width="1122" height="1402" fetchpriority="high" class="h-[240px] w-full object-cover sm:h-[320px] lg:h-[400px]">
        <figcaption class="p-5 sm:p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-primary">Satu ruang, banyak ide.</p>
            <p class="mt-2 text-lg font-semibold text-primaryDark">Tempat untuk langkah besar berikutnya.</p>
            <p class="mt-2 text-base text-slate-600">Ilustrasi suasana ruang rapat</p>
        </figcaption>
    </figure>
    </div>
</section>

<section class="relative my-4 overflow-hidden rounded-3xl bg-gradient-to-r from-primaryDark to-primary px-6 py-10 shadow-2xl sm:px-12 sm:py-14">
    <!-- Decorative background elements -->
    <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-blue-400/20 blur-3xl"></div>

    <div class="relative z-10 grid gap-10 md:items-center lg:grid-cols-[1.2fr_1fr]">
        <!-- Bagian Kiri: 3 Langkah (Dari Rencana Menjadi Pertemuan) -->
        <div class="space-y-8">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-1.5 text-base font-semibold tracking-wider text-white backdrop-blur-md">
                    ALUR PEMESANAN
                </span>
                <h2 class="mt-5 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Dari Rencana<br>Menjadi Pertemuan
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-blue-100">
                    Proses pemesanan ruangan dirancang simpel dalam tiga tahapan.
                </p>
            </div>

            <div class="flex flex-col gap-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white/20 font-bold text-white shadow-inner backdrop-blur-md">01</div>
                    <div class="pt-1">
                        <h3 class="text-lg font-semibold text-white">Pilih Ruangan</h3>
                        <p class="mt-1 text-base text-blue-200">Pilih ruangan dengan fasilitas yang sesuai kebutuhan.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white/20 font-bold text-white shadow-inner backdrop-blur-md">02</div>
                    <div class="pt-1">
                        <h3 class="text-lg font-semibold text-white">Atur Waktu & Agenda</h3>
                        <p class="mt-1 text-base text-blue-200">Tentukan tanggal, jam, dan kelengkapan rapat secara langsung.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white/20 font-bold text-white shadow-inner backdrop-blur-md">03</div>
                    <div class="pt-1">
                        <h3 class="text-lg font-semibold text-white">Pantau Pengajuan</h3>
                        <p class="mt-1 text-base text-blue-200">Lacak status persetujuan dari pengelola dan kelola pesanan Anda.</p>
                    </div>
                </div>
            </div>
            

        </div>
        
        <!-- Bagian Kanan: Visi & Modernisasi -->
        <div class="relative hidden md:flex md:flex-col md:justify-center">
            <div class="relative mx-auto w-full overflow-hidden rounded-2xl border border-white/20 bg-white/10 p-8 shadow-2xl backdrop-blur-xl transition-all duration-500 hover:-translate-y-2 hover:bg-white/15 hover:shadow-black/20">
                <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-lg">
                    <x-schedule-icon name="room" class="h-8 w-8" />
                </div>
                <h3 class="mb-4 text-2xl font-bold text-white">Sistem Booking Modern</h3>
                <p class="mb-6 text-base leading-relaxed text-blue-100">
                    Efisiensi waktu adalah kunci kesuksesan. Platform Manajemen Ruang ini hadir untuk memberikan kemudahan akses, transparansi ketersediaan, serta pengelolaan kolaborasi secara profesional di lingkungan PT Pelindo Multi Terminal.
                </p>
                <div class="mt-2 border-t border-white/10 pt-5">
                    <p class="text-base font-semibold uppercase tracking-wider text-blue-200">Fokus pada Produktivitas</p>
                    <p class="mt-1 text-sm text-blue-100 opacity-80">Kolaborasi hebat selalu dimulai dari ruangan yang tepat.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_340px]">
    <section aria-labelledby="next-step-title" class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-surface p-6 transition-colors group hover:border-primary/30 sm:p-8">
        <div class="absolute right-0 top-0 -mr-4 -mt-4 h-32 w-32 rounded-full bg-primary/5 blur-3xl transition-colors group-hover:bg-primary/10"></div>
        <h2 id="next-step-title" class="relative z-10 text-2xl font-bold tracking-tight text-primaryDark">Rencanakan dengan Sempurna.</h2>
        <p class="relative z-10 mt-4 max-w-2xl text-lg leading-relaxed text-slate-600">
            Setiap ide besar berawal dari ruang dan waktu yang tepat. Pastikan kelancaran agenda Anda dengan mengecek <a href="{{ route('schedule.index') }}" class="font-semibold text-primary underline decoration-primary/30 decoration-2 underline-offset-4 transition-colors hover:text-primaryDark hover:decoration-primary">Kalender Ketersediaan</a> secara real-time, atau pantau seluruh aktivitas pertemuan Anda melalui <a href="{{ route('my-bookings.index') }}" class="font-semibold text-primary underline decoration-primary/30 decoration-2 underline-offset-4 transition-colors hover:text-primaryDark hover:decoration-primary">Riwayat Booking</a>.
        </p>
    </section>
    @include('shared.dashboard-account')
</div>
