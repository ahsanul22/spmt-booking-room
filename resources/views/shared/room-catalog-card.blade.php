            <article wire:key="room-{{ $room->id }}" class="relative flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm transition hover:border-secondaryLight hover:shadow-md">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-background/50 px-6 py-5">
                    <span class="inline-flex rounded-xl border border-secondaryLight/50 bg-surface p-3 text-primary"><x-schedule-icon name="room" class="h-7 w-7" /></span>
                    <span @class(['rounded-full border px-3 py-1 text-sm font-medium', 'border-slate-200 bg-surface text-slate-600' => $room->is_active && $room->status === 'available', 'border-danger/20 bg-danger/5 text-danger' => ! $room->is_active || $room->status !== 'available'])>
                        {{ ! $room->is_active ? 'Nonaktif' : match ($room->status) { 'available' => 'Operasional', 'maintenance' => 'Dalam perawatan', default => 'Tidak operasional' } }}
                    </span>
                </div>
                <div class="flex flex-1 flex-col p-6">
                    <p class="break-words text-sm font-medium text-slate-500">{{ $room->floor?->name ?? 'Lantai belum ditentukan' }}</p>
                    <h3 class="mt-2 break-words text-xl font-bold text-primaryDark">@if($public){{ $room->name }}@else<a class="hover:underline" href="{{ route('rooms.show', $room) }}">{{ $room->name }}</a>@endif</h3>
                    @unless($public)
                    <p class="mt-4 break-words text-base leading-6 text-slate-600">{{ $room->description ?: 'Deskripsi ruangan belum ditambahkan.' }}</p>
                    @endunless
                    <div class="mt-5">
                        <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Fasilitas</h4>
                        <ul class="mt-2 flex flex-wrap gap-2">
                            @forelse($room->facilities as $facility)
                                <li class="max-w-full break-words rounded-md bg-background px-2.5 py-1 text-sm text-slate-600">{{ $facility->name }}</li>
                            @empty
                                <li class="text-sm text-slate-500">Fasilitas belum dicantumkan.</li>
                            @endforelse
                        </ul>
                    </div>
                    @unless($public)
                    <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm leading-5">
                        <div><dt class="text-slate-500">Akses ruangan</dt><dd class="mt-1 font-medium text-primaryDark">{{ $room->access_type === 'restricted' ? 'Unit tertentu' : 'Semua pegawai' }}</dd></div>
                        <div><dt class="text-slate-500">Persetujuan</dt><dd class="mt-1 font-medium text-primaryDark">{{ $room->requires_approval ? 'Memerlukan PIC' : 'Tanpa approval PIC' }}</dd></div>
                    </dl>
                    @endunless
                    <div class="mt-auto pt-5">
                        @if($managed ?? false)
                            <a href="{{ route('schedule.index', ['room_id' => $room->id]) }}" class="workspace-button-secondary w-full" aria-label="Lihat jadwal {{ $room->name }}"><x-schedule-icon name="calendar" class="h-4 w-4" />Lihat Jadwal</a>
                        @elseif($public)
                            @if($room->status === 'available')
                                <a href="{{ route('login', ['room' => $room->id]) }}" class="workspace-button w-full">Login untuk Booking<span class="sr-only"> {{ $room->name }}</span></a>
                            @else
                                <p class="text-base text-slate-600">Ruangan sedang tidak operasional.</p>
                            @endif
                        @else
                        @can('access-employee')
                            @if($reasons[$room->id])
                                <p class="mb-3 text-sm leading-5 text-slate-600">{{ $reasons[$room->id] }}</p>
                                <button type="button" disabled class="w-full cursor-not-allowed rounded-xl bg-background px-4 py-3 text-base text-slate-600">Belum dapat dipilih</button>
                            @else
                                <a href="{{ route('rooms.book', $room) }}" aria-label="Pilih ruangan {{ $room->name }}" class="workspace-button w-full after:absolute after:inset-0 after:rounded-2xl focus-visible:after:outline focus-visible:after:outline-2 focus-visible:after:outline-primary">Pilih Ruangan<x-schedule-icon name="arrow" class="h-4 w-4" /></a>
                            @endif
                        @endcan
                        @endif
                    </div>
                </div>
            </article>
