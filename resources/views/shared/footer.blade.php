<footer class="relative z-10 mt-6 border-t border-white/10 bg-primaryDark">
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
                    @php
                        if (! auth()->check()) {
                            $footerLinks = [route('home') => 'Beranda', route('home').'#room-schedule' => 'Jadwal Ruangan', route('login') => 'Login'];
                        } elseif (auth()->user()->can('access-admin')) {
                            $footerLinks = [route('admin.dashboard') => 'Dashboard', route('admin.rooms.index') => 'Daftar Ruangan', route('admin.bookings.index') => 'Semua Booking', route('admin.schedule.index') => 'Jadwal Ruangan'];
                        } elseif (auth()->user()->can('access-pic')) {
                            $footerLinks = [route('pic.dashboard') => 'Dashboard', route('pic.approvals.index') => 'Permintaan Approval', route('pic.rooms.index') => 'Ruangan Saya', route('schedule.index') => 'Jadwal Ruangan'];
                        } else {
                            $footerLinks = [route('rooms.index') => 'Booking Ruangan', route('my-bookings.index') => 'My Booking', route('schedule.index') => 'Jadwal Ruangan'];
                        }
                    @endphp
                    @foreach($footerLinks as $url => $label)
                        <a href="{{ $url }}" class="inline-flex min-h-[44px] items-center text-sm font-medium text-secondaryLight hover:text-white hover:underline">{{ $label }}</a>
                    @endforeach
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
