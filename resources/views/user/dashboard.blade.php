<section aria-labelledby="booking-intro" class="relative mb-12 grid min-h-[58vh] items-center gap-10 overflow-hidden rounded-3xl border border-slate-200/80 bg-surface p-6 sm:p-10 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.7fr)] lg:p-12 xl:p-16">
    <div class="relative max-w-3xl">
        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-primary">Ruang untuk berkolaborasi</p>
        <h2 id="booking-intro" class="mt-5 text-4xl font-bold leading-tight tracking-tight text-primaryDark sm:text-5xl xl:text-6xl">Pertemuan yang baik<br>dimulai dari ruang<br class="hidden xl:block"> yang tepat.</h2>
        <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600">Selamat datang di Booking Room PT Pelindo Multi Terminal. Kenali ruang rapat yang sesuai kebutuhan tim, rencanakan waktu pertemuan, dan pantau pengajuan Anda dalam satu tempat.</p>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">Mulai dengan melihat kapasitas dan fasilitas ruangan di bawah. Saat mengajukan booking, siapkan tanggal, waktu, jumlah peserta, dan agenda rapat. Ruangan tertentu memerlukan persetujuan PIC.</p>
        <div class="mt-8 flex flex-wrap items-center gap-5">
            <a href="#room-catalog" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primaryDark">Jelajahi ruangan<x-schedule-icon name="arrow" class="h-4 w-4 rotate-90" /></a>
            <a href="{{ route('my-bookings.index') }}" class="inline-flex items-center gap-2 py-3 text-sm font-semibold text-primaryDark hover:underline">My Booking<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
        </div>
    </div>
    <div class="relative overflow-hidden rounded-2xl bg-primaryDark p-7 text-white sm:p-9">
        <div aria-hidden="true" class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full border-[30px] border-secondary/10"></div>
        <span class="relative inline-flex rounded-xl bg-white/10 p-3 text-secondaryLight"><x-schedule-icon name="room" class="h-8 w-8" /></span>
        <h3 class="relative mt-5 text-xl font-semibold">Satu ruang, banyak ide.</h3>
        <p class="relative mt-2 text-sm leading-6 text-secondaryLight">Kenali alur perencanaan rapat Anda.</p>
        <ol class="relative mt-7 space-y-6">
            <li class="flex gap-4"><span class="text-sm font-semibold text-secondaryLight" aria-hidden="true">01</span><div><h4 class="text-sm font-semibold">Temukan ruangan</h4><p class="mt-1 text-xs leading-6 text-secondaryLight">Sesuaikan kapasitas, fasilitas, dan kebutuhan pertemuan.</p></div></li>
            <li class="flex gap-4"><span class="text-sm font-semibold text-secondaryLight" aria-hidden="true">02</span><div><h4 class="text-sm font-semibold">Rencanakan pertemuan</h4><p class="mt-1 text-xs leading-6 text-secondaryLight">Tentukan jadwal dan lengkapi agenda sebelum mengajukan.</p></div></li>
            <li class="flex gap-4"><span class="text-sm font-semibold text-secondaryLight" aria-hidden="true">03</span><div><h4 class="text-sm font-semibold">Pantau pengajuan</h4><p class="mt-1 text-xs leading-6 text-secondaryLight">Pastikan status Approved sebelum menggunakan ruangan.</p></div></li>
        </ol>
        <p class="relative mt-7 border-t border-white/15 pt-5 text-xs leading-6 text-secondaryLight">Katalog ruangan sudah dapat dilihat. Pengecekan jadwal, pengajuan, dan approval belum tersedia.</p>
    </div>
</section>

<livewire:room-catalog />

<div class="mt-12 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
    <section aria-labelledby="next-step-title" class="rounded-2xl border border-slate-200/80 bg-surface p-6 sm:p-8">
        <h2 id="next-step-title" class="text-xl font-bold text-primaryDark">Sudah menemukan ruang yang sesuai?</h2>
        <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">Siapkan tanggal, jam mulai dan selesai, jumlah peserta, serta agenda rapat. Pengajuan akan mengikuti aturan akses dan persetujuan masing-masing ruangan.</p>
        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <x-dashboard-shortcut :href="route('schedule.index')" title="Jadwal Ruangan" icon="calendar">Pratinjau kalender. Jadwal kosong belum berarti ruangan tersedia.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('my-bookings.index')" title="My Booking" icon="clock">Pratinjau pengajuan dan status booking pribadi Anda.</x-dashboard-shortcut>
        </div>
    </section>
    @include('shared.dashboard-account')
</div>
