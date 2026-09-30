<section aria-labelledby="booking-intro" class="mb-6 grid items-center gap-6 overflow-hidden rounded-3xl border border-secondaryLight/50 bg-surface px-5 py-6 sm:px-7 sm:py-7 lg:px-8 lg:py-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
    <div class="flex min-w-0 flex-col justify-center">
        <p class="flex items-center gap-3 text-xs sm:text-sm font-bold uppercase tracking-[0.1em] text-primary">Ruang untuk berkolaborasi</p>
        <p class="mt-3 break-words text-base font-medium text-slate-600">Selamat datang, {{ $user->name }}.</p>
        <h2 id="booking-intro" class="mt-3 text-4xl font-bold leading-[1.12] tracking-tight text-primaryDark sm:text-5xl xl:text-6xl">Ruang yang tepat.<br><span class="text-primary">Ide yang hebat.</span></h2>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('rooms.index') }}" class="workspace-button px-6 py-3.5 text-base">Booking Ruangan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            <a href="{{ route('my-bookings.index') }}" class="workspace-button-secondary px-6 py-3.5 text-base">My Booking<x-schedule-icon name="clock" class="h-4 w-4" /></a>
        </div>
        <div class="mt-5 border-t border-secondaryLight/60 pt-4">
            <p class="text-lg font-semibold text-primaryDark">Dari rencana menjadi pertemuan.</p>
            <ol class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-base text-slate-600">
                <li><span class="mr-2 font-semibold text-primary">01</span>Pilih ruangan</li>
                <li><span class="mr-2 font-semibold text-primary">02</span>Atur waktu &amp; agenda</li>
                <li><span class="mr-2 font-semibold text-primary">03</span>Pantau pengajuan</li>
            </ol>
        </div>
    </div>
    <figure class="relative mx-auto w-full max-w-[420px] overflow-hidden rounded-2xl border border-secondaryLight/40 bg-background">
        <img src="{{ asset('images/dashboard-meeting-room.jpg') }}" alt="Ilustrasi ruang rapat modern dengan kursi biru dan pemandangan pelabuhan." width="1122" height="1402" fetchpriority="high" class="h-[200px] w-full object-cover sm:h-[240px] lg:h-[260px]">
        <figcaption class="px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.1em] text-primary">Satu ruang, banyak ide.</p>
            <p class="mt-2 text-lg font-semibold text-primaryDark">Tempat untuk langkah besar berikutnya.</p>
            <p class="mt-2 text-sm text-slate-600">Ilustrasi suasana ruang rapat</p>
        </figcaption>
    </figure>
</section>

<livewire:room-catalog :summary="true" />

<div class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_340px]">
    <section aria-labelledby="next-step-title" class="rounded-2xl border border-slate-200/80 bg-surface p-6 sm:p-8">
        <h2 id="next-step-title" class="text-xl font-bold text-primaryDark">Sudah menemukan ruang yang sesuai?</h2>
        <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">Siapkan tanggal, jam mulai dan selesai, serta agenda rapat. Pengajuan akan mengikuti aturan akses dan persetujuan masing-masing ruangan.</p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <x-dashboard-shortcut :href="route('schedule.index')" title="Jadwal Ruangan" icon="calendar">Lihat tanggal, jam, dan penggunaan ruangan sebelum booking.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('my-bookings.index')" title="My Booking" icon="clock">Lihat pengajuan dan status booking pribadi Anda.</x-dashboard-shortcut>
        </div>
    </section>
    @include('shared.dashboard-account')
</div>
