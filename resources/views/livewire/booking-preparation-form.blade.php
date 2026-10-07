<div wire:poll.30s x-data="{ changed: false }" x-on:input="changed = true" x-on:change="changed = true" x-on:plan-checked.window="changed = false" class="mx-auto max-w-6xl [&_.workspace-control]:text-base">
    <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
        <div><p class="mb-2 text-base font-semibold text-primary">Langkah 2 dari 2 &middot; Detail pertemuan</p><h1 class="text-3xl font-bold text-primaryDark">Booking Ruangan</h1></div>
        <a href="{{ route('rooms.index') }}" class="workspace-button-secondary">Kembali ke pilihan ruangan</a>
    </div>
    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
        <div class="min-w-0 lg:order-2">
            <x-workspace-panel title="Ruangan pilihan">
                <div class="space-y-4 p-5 sm:p-6">
                    <div><h2 class="break-words text-xl font-bold text-primaryDark">{{ $room->name }}</h2><p class="mt-2 text-base text-slate-600">{{ $room->floor?->name ?? 'Lantai belum ditentukan' }}</p></div>
                    <p class="text-base leading-6 text-slate-600">{{ $room->requires_approval ? 'Pengajuan memerlukan persetujuan PIC.' : 'Ruangan tidak memerlukan persetujuan PIC.' }}</p>
                    <details class="border-t border-slate-100 pt-3 text-base text-slate-600">
                        <summary class="cursor-pointer py-2 font-semibold text-primary">Fasilitas ruangan</summary>
                        <ul class="mt-2 flex flex-wrap gap-2">@forelse($room->facilities as $facility)<li class="break-words rounded-lg bg-background px-3 py-1.5">{{ $facility->name }}</li>@empty<li>Fasilitas belum dicantumkan.</li>@endforelse</ul>
                    </details>
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
                <section aria-labelledby="booking-schedule-title" class="border-b border-slate-200">
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
                        {{-- <p class="text-base leading-6 text-slate-600">Diperbarui otomatis &middot; {{ $scheduleUpdatedAt }} WIB.</p> --}}
                    </div>
                </section>
                <div class="px-5 pt-5 sm:px-6"><h2 class="text-lg font-bold text-primaryDark">Detail Pertemuan</h2></div>
                <form wire:submit="submitBooking" method="POST" action="{{ route('rooms.book', $room) }}" class="workspace-form">
                    @csrf
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <p class="text-sm leading-6 text-slate-600 sm:col-span-2">Rapat dimulai minimal <strong>{{ config('booking.minimum_notice_hours') }} jam</strong> setelah pengajuan dan selesai pada tanggal yang sama.</p>
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
                    @if($errors->any())<div role="alert" class="text-base text-danger sm:col-span-2">Rencana belum lolos pemeriksaan. Periksa pesan pada kolom yang perlu diperbaiki.@error('room')<p>{{ $message }}</p>@enderror</div>@endif
                    @if($checked && ! $unavailableReason)
                        <div x-show="!changed" role="status" class="rounded-xl border border-secondaryLight bg-background p-4 text-base leading-6 text-primaryDark sm:col-span-2">Waktu dan isian lolos pemeriksaan saat ini. <strong>Ruangan belum dipesan.</strong> Klik tombol booking di bawah; ketersediaan akan diperiksa ulang saat dikirim.</div>
                    @endif
                    <p wire:offline class="text-danger sm:col-span-2">Koneksi terputus. Isian tetap di halaman ini; sambungkan kembali sebelum memeriksa rencana.</p>
                    <div class="workspace-form-actions">
                        <button type="button" wire:click="checkPlan" wire:loading.attr="disabled" @disabled($unavailableReason) class="workspace-button-secondary disabled:cursor-not-allowed disabled:opacity-50">Periksa Rencana</button>
                        <button type="submit" wire:loading.attr="disabled" @disabled($unavailableReason) class="workspace-button disabled:cursor-not-allowed disabled:opacity-50">{{ $room->requires_approval ? 'Ajukan Booking' : 'Booking Sekarang' }}</button>
                        <span wire:loading role="status" class="text-base text-primary">Memeriksa rencana...</span>
                    </div>
                    {{-- <p id="submit-help">Ketersediaan diperiksa kembali saat booking dikirim.</p> --}}
                    <noscript><p>Aktifkan JavaScript untuk memeriksa dan mengirim booking.</p></noscript>
                </form>
            </div>
        </div>
    </div>
</div>
