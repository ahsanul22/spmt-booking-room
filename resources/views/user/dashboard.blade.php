<section aria-labelledby="booking-intro" class="mb-12 grid items-center gap-12 overflow-hidden rounded-3xl border border-secondaryLight/50 bg-surface px-6 py-10 sm:px-8 sm:py-12 lg:px-10 lg:py-14 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
    <div class="flex min-w-0 flex-col justify-center">
        <p class="flex items-center gap-3 text-xs sm:text-sm font-bold uppercase tracking-[0.2em] text-primary"><span aria-hidden="true" class="h-px w-8 bg-primary"></span>Ruang untuk berkolaborasi</p>
        <p class="mt-5 break-words text-base font-medium text-slate-600">Selamat datang, {{ $user->name }}.</p>
        <h2 id="booking-intro" class="mt-4 text-4xl font-bold leading-[1.12] tracking-tight text-primaryDark sm:text-5xl xl:text-6xl">Ruang yang tepat.<br><span class="text-primary">Ide yang hebat.</span></h2>
        <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Pertemuan yang baik dimulai dari ruang yang tepat. Temukan ruang rapat untuk tim Anda, rencanakan pertemuan, dan mulai kolaborasi berikutnya.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('rooms.index') }}" class="workspace-button px-6 py-3.5 text-base">Booking Ruangan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            <a href="{{ route('my-bookings.index') }}" class="workspace-button-secondary px-6 py-3.5 text-base">My Booking<x-schedule-icon name="clock" class="h-4 w-4" /></a>
        </div>
        <div class="mt-8 border-t border-secondaryLight/60 pt-6">
            <p class="text-base font-semibold text-primaryDark">Dari rencana menjadi pertemuan.</p>
            <ol class="mt-4 grid gap-4 text-sm leading-6 sm:grid-cols-3">
                <li><span class="font-bold text-primary">01 /</span><span class="mt-1 block font-semibold text-primaryDark">Temukan ruangan</span><span class="text-slate-600">Pilih fasilitas yang sesuai.</span></li>
                <li><span class="font-bold text-primary">02 /</span><span class="mt-1 block font-semibold text-primaryDark">Rencanakan pertemuan</span><span class="text-slate-600">Tentukan waktu dan agenda.</span></li>
                <li><span class="font-bold text-primary">03 /</span><span class="mt-1 block font-semibold text-primaryDark">Pantau pengajuan</span><span class="text-slate-600">Lihat status di My Booking.</span></li>
            </ol>
        </div>
    </div>
    <figure class="relative mx-auto w-full max-w-[420px] overflow-hidden rounded-2xl border border-secondaryLight/40 bg-background">
        <img src="{{ asset('images/dashboard-meeting-room.jpg') }}" alt="Ilustrasi ruang rapat modern dengan kursi biru dan pemandangan pelabuhan." width="1122" height="1402" fetchpriority="high" class="h-[240px] w-full object-cover sm:h-[320px] lg:h-[360px]">
        <figcaption class="p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Satu ruang, banyak ide.</p>
            <p class="mt-2 text-lg font-semibold text-primaryDark">Tempat untuk langkah besar berikutnya.</p>
            <p class="mt-2 text-sm text-slate-600">Ilustrasi suasana ruang rapat</p>
        </figcaption>
    </figure>
</section>

<livewire:room-catalog :summary="true" />

<div class="mt-10 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
    <section aria-labelledby="next-step-title" class="rounded-2xl border border-slate-200/80 bg-surface p-6 sm:p-8">
        <h2 id="next-step-title" class="text-xl font-bold text-primaryDark">Sudah menemukan ruang yang sesuai?</h2>
        <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">Siapkan tanggal, jam mulai dan selesai, serta agenda rapat. Pengajuan akan mengikuti aturan akses dan persetujuan masing-masing ruangan.</p>
        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <x-dashboard-shortcut :href="route('schedule.index')" title="Jadwal Ruangan" icon="calendar">Lihat tanggal, jam, dan penggunaan ruangan sebelum booking.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('my-bookings.index')" title="My Booking" icon="clock">Lihat pengajuan dan status booking pribadi Anda.</x-dashboard-shortcut>
        </div>
    </section>
    @include('shared.dashboard-account')
</div>
