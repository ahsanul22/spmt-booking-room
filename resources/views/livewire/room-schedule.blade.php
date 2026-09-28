<div wire:poll.30s class="space-y-5">
    <div class="grid items-end gap-4 rounded-2xl border border-slate-200 bg-surface p-5 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_240px_auto]">
        <x-workspace-field name="schedule_room" label="Ruangan" type="select" wire:change="selectRoom($event.target.value)">
            <option value="" @selected($roomId === null)>Semua ruangan</option>
            @foreach($rooms as $room)<option value="{{ $room->id }}" @selected($roomId === $room->id)>{{ $room->name }}{{ ! $room->is_active || $room->status !== 'available' ? ' (tidak operasional)' : '' }}</option>@endforeach
        </x-workspace-field>
        <x-workspace-field name="schedule_date" label="Lompat ke tanggal (WIB)" type="date" :value="$selectedDate" wire:change="selectDate($event.target.value)" />
        <button type="button" wire:click="today" wire:loading.attr="disabled" class="workspace-button-secondary">Hari Ini</button>
        @error('date')<p role="alert" class="text-sm text-danger">{{ $message }}</p>@enderror
    </div>
    <div class="flex flex-wrap gap-3" aria-label="Keterangan warna jadwal">
        <x-schedule-status state="pending" label="Menunggu PIC" /><x-schedule-status state="scheduled" label="Terjadwal" /><x-schedule-status state="ongoing" label="Sedang berlangsung" /><x-schedule-status state="elapsed" label="Sudah lewat" />
    </div>
    <p wire:offline role="alert" class="rounded-xl border border-danger/30 bg-surface p-4 text-sm text-danger">Koneksi terputus. Jadwal di layar mungkin sudah berubah. Sambungkan kembali sebelum memilih waktu.</p>
    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <section aria-label="Kalender booking" class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-surface shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                <h2 class="text-xl font-bold text-primaryDark" aria-live="polite">{{ $monthLabel }}</h2>
                <div class="flex gap-2"><button type="button" wire:click="changeMonth(-1)" wire:loading.attr="disabled" class="workspace-button-secondary" aria-label="Bulan sebelumnya"><x-schedule-icon name="arrow" class="h-4 w-4 rotate-180" /></button><button type="button" wire:click="changeMonth(1)" wire:loading.attr="disabled" class="workspace-button-secondary" aria-label="Bulan berikutnya"><x-schedule-icon name="arrow" class="h-4 w-4" /></button></div>
            </div>
            <div class="overflow-x-auto" role="region" aria-label="Kalender bulanan, geser untuk melihat seluruh tanggal" tabindex="0">
                <div class="min-w-[490px]">
                    <div class="grid grid-cols-7 bg-background/60 text-center text-xs font-semibold text-slate-600">@foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)<div class="py-3">{{ $day }}</div>@endforeach</div>
                    <div class="grid grid-cols-7">
                        @foreach($days as $day)
                            <button type="button" wire:key="date-{{ $day['date'] }}" wire:click="selectDate('{{ $day['date'] }}')" wire:loading.attr="disabled"
                                aria-label="{{ $day['date'] }}, {{ $day['total'] }} booking{{ $day['today'] ? ', hari ini' : '' }}" aria-pressed="{{ $selectedDate === $day['date'] ? 'true' : 'false' }}" @if($day['today']) aria-current="date" @endif
                                @class(['flex min-h-[120px] min-w-0 flex-col items-start gap-1 border-b border-r border-slate-100 p-2 text-left transition hover:bg-secondaryLight/20 sm:p-3', 'bg-secondaryLight/20 ring-1 ring-inset ring-primary' => $selectedDate === $day['date'], 'bg-background/60 text-slate-500' => ! $day['in_month']])>
                                <span @class(['flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold', 'bg-primary text-white' => $day['today'], 'text-primaryDark' => ! $day['today']])>{{ $day['number'] }}</span>
                                @if($day['total'])<span class="text-[11px] font-semibold text-primaryDark">{{ $day['total'] }} booking</span>@endif
                                <span class="flex flex-wrap gap-1">
                                    @foreach(['pending' => 'Pending', 'scheduled' => 'Terjadwal', 'ongoing' => 'Berlangsung', 'elapsed' => 'Lewat'] as $state => $label)
                                        @if($day['counts'][$state] ?? 0)
                                            <span @class(['rounded px-1 py-0.5 text-[10px] font-semibold', 'bg-warning/15 text-warningDark' => $state === 'pending', 'bg-primary/10 text-primaryDark' => $state === 'scheduled', 'bg-danger/10 text-danger' => $state === 'ongoing', 'bg-slate-200 text-slate-600' => $state === 'elapsed'])><span class="sr-only sm:not-sr-only">{{ $label }} </span>{{ $day['counts'][$state] }}</span>
                                        @endif
                                    @endforeach
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <p class="px-5 py-4 text-xs leading-6 text-slate-600">Pilih tanggal untuk membaca ruangan dan jamnya pada daftar jadwal. Angka menunjukkan jumlah booking, bukan jumlah ruangan yang kosong.</p>
        </section>
        <section class="min-w-0 rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm" aria-label="Jadwal tanggal dipilih">
            <h2 class="text-lg font-bold text-primaryDark">Jadwal Harian</h2><p class="mb-5 mt-2 text-sm text-slate-600" aria-live="polite">{{ $dayLabel }} &middot; {{ $daySlots->count() }} booking</p>
            @include('shared.schedule-slots', ['slots' => $daySlots])
        </section>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-2 text-xs leading-6 text-slate-600"><p>Diperbarui {{ $updatedAt }} WIB &middot; otomatis setiap 30 detik.</p><button type="button" wire:click="$refresh" wire:loading.attr="disabled" class="rounded-lg px-3 py-2 font-semibold text-primary hover:bg-surface">Perbarui sekarang</button><span wire:loading role="status">Memuat jadwal...</span></div>
    <noscript><p class="text-sm text-slate-600">Jadwal awal ditampilkan. Aktifkan JavaScript untuk berpindah bulan, memilih tanggal, dan memperbarui data.</p></noscript>
</div>
