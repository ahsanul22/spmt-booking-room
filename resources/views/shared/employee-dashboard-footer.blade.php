<footer class="mt-6 border-t border-white/10 bg-primaryDark">
    <div class="mx-auto max-w-[1800px] px-5 pt-10 sm:px-8">
        <div class="grid gap-8 pb-8 md:grid-cols-[minmax(0,1fr)_auto] md:gap-16">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondaryLight">PT Pelindo Multi Terminal</p>
                <p class="mt-3 text-2xl font-bold tracking-tight text-white">Ruang untuk berkolaborasi.</p>
                <p class="mt-3 max-w-lg text-sm leading-7 text-secondaryLight">Rencanakan pertemuan, temukan ruangan yang sesuai, dan pantau pengajuan Anda dalam satu tempat.</p>
            </div>
            <nav aria-label="Navigasi footer" class="min-w-0">
                <p class="text-sm font-semibold text-white">Akses cepat</p>
                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 md:max-w-sm">
                    <a href="{{ route('rooms.index') }}" class="inline-flex min-h-[44px] items-center text-sm font-medium text-secondaryLight hover:text-white hover:underline">Booking Ruangan</a>
                    <a href="{{ route('my-bookings.index') }}" class="inline-flex min-h-[44px] items-center text-sm font-medium text-secondaryLight hover:text-white hover:underline">My Booking</a>
                    <a href="{{ route('schedule.index') }}" class="inline-flex min-h-[44px] items-center text-sm font-medium text-secondaryLight hover:text-white hover:underline">Jadwal Ruangan</a>
                </div>
            </nav>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-3 border-t border-white/15 py-5 text-xs leading-6 text-secondaryLight">
            <p>&copy; {{ now()->year }} PT Pelindo Multi Terminal</p>
            <p>Booking Room &middot; Untuk pegawai internal</p>
            <a href="#content" class="inline-flex min-h-[44px] items-center gap-2 font-semibold text-white hover:underline">Kembali ke atas <span aria-hidden="true">&uarr;</span></a>
        </div>
    </div>
</footer>
