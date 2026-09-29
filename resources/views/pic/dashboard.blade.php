<div class="relative mb-8 overflow-hidden rounded-3xl border border-white/10 bg-[#091E3A] shadow-xl">
    {{-- Banner background graphic with 3D illustration and glowing grid --}}
    <div class="pointer-events-none absolute inset-0 select-none">
        <img src="{{ asset('images/dashboard-pic-banner.jpg') }}" alt="" class="h-full w-full object-cover object-right lg:object-center">
        {{-- High-contrast gradient overlay for small devices to guarantee readability --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#091E3A] via-[#091E3A]/90 to-transparent sm:via-[#091E3A]/75 lg:hidden"></div>
    </div>

    {{-- Banner Content --}}
    <div class="relative z-10 flex min-h-[360px] flex-col justify-center p-6 sm:p-10 md:p-12 lg:max-w-2xl xl:max-w-3xl">
        {{-- Pill badge --}}
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 backdrop-blur-md">
                <svg class="h-3.5 w-3.5 text-white/90" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <span class="text-xs font-semibold tracking-wider text-white">DASHBOARD PIC RUANGAN</span>
                <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399]"></span>
            </span>
        </div>

        {{-- Heading --}}
        <h2 class="mt-5 text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-[42px] lg:leading-tight">
            Selamat Datang! 👋
        </h2>

        {{-- Subheading --}}
        <p class="mt-2 text-xl font-bold text-white sm:text-2xl">
            Mari kelola pengajuan dengan mudah.
        </p>

        {{-- Description text --}}
        <p class="mt-4 max-w-xl text-xs leading-relaxed text-blue-100/90 sm:text-sm sm:leading-6">
            Di sini Anda dapat dengan mudah memeriksa pengajuan pemesanan ruangan yang menjadi tanggung jawab Anda. Prioritaskan pengajuan yang perlu segera ditinjau, kemudian berikan keputusan sesuai dengan kondisi dan ketersediaan ruangan.
        </p>

        {{-- Action Button --}}
        <div class="mt-7">
            <a href="{{ route('pic.approvals.index') }}" class="group inline-flex items-center gap-2.5 rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-800 shadow-xl shadow-black/15 transition-all duration-200 hover:bg-slate-50 hover:shadow-2xl hover:scale-[1.02] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white active:scale-[0.98]">
                <svg class="h-4 w-4 text-slate-800 transition-transform duration-200 group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Cek Daftar Pengajuan</span>
            </a>
        </div>
    </div>
</div>
<p role="note" class="mb-6 rounded-xl border border-secondaryLight bg-secondaryLight/20 px-4 py-3 text-xs leading-5 text-primaryDark"><strong>Pratinjau dashboard PIC</strong> · Buka Permintaan Approval untuk meninjau dan memutuskan pengajuan. Ringkasan dashboard belum menampilkan antrean langsung.</p>

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
    <div class="min-w-0 space-y-6">
        <section aria-labelledby="approval-title" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:px-6"><h2 id="approval-title" class="text-lg font-bold text-primaryDark">Menunggu Tinjauan Anda</h2><span class="rounded-md bg-background px-3 py-1 text-xs text-slate-600">Pratinjau</span></div>
            <div class="px-6 py-10 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-background text-primary"><x-schedule-icon name="clock" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-sm font-semibold text-primaryDark">Tinjau permintaan approval</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Permintaan untuk ruangan yang ditugaskan kepada Anda tersedia di halaman Permintaan Approval.</p>
                <a href="{{ route('pic.approvals.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">Buka permintaan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            </div>
        </section>
        <section aria-labelledby="rooms-title" class="rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm">
            <h2 id="rooms-title" class="text-lg font-bold text-primaryDark">Ruangan dan Jadwal</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Tinjau ruangan tanggung jawab Anda dan waktu penggunaan sebelum mengambil keputusan.</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <x-dashboard-shortcut :href="route('pic.rooms.index')" title="Ruangan Saya" icon="room">Lihat informasi dan jadwal ruangan yang ditugaskan kepada Anda.</x-dashboard-shortcut>
                <x-dashboard-shortcut :href="route('schedule.index')" title="Jadwal Ruangan" icon="calendar">Lihat jam penggunaan dan booking ruangan.</x-dashboard-shortcut>
            </div>
        </section>
        <section aria-labelledby="history-title" class="rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm">
            <h2 id="history-title" class="text-lg font-bold text-primaryDark">Keputusan Sebelumnya</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Lihat keputusan persetujuan dan penolakan untuk ruangan Anda.</p>
            <a href="{{ route('pic.approvals.history') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">Lihat Riwayat Approval<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
        </section>
    </div>
    <div class="min-w-0 space-y-5">
        <section aria-labelledby="review-guide-title" class="rounded-2xl border border-secondaryLight bg-secondaryLight/20 p-6">
            <h2 id="review-guide-title" class="font-bold text-primaryDark">Saat meninjau pengajuan</h2>
            <ol class="mt-4 list-decimal space-y-3 pl-4 text-sm leading-6 text-slate-600"><li>Periksa ruangan, pemohon, dan agenda rapat.</li><li>Tinjau tanggal, waktu, serta jadwal penggunaan.</li><li>Berikan keputusan sesuai pengajuan. Sertakan alasan jika menolak.</li></ol>
            <p class="mt-4 border-t border-secondaryLight pt-4 text-xs leading-5 text-primaryDark">Kewenangan PIC mengikuti penugasan ruangan, bukan seluruh ruangan atau unit kerja.</p>
        </section>
        @include('shared.dashboard-account')
    </div>
</div>
