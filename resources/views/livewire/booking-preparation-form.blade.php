<div wire:poll.30s>
    <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
        <div><p class="mb-2 text-xs font-semibold text-primary">Langkah 2 dari 2 &middot; Detail pertemuan</p><h1 class="text-3xl font-bold text-primaryDark">Booking Ruangan</h1><p class="mt-3 text-sm text-slate-600">Ruangan sudah dipilih. Lengkapi jadwal dan agenda Anda.</p></div>
        <a href="{{ route('rooms.index') }}" class="workspace-button-secondary">Kembali ke pilihan ruangan</a>
    </div>
    <div class="workspace-notice" role="note">Ruangan tanpa approval langsung dikonfirmasi setelah validasi. Ruangan dengan approval menunggu keputusan PIC.</div>
    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(260px,360px)]">
        <div class="min-w-0 lg:order-2">
            <x-workspace-panel title="Ruangan pilihan">
                <div class="space-y-4 p-5 sm:p-6">
                    <div><h2 class="break-words text-xl font-bold text-primaryDark">{{ $room->name }}</h2><p class="mt-2 text-sm text-slate-600">{{ $room->floor?->name ?? 'Lantai belum ditentukan' }}</p></div>
                    <p class="text-sm leading-6 text-slate-600">{{ $room->requires_approval ? 'Pengajuan memerlukan persetujuan PIC.' : 'Ruangan tidak memerlukan persetujuan PIC.' }}</p>
                    <ul class="flex flex-wrap gap-2">@forelse($room->facilities as $facility)<li class="break-words rounded-lg bg-background px-3 py-2 text-xs text-slate-600">{{ $facility->name }}</li>@empty<li class="text-sm text-slate-600">Fasilitas belum dicantumkan.</li>@endforelse</ul>
                    <p class="border-t border-slate-100 pt-4 text-xs leading-6 text-slate-600">Mengganti ruangan akan membuka form baru. Isian di halaman ini belum disimpan.</p>
                    <a href="{{ route('rooms.index') }}" class="font-semibold text-primary hover:underline">Ganti ruangan</a>
                </div>
            </x-workspace-panel>
        </div>
        <div class="min-w-0 lg:order-1">
            @if($unavailableReason)
                <div role="alert" class="mb-5 rounded-xl border border-danger/30 bg-surface p-5 text-sm leading-6 text-danger">{{ $unavailableReason }} <a href="{{ route('rooms.index') }}" class="font-semibold underline">Pilih ruangan lain</a></div>
            @endif
            <div class="mb-6">
                <x-workspace-panel title="Jadwal Ruangan Ini" description="Periksa jam yang sudah dipesan, lalu isi waktu pertemuan di bawah.">
                    <div class="space-y-4 p-5 sm:p-6">
                        <x-workspace-field wire:model.live="date" name="date" label="Tanggal booking dan jadwal (WIB)" type="date" :min="$earliestStart->format('Y-m-d')" :value="$date" required />
                        @if($scheduleDateValid)
                            @include('shared.schedule-slots', ['slots' => $daySlots])
                            <a href="{{ route('schedule.index', ['room_id' => $room->id, 'date' => $date]) }}" target="_blank" rel="noopener" class="inline-block text-sm font-semibold text-primary hover:underline">Lihat kalender ruangan (tab baru)</a>
                        @else
                            <p class="text-sm text-slate-600">Pilih tanggal yang valid untuk melihat jadwal ruangan.</p>
                        @endif
                        <p class="text-xs leading-6 text-slate-600">Diperbarui {{ $scheduleUpdatedAt }} WIB. Jadwal diperbarui otomatis; slot tetap diperiksa lagi saat Anda mengirim booking.</p>
                    </div>
                </x-workspace-panel>
            </div>
            <x-workspace-panel title="Detail Pertemuan" description="Isi tanggal, waktu, dan agenda. Kolom bertanda * wajib diisi.">
                <form x-data="{ changed: false }" x-on:input="changed = true" x-on:plan-checked.window="changed = false" wire:submit="submitBooking" method="POST" action="{{ route('my-bookings.create') }}" class="workspace-form">
                    @csrf
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <div class="rounded-xl bg-background p-4 text-sm leading-6 text-primaryDark sm:col-span-2">
                        <p>Rapat dimulai minimal <strong>{{ config('booking.minimum_notice_hours') }} jam</strong> setelah waktu pengajuan.</p>
                        <p class="mt-1 text-xs text-slate-600">Waktu paling awal saat halaman diperbarui: {{ $earliestStart->format('d/m/Y H:i') }} WIB. Batas dihitung ulang saat pemeriksaan dan saat booking dikirim.</p>
                    </div>
                    <div class="grid gap-5 sm:col-span-2 sm:grid-cols-2">
                        <x-workspace-field wire:model="start_time" name="start_time" label="Jam Mulai (WIB)" type="time" required />
                        <x-workspace-field wire:model="end_time" name="end_time" label="Jam Selesai (WIB)" type="time" required />
                    </div>
                    <p>Jam selesai harus setelah jam mulai pada tanggal yang sama. Rapat lintas hari belum didukung.</p>
                    <div class="sm:col-span-2"><x-workspace-field wire:model="agenda" name="agenda" label="Judul / Agenda Rapat" maxlength="255" required /></div>
                    <div class="sm:col-span-2"><x-workspace-field wire:model="notes" name="notes" label="Catatan (opsional)" type="textarea" maxlength="2000" /></div>
                    @if($errors->any())<div role="alert" class="text-sm text-danger sm:col-span-2">Rencana belum lolos pemeriksaan. Periksa pesan pada kolom yang perlu diperbaiki.@error('room')<p>{{ $message }}</p>@enderror</div>@endif
                    @if($checked && ! $unavailableReason)
                        <div x-show="!changed" role="status" class="rounded-xl border border-secondaryLight bg-background p-4 text-sm leading-6 text-primaryDark sm:col-span-2">Waktu dan isian lolos pemeriksaan saat ini. <strong>Ruangan belum dipesan.</strong> Klik tombol booking di bawah; ketersediaan akan diperiksa ulang saat dikirim.</div>
                    @endif
                    <p wire:offline class="text-danger sm:col-span-2">Koneksi terputus. Isian tetap di halaman ini; sambungkan kembali sebelum memeriksa rencana.</p>
                    <div class="workspace-form-actions">
                        <button type="button" wire:click="checkPlan" wire:loading.attr="disabled" @disabled($unavailableReason) class="workspace-button-secondary disabled:cursor-not-allowed disabled:opacity-50">Periksa Rencana</button>
                        <button type="submit" wire:loading.attr="disabled" @disabled($unavailableReason) class="workspace-button disabled:cursor-not-allowed disabled:opacity-50">{{ $room->requires_approval ? 'Ajukan Booking' : 'Booking Sekarang' }}</button>
                        <span wire:loading role="status" class="text-sm text-primary">Memeriksa rencana...</span>
                    </div>
                    <p id="submit-help">Menekan tombol booking akan menyimpan pengajuan. Tunggu halaman detail untuk memastikan pengiriman berhasil.</p>
                    <noscript><p>Aktifkan JavaScript untuk memeriksa dan mengirim booking.</p></noscript>
                </form>
            </x-workspace-panel>
        </div>
    </div>
</div>
