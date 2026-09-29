<section aria-labelledby="booking-intro" class="mb-10 grid min-h-screen overflow-hidden rounded-3xl border border-secondaryLight/50 bg-surface supports-[height:100svh]:min-h-[100svh] lg:min-h-[calc(100vh-216px)] lg:grid-cols-2 lg:supports-[height:100svh]:min-h-[calc(100svh-216px)]">
    <div class="flex min-w-0 flex-col justify-center p-6 sm:p-10 xl:p-16">
        <p class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.2em] text-primary"><span aria-hidden="true" class="h-px w-8 bg-primary"></span>Ruang untuk berkolaborasi</p>
        <p class="mt-7 break-words text-sm font-medium text-slate-600">Selamat datang, {{ $user->name }}.</p>
        <h2 id="booking-intro" class="mt-4 text-4xl font-bold leading-[1.12] tracking-tight text-primaryDark sm:text-5xl xl:text-6xl">Ruang yang tepat.<br><span class="text-primary">Ide yang hebat.</span></h2>
        <p class="mt-6 max-w-lg text-sm leading-7 text-slate-600 sm:text-base sm:leading-8">Pertemuan yang baik dimulai dari ruang yang tepat. Temukan ruang rapat untuk tim Anda, rencanakan pertemuan, dan mulai kolaborasi berikutnya.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('rooms.index') }}" class="workspace-button px-6 py-3.5">Booking Ruangan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            <a href="{{ route('my-bookings.index') }}" class="workspace-button-secondary px-6 py-3.5">My Booking<x-schedule-icon name="clock" class="h-4 w-4" /></a>
        </div>
        <div class="mt-10 border-t border-secondaryLight/60 pt-6 xl:mt-14">
            <p class="text-xs font-semibold text-primaryDark">Dari rencana menjadi pertemuan.</p>
            <ol class="mt-4 grid gap-4 text-xs leading-5 sm:grid-cols-3">
                <li><span class="font-bold text-primary">01 /</span><span class="mt-1 block font-semibold text-primaryDark">Temukan ruangan</span><span class="text-slate-600">Pilih fasilitas yang sesuai.</span></li>
                <li><span class="font-bold text-primary">02 /</span><span class="mt-1 block font-semibold text-primaryDark">Rencanakan pertemuan</span><span class="text-slate-600">Tentukan waktu dan agenda.</span></li>
                <li><span class="font-bold text-primary">03 /</span><span class="mt-1 block font-semibold text-primaryDark">Pantau pengajuan</span><span class="text-slate-600">Lihat status di My Booking.</span></li>
            </ol>
        </div>
    </div>
    <figure class="relative m-3 mt-0 min-h-[360px] overflow-hidden rounded-2xl bg-secondaryLight sm:min-h-[440px] lg:m-3 lg:ml-0 lg:min-h-[560px]">
        <img src="{{ asset('images/dashboard-meeting-room.jpg') }}" alt="Ilustrasi ruang rapat modern dengan kursi biru dan pemandangan pelabuhan." width="1122" height="1402" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover">
        <figcaption class="absolute inset-x-4 bottom-4 rounded-xl bg-primaryDark/95 p-5 text-white sm:inset-x-6 sm:bottom-6 sm:p-6">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-secondaryLight">Satu ruang, banyak ide.</p>
            <p class="mt-2 text-xl font-semibold sm:text-2xl">Tempat untuk langkah besar berikutnya.</p>
            <p class="mt-3 text-xs text-secondaryLight">Ilustrasi suasana ruang rapat</p>
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
