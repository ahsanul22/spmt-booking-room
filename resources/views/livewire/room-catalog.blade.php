<section id="room-catalog" class="scroll-mt-6" aria-labelledby="catalog-title">
    <div class="flex flex-wrap items-center gap-4">
        <div class="min-w-0">
            <h2 id="catalog-title" class="text-2xl font-bold tracking-tight text-primaryDark">{{ $summary ? 'Temukan ruang untuk pertemuan Anda' : 'Pilih ruangan' }}</h2>
            <p class="mt-2 text-base leading-6 text-slate-600">{{ $summary ? 'Pilih ruangan untuk mulai merencanakan rapat.' : 'Bandingkan fasilitas dan aturan akses sebelum memilih.' }}</p>
        </div>
        <p class="text-base text-slate-600 sm:ml-auto" aria-live="polite">{{ $rooms->total() }} ruangan</p>
    </div>

    @if(! $summary)
    <div class="mt-7 flex flex-wrap gap-x-6 border-b border-slate-300 sm:gap-x-9" role="group" aria-label="Filter ruangan">
        @foreach($filters as $key => $label)
            <button type="button" wire:key="filter-{{ $key }}" wire:click="selectFilter('{{ $key }}')" wire:loading.attr="disabled" aria-pressed="{{ $filter === $key ? 'true' : 'false' }}" aria-controls="room-results"
                @class(['-mb-px border-b-2 px-1 py-3 text-base transition disabled:cursor-wait focus-visible:outline-primary', 'border-primaryDark font-bold text-primaryDark' => $filter === $key, 'border-transparent font-medium text-slate-600 hover:border-slate-400 hover:text-text' => $filter !== $key])>{{ $label }}</button>
        @endforeach
    </div>
    @endif
    <div class="flex min-h-[44px] flex-wrap items-center justify-between gap-2 py-3 text-base leading-5 text-slate-600">
        <p>Status operasional bukan ketersediaan jadwal. Pilih ruangan untuk menentukan waktu booking.</p>
        <span wire:loading role="status" class="font-medium text-primary">Memuat ruangan…</span>
    </div>
    <p wire:offline class="mb-4 rounded-xl border border-danger/30 bg-surface p-4 text-base text-danger">Koneksi terputus. Sambungkan kembali untuk mengganti filter ruangan.</p>
    <noscript><p class="mb-4 text-base text-slate-600">Daftar awal ditampilkan di bawah. Aktifkan JavaScript untuk mengganti filter dan halaman.</p></noscript>

    <div id="room-results" wire:loading.class="opacity-50" class="grid gap-5 transition-opacity sm:grid-cols-2 xl:grid-cols-3">
        @forelse($rooms as $room)
            @include('shared.room-catalog-card', ['public' => false])
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-surface px-6 py-14 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-background text-primary"><x-schedule-icon name="room" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-lg font-semibold text-primaryDark">Belum ada ruangan pada pilihan ini</h3>
                <p class="mt-2 text-base text-slate-600">Coba pilihan lain. Ruangan aktif akan muncul setelah ditambahkan oleh administrator.</p>
            </div>
        @endforelse
    </div>
    @if($summary)
        <a href="{{ route('rooms.index') }}" class="workspace-button-secondary mt-6">Lihat semua ruangan</a>
    @elseif($rooms->hasPages())
        <nav aria-label="Halaman katalog ruangan" class="mt-6 flex flex-wrap items-center justify-between gap-3 text-base">
            <button type="button" wire:click="previousPage('roomsPage')" wire:loading.attr="disabled" @disabled($rooms->onFirstPage()) class="rounded-lg border border-slate-300 px-4 py-3 text-primaryDark enabled:hover:bg-surface disabled:cursor-not-allowed disabled:text-muted">Sebelumnya</button>
            <p class="text-slate-600">Halaman {{ $rooms->currentPage() }} dari {{ $rooms->lastPage() }}</p>
            <button type="button" wire:click="nextPage('roomsPage')" wire:loading.attr="disabled" @disabled(! $rooms->hasMorePages()) class="rounded-lg border border-slate-300 px-4 py-3 text-primaryDark enabled:hover:bg-surface disabled:cursor-not-allowed disabled:text-muted">Berikutnya</button>
        </nav>
    @endif
</section>
