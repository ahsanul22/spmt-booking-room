<div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-primaryDark p-6 text-white">
    <div class="max-w-2xl"><h2 class="text-xl font-bold">Prioritaskan permintaan yang perlu ditinjau.</h2><p class="mt-2 text-sm leading-6 text-secondaryLight">Sebagai PIC, fokus Anda adalah memeriksa pengajuan dan memberikan keputusan untuk ruangan yang menjadi tanggung jawab Anda.</p></div>
    {{-- <a href="{{ route('pic.approvals.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-surface px-5 py-3 text-sm font-semibold text-primaryDark hover:bg-secondaryLight">Permintaan Approval<x-schedule-icon name="arrow" class="h-4 w-4" /></a> --}}
</div>
<p role="note" class="mb-6 rounded-xl border border-secondaryLight bg-secondaryLight/20 px-4 py-3 text-xs leading-5 text-primaryDark"><strong>Pratinjau dashboard PIC</strong> · Antrean pengajuan, data ruangan PIC, dan riwayat keputusan belum terhubung. Persetujuan atau penolakan belum dapat diproses.</p>

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
    <div class="min-w-0 space-y-6">
        <section aria-labelledby="approval-title" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:px-6"><h2 id="approval-title" class="text-lg font-bold text-primaryDark">Menunggu Tinjauan Anda</h2><span class="rounded-md bg-background px-3 py-1 text-xs text-slate-600">Pratinjau</span></div>
            <div class="px-6 py-10 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-background text-primary"><x-schedule-icon name="clock" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-sm font-semibold text-primaryDark">Antrean approval belum tersedia</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Permintaan untuk ruangan yang ditugaskan kepada Anda akan ditampilkan di sini setelah layanan approval tersedia.</p>
                <a href="{{ route('pic.approvals.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">Buka pratinjau permintaan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
            </div>
        </section>
        <section aria-labelledby="rooms-title" class="rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm">
            <h2 id="rooms-title" class="text-lg font-bold text-primaryDark">Ruangan dan Jadwal</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Tinjau ruangan tanggung jawab Anda dan waktu penggunaan sebelum mengambil keputusan.</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <x-dashboard-shortcut :href="route('pic.rooms.index')" title="Ruangan Saya" icon="room">Pratinjau daftar ruangan yang ditugaskan kepada Anda.</x-dashboard-shortcut>
                <x-dashboard-shortcut :href="route('schedule.index')" title="Jadwal Ruangan" icon="calendar">Pratinjau kalender; belum menunjukkan ketersediaan.</x-dashboard-shortcut>
            </div>
        </section>
        <section aria-labelledby="history-title" class="rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm">
            <h2 id="history-title" class="text-lg font-bold text-primaryDark">Keputusan Sebelumnya</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Riwayat persetujuan dan penolakan belum tersedia.</p>
            <a href="{{ route('pic.approvals.history') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">Pratinjau Riwayat Approval<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
        </section>
    </div>
    <div class="min-w-0 space-y-5">
        <section aria-labelledby="review-guide-title" class="rounded-2xl border border-secondaryLight bg-secondaryLight/20 p-6">
            <h2 id="review-guide-title" class="font-bold text-primaryDark">Saat meninjau pengajuan</h2>
            <ol class="mt-4 list-decimal space-y-3 pl-4 text-sm leading-6 text-slate-600"><li>Periksa ruangan, pemohon, dan agenda rapat.</li><li>Tinjau tanggal, waktu, jumlah peserta, serta jadwal penggunaan.</li><li>Berikan keputusan sesuai pengajuan. Sertakan alasan jika menolak.</li></ol>
            <p class="mt-4 border-t border-secondaryLight pt-4 text-xs leading-5 text-primaryDark">Kewenangan PIC mengikuti penugasan ruangan, bukan seluruh ruangan atau unit kerja.</p>
        </section>
        @can('access-employee')
            <section aria-labelledby="personal-title" class="rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm">
                <h2 id="personal-title" class="font-bold text-primaryDark">Butuh ruang untuk rapat Anda?</h2>
                <p class="mt-2 text-xs leading-6 text-slate-600">Booking pribadi tetap tersedia sebagai menu terpisah dari tugas approval. Prosesnya masih pratinjau.</p>
                <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold text-primary"><a href="{{ route('rooms.search') }}" class="hover:underline">Cari Ruangan</a><a href="{{ route('my-bookings.index') }}" class="hover:underline">My Booking</a></div>
            </section>
        @endcan
        @include('shared.dashboard-account')
    </div>
</div>
