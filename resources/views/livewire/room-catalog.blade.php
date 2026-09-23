<section id="room-catalog" class="scroll-mt-6" aria-labelledby="catalog-title">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary">Temukan ruang Anda</p><h2 id="catalog-title" class="mt-2 text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">Pilihan ruang untuk setiap ide.</h2><p class="mt-3 text-sm leading-6 text-slate-600">Kenali kapasitas, fasilitas, dan aturan ruangan sebelum merencanakan pertemuan.</p></div>
        <p class="text-sm text-slate-500" aria-live="polite">{{ $rooms->total() }} ruangan</p>
    </div>

    <div class="mt-7 flex flex-wrap gap-x-6 border-b border-slate-300 sm:gap-x-9" role="group" aria-label="Filter ruangan">
        @foreach($filters as $key => $label)
            <button type="button" wire:key="filter-{{ $key }}" wire:click="selectFilter('{{ $key }}')" wire:loading.attr="disabled" aria-pressed="{{ $filter === $key ? 'true' : 'false' }}" aria-controls="room-results"
                @class(['-mb-px border-b-2 px-1 py-4 text-sm transition disabled:cursor-wait focus-visible:outline-primary', 'border-primaryDark font-bold text-primaryDark' => $filter === $key, 'border-transparent font-medium text-slate-600 hover:border-slate-400 hover:text-text' => $filter !== $key])>{{ $label }}</button>
        @endforeach
    </div>
    <div class="flex min-h-[44px] flex-wrap items-center justify-between gap-2 py-3 text-xs leading-5 text-slate-600">
        <p>Status operasional bukan ketersediaan jadwal. Proses booking belum tersedia.</p>
        <span wire:loading role="status" class="font-medium text-primary">Memuat ruangan…</span>
    </div>
    <p wire:offline class="mb-4 rounded-xl border border-danger/30 bg-surface p-4 text-sm text-danger">Koneksi terputus. Sambungkan kembali untuk mengganti filter ruangan.</p>
    <noscript><p class="mb-4 text-sm text-slate-600">Daftar awal ditampilkan di bawah. Aktifkan JavaScript untuk mengganti filter dan halaman.</p></noscript>

    <div id="room-results" wire:loading.class="opacity-50" class="grid gap-5 transition-opacity sm:grid-cols-2 xl:grid-cols-3">
        @forelse($rooms as $room)
            <article wire:key="room-{{ $room->id }}" class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm transition hover:border-secondaryLight hover:shadow-md">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-background/50 px-6 py-5">
                    <span class="inline-flex rounded-xl border border-secondaryLight/50 bg-surface p-3 text-primary"><x-schedule-icon name="room" class="h-7 w-7" /></span>
                    <span @class(['rounded-full border px-3 py-1 text-xs font-medium', 'border-slate-200 bg-surface text-slate-600' => $room->status === 'available', 'border-danger/20 bg-danger/5 text-danger' => $room->status !== 'available'])>
                        {{ match ($room->status) { 'available' => 'Operasional', 'maintenance' => 'Dalam perawatan', default => 'Tidak operasional' } }}
                    </span>
                </div>
                <div class="flex flex-1 flex-col p-6">
                    <p class="break-words text-xs font-medium text-slate-500">{{ $room->floor?->name ?? 'Lantai belum ditentukan' }}</p>
                    <h3 class="mt-2 break-words text-xl font-bold text-primaryDark">{{ $room->name }}</h3>
                    <p class="mt-2 text-sm text-slate-600">Kapasitas {{ $room->capacity }} peserta</p>
                    <p class="mt-4 break-words text-sm leading-6 text-slate-600">{{ $room->description ?: 'Deskripsi ruangan belum ditambahkan.' }}</p>
                    <div class="mt-5">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Fasilitas</h4>
                        <ul class="mt-2 flex flex-wrap gap-2">
                            @forelse($room->facilities as $facility)
                                <li class="max-w-full break-words rounded-md bg-background px-2.5 py-1 text-xs text-slate-600">{{ $facility->name }}</li>
                            @empty
                                <li class="text-xs text-slate-500">Fasilitas belum dicantumkan.</li>
                            @endforelse
                        </ul>
                    </div>
                    <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-xs leading-5">
                        <div><dt class="text-slate-500">Akses ruangan</dt><dd class="mt-1 font-medium text-primaryDark">{{ $room->access_type === 'restricted' ? 'Unit tertentu' : 'Semua pegawai' }}</dd></div>
                        <div><dt class="text-slate-500">Persetujuan</dt><dd class="mt-1 font-medium text-primaryDark">{{ $room->requires_approval ? 'Memerlukan PIC' : 'Tanpa approval PIC' }}</dd></div>
                    </dl>
                    <div class="mt-auto pt-5"><button type="button" disabled class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-background px-4 py-3 text-sm text-slate-500">Booking belum tersedia</button></div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-surface px-6 py-14 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-background text-primary"><x-schedule-icon name="room" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-lg font-semibold text-primaryDark">Belum ada ruangan pada pilihan ini</h3>
                <p class="mt-2 text-sm text-slate-600">Coba pilihan lain. Ruangan aktif akan muncul setelah ditambahkan oleh administrator.</p>
            </div>
        @endforelse
    </div>
    @if($rooms->hasPages())
        <nav aria-label="Halaman katalog ruangan" class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm">
            <button type="button" wire:click="previousPage('roomsPage')" wire:loading.attr="disabled" @disabled($rooms->onFirstPage()) class="rounded-lg border border-slate-300 px-4 py-3 text-primaryDark enabled:hover:bg-surface disabled:cursor-not-allowed disabled:text-muted">Sebelumnya</button>
            <p class="text-slate-600">Halaman {{ $rooms->currentPage() }} dari {{ $rooms->lastPage() }}</p>
            <button type="button" wire:click="nextPage('roomsPage')" wire:loading.attr="disabled" @disabled(! $rooms->hasMorePages()) class="rounded-lg border border-slate-300 px-4 py-3 text-primaryDark enabled:hover:bg-surface disabled:cursor-not-allowed disabled:text-muted">Berikutnya</button>
        </nav>
    @endif
</section>
