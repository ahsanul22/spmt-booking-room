<div wire:poll.30s x-data="{ changed: false }" x-on:input="changed = true" x-on:change="changed = true" x-on:plan-checked.window="changed = false" class="mx-auto w-full max-w-7xl [&_.workspace-control]:text-base">
    <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
        <div><p class="mb-2 text-base font-semibold text-primary">Langkah 2 dari 2 &middot; Detail pertemuan</p><h1 class="text-3xl font-bold text-primaryDark">Booking Ruangan</h1></div>
        <a href="{{ route('rooms.index') }}" class="workspace-button-secondary">Kembali ke pilihan ruangan</a>
    </div>
    <section aria-labelledby="booking-schedule-title" class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-surface">
        <h2 id="booking-schedule-title" class="px-5 pt-5 text-lg font-bold text-primaryDark sm:px-6">Jadwal Ruangan Ini</h2>
        <div class="space-y-4 p-5 sm:p-6">
            <div class="flex flex-wrap items-end gap-3">
                <div class="w-full sm:w-60"><x-workspace-field wire:model.live="date" name="date" label="Tanggal booking (WIB)" type="date" :min="$earliestStart->format('Y-m-d')" :value="$date" required /></div>
                @if($scheduleDateValid)<a href="{{ route('schedule.index', ['room_id' => $room->id, 'date' => $date]) }}" target="_blank" rel="noopener" class="workspace-button-secondary"><x-schedule-icon name="calendar" class="h-5 w-5" />Lihat kalender<span class="sr-only"> ruangan (tab baru)</span></a>@endif
            </div>
            @if($scheduleDateValid)
                <div class="max-h-64 overflow-y-auto rounded-xl" tabindex="0" role="region" aria-label="Jam penggunaan ruangan">@include('shared.schedule-slots', ['slots' => $daySlots])</div>

            @else
                <p class="text-base text-slate-600">Pilih tanggal yang valid untuk melihat jadwal ruangan.</p>
            @endif
            <div class="space-y-3">
                <button type="button" wire:click="addDate" wire:loading.attr="disabled" class="workspace-button-secondary">+ Tambah booking</button>
                <p class="text-sm text-slate-600">Tambah booking untuk hari lain dengan jam, agenda, peserta, dan permintaan konsumsi yang sama. Setiap booking ditinjau terpisah. Jika satu jadwal bermasalah, seluruh pengajuan belum disimpan.</p>
                @foreach($additionalDates as $index => $extraDate)
                    <div wire:key="extra-date-{{ $index }}" class="flex flex-wrap items-end gap-3">
                        <x-workspace-field wire:model.live="additionalDates.{{ $index }}" name="additionalDates.{{ $index }}" label="Tanggal booking {{ $index + 2 }}" type="date" :min="$earliestStart->format('Y-m-d')" required />
                        <button type="button" wire:click="removeDate({{ $index }})" class="workspace-button-secondary" aria-label="Hapus booking {{ $index + 2 }}">Hapus</button>
                        @if($extraDate)<a href="{{ route('schedule.index', ['room_id' => $room->id, 'date' => $extraDate]) }}" target="_blank" rel="noopener" class="font-semibold text-primary underline">Lihat jadwal<span class="sr-only"> (tab baru)</span></a>@endif
                    </div>
                @endforeach
                @error('additionalDates')<p role="alert" class="text-danger">{{ $message }}</p>@enderror
            </div>
            {{-- <p class="text-base leading-6 text-slate-600">Diperbarui otomatis &middot; {{ $scheduleUpdatedAt }} WIB.</p> --}}
        </div>
    </section>
    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="min-w-0 lg:order-2">
            <x-workspace-panel title="Ruangan pilihan">
                <div class="space-y-4 p-5 sm:p-6">
                    <div><h2 class="break-words text-xl font-bold text-primaryDark">{{ $room->name }}</h2><p class="mt-2 text-base text-slate-600">{{ $room->floor?->name ?? 'Lantai belum ditentukan' }}</p></div>
                    <p class="text-base leading-6 text-slate-600">{{ $room->requires_approval ? 'Pengajuan memerlukan persetujuan PIC.' : 'Ruangan dapat langsung dibooking. Permintaan konsumsi tetap memerlukan review PIC / Admin.' }}</p>
                    <div class="border-t border-slate-100 pt-4 text-base">
                        <h3 class="font-semibold text-primaryDark">Fasilitas ruangan</h3>
                        @if($room->facilities->isNotEmpty())
                            <ul class="mt-3 flex flex-wrap gap-2" aria-label="Fasilitas ruangan terpilih">
                                @foreach($room->facilities as $facility)
                                    <li wire:key="room-facility-{{ $facility->id }}" class="max-w-full break-words rounded-lg border border-slate-200 bg-background px-3 py-1.5 text-sm leading-6 text-slate-700">{{ $facility->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-2 leading-6 text-slate-600">Fasilitas ruangan ini belum dicantumkan.</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Admin dapat melengkapi fasilitas melalui Kelola Ruangan.</p>
                        @endif
                    </div>
                    <p class="text-base leading-6 text-slate-600">Mengganti ruangan membuka form baru. Isian belum disimpan.</p>
                    <a href="{{ route('rooms.index') }}" class="font-semibold text-primary hover:underline">Ganti ruangan</a>
                </div>
            </x-workspace-panel>
        </div>
        <div class="min-w-0 lg:order-1">
            @if($unavailableReason)
                <div role="alert" class="mb-5 rounded-xl border border-danger/30 bg-surface p-5 text-base leading-6 text-danger">{{ $unavailableReason }} <a href="{{ route('rooms.index') }}" class="font-semibold underline">Pilih ruangan lain</a></div>
            @endif
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-surface">
                <div class="px-5 pt-5 sm:px-6"><h2 class="text-lg font-bold text-primaryDark">Detail Pertemuan</h2></div>
                <form wire:submit="submitBooking" method="POST" action="{{ route('rooms.book', $room) }}" class="workspace-form">
                    @csrf
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <p class="text-sm leading-6 text-slate-600 sm:col-span-2">Tidak ada batas minimal waktu pengajuan. Jam mulai belum boleh lewat dan rapat selesai pada tanggal yang sama.</p>
                    <label class="flex min-h-[44px] items-center gap-3 text-base text-primaryDark sm:col-span-2">
                        <input type="checkbox" wire:model.live="outsideWorkHours" class="h-4 w-4 rounded border-slate-300 accent-primary focus:ring-2 focus:ring-primary">
                        Tampilkan jam di luar jam kerja ({{ config('booking.workday_start') }}&ndash;{{ config('booking.workday_end') }} WIB)
                    </label>
                    <div class="grid gap-4 sm:col-span-2 sm:grid-cols-2">
                        <x-workspace-field wire:model.live="start_time" name="start_time" label="Jam Mulai (WIB)" type="select" required>
                            <option value="">Pilih jam mulai</option>
                            @foreach($startOptions as $time)<option value="{{ $time }}">{{ $time }}</option>@endforeach
                        </x-workspace-field>
                        <x-workspace-field wire:model.live="duration" name="duration" label="Durasi" type="select" required>
                            @foreach(config('booking.duration_options') as $minutes => $label)<option value="{{ $minutes }}">{{ $label }}</option>@endforeach
                            <option value="custom">Atur jam selesai sendiri</option>
                        </x-workspace-field>
                        @if($duration === 'custom')
                            <div wire:key="manual-end-time">
                                <x-workspace-field wire:model.live="end_time" name="end_time" label="Jam Selesai (WIB)" type="time" step="60" hint="Isi jam selesai setelah jam mulai, pada tanggal yang sama." required />
                            </div>
                        @else
                            <div wire:key="automatic-end-time" class="flex flex-wrap items-center gap-x-3 gap-y-1 text-base sm:col-span-2">
                                <p class="text-base font-semibold text-primaryDark">Jam selesai otomatis (WIB)</p>
                                <p class="text-base font-bold text-primaryDark">{{ $end_time ?: '' }}</p>
                                @error('end_time')<p role="alert" class="mt-2 text-base text-danger">{{ $message }}</p>@enderror
                            </div>
                        @endif
                        <div role="status" aria-live="polite" class="text-base leading-6 text-primaryDark sm:col-span-2">
                            @if($start_time && $end_time)

                                @if($outsideWorkday)<p>Waktu pilihan berada di luar jam kerja. Pastikan sesuai kebutuhan rapat Anda.</p>@endif
                            @elseif($start_time)
                                <p>Pilih jam selesai pada tanggal yang sama. Jika durasi melewati tengah malam, pilih durasi lebih singkat atau custom.</p>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-2"><x-workspace-field wire:model="agenda" name="agenda" label="Judul / Agenda Rapat" maxlength="255" required /></div>
                    <div class="sm:col-span-2"><x-workspace-field wire:model="notes" name="notes" label="Catatan (opsional)" type="textarea" rows="2" maxlength="2000" /></div>
                    @if($room->allows_meeting_preparation)
                        <fieldset class="min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-surface sm:col-span-2" aria-describedby="meeting-preparation-help">
                            <legend class="sr-only">Persiapan rapat (opsional)</legend>
                            <div class="p-4 sm:p-5">
                                <label for="meeting-preparation-toggle" class="flex cursor-pointer items-start gap-3">
                                    <input id="meeting-preparation-toggle" type="checkbox" wire:model.live="meeting_preparation" name="meeting_preparation" aria-controls="meeting-preparation-fields" aria-describedby="meeting-preparation-help" class="mt-1 h-5 w-5 shrink-0 rounded border-slate-300 text-primary focus:ring-primary focus:ring-offset-2">
                                    <span class="min-w-0">
                                        <span class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                            <span class="text-base font-semibold leading-6 text-primaryDark">Tambahkan kebutuhan persiapan rapat</span>
                                            <span class="rounded-md bg-background px-2 py-1 text-sm font-medium text-slate-600">Opsional</span>
                                        </span>
                                        <span id="meeting-preparation-help" class="mt-1 block text-sm leading-6 text-slate-600">Isi perkiraan peserta dan kebutuhan konsumsi untuk ditinjau PIC.</span>
                                    </span>
                                </label>
                                @error('meeting_preparation')<p role="alert" class="mt-3 text-base text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div id="meeting-preparation-fields">
                                @if($meeting_preparation)
                                    <div class="space-y-4 border-t border-slate-200 bg-background/40 p-4 sm:p-5">
                                        <div class="grid items-start gap-5 sm:grid-cols-2">
                                            <x-workspace-field wire:model="participant_count" type="number" name="participant_count" label="Perkiraan Jumlah Peserta" min="1" max="2147483647" step="1" hint="Jumlah orang yang diperkirakan hadir." required />
                                            <div class="min-w-0">
                                                <p id="consumption-label" class="mb-2 text-base font-semibold text-primaryDark">Kebutuhan Konsumsi</p>
                                                <div class="space-y-2">
                                                    <label for="consumption-snack" class="flex min-h-[44px] cursor-pointer items-center gap-3 rounded-lg border border-slate-300 bg-surface px-3 py-2 transition hover:border-primary focus-within:ring-2 focus-within:ring-primary/30">
                                                        <input id="consumption-snack" type="checkbox" wire:model.live="consumption_snack" name="consumption_snack" aria-describedby="consumption-help{{ $errors->has('consumption_snack') ? ' consumption-snack-error' : '' }}" aria-invalid="{{ $errors->has('consumption_snack') ? 'true' : 'false' }}" class="h-5 w-5 shrink-0 rounded border-slate-300 text-primary focus:ring-primary focus:ring-offset-2">
                                                        <span class="text-base leading-6 text-text">Snack</span>
                                                    </label>
                                                    <label for="consumption-meal" class="flex min-h-[44px] cursor-pointer items-center gap-3 rounded-lg border border-slate-300 bg-surface px-3 py-2 transition hover:border-primary focus-within:ring-2 focus-within:ring-primary/30">
                                                        <input id="consumption-meal" type="checkbox" wire:model.live="consumption_meal" name="consumption_meal" aria-describedby="consumption-help{{ $errors->has('consumption_meal') ? ' consumption-meal-error' : '' }}" aria-invalid="{{ $errors->has('consumption_meal') ? 'true' : 'false' }}" class="h-5 w-5 shrink-0 rounded border-slate-300 text-primary focus:ring-primary focus:ring-offset-2">
                                                        <span class="text-base leading-6 text-text">Makanan</span>
                                                    </label>
                                                </div>
                                                <p id="consumption-help" class="mt-2 text-sm leading-5 text-slate-600">Centang jenis konsumsi yang dibutuhkan untuk rapat.</p>
                                                @error('consumption_snack')<p id="consumption-snack-error" role="alert" class="mt-2 text-base text-danger">{{ $message }}</p>@enderror
                                                @error('consumption_meal')<p id="consumption-meal-error" role="alert" class="mt-2 text-base text-danger">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                        <p class="border-t border-slate-200 pt-4 text-sm leading-6 text-slate-600">Kebutuhan konsumsi akan ditinjau PIC, belum merupakan konfirmasi penyediaan.</p>
                                    </div>
                                @endif
                            </div>
                        </fieldset>
                    @endif
                    @if($errors->any())<div role="alert" class="text-base text-danger sm:col-span-2">Rencana belum lolos pemeriksaan. Periksa pesan pada kolom yang perlu diperbaiki.@error('room')<p>{{ $message }}</p>@enderror</div>@endif
                    @if($checked && ! $unavailableReason)
                        <div x-show="!changed" role="status" class="rounded-xl border border-secondaryLight bg-background p-4 text-base leading-6 text-primaryDark sm:col-span-2">Waktu dan isian lolos pemeriksaan saat ini. <strong>Ruangan belum dipesan.</strong> Klik tombol booking di bawah; ketersediaan akan diperiksa ulang saat dikirim.</div>
                    @endif
                    <p wire:offline class="text-danger sm:col-span-2">Koneksi terputus. Isian tetap di halaman ini; sambungkan kembali sebelum memeriksa rencana.</p>
                    <div class="booking-form-actions">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <button type="button" wire:click="checkPlan" wire:loading.attr="disabled" @disabled($unavailableReason) class="workspace-button-secondary booking-form-button disabled:cursor-not-allowed disabled:opacity-50">Periksa Rencana</button>
                            <button type="submit" wire:loading.attr="disabled" @disabled($unavailableReason) class="workspace-button booking-form-button border border-transparent disabled:cursor-not-allowed disabled:opacity-50">{{ ($room->requires_approval || $consumption_snack || $consumption_meal) ? 'Ajukan Booking' : 'Booking Sekarang' }}</button>
                        </div>
                        <div role="status" aria-live="polite" aria-atomic="true" class="mt-3 min-h-[24px] text-sm leading-6 text-primary">
                            <span wire:loading.delay wire:target="checkPlan">Memeriksa rencana...</span>
                            <span wire:loading.delay wire:target="submitBooking">Mengirim pengajuan booking...</span>
                        </div>
                    </div>
                    {{-- <p id="submit-help">Ketersediaan diperiksa kembali saat booking dikirim.</p> --}}
                    <noscript><p>Aktifkan JavaScript untuk memeriksa dan mengirim booking.</p></noscript>
                </form>
            </div>
        </div>
    </div>
</div>
