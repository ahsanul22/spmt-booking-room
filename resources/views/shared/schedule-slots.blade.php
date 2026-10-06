@if($slots->isEmpty())
    <div class="rounded-xl border border-dashed border-slate-300 bg-background/50 p-6 text-center"><x-schedule-icon name="calendar" class="mx-auto h-8 w-8 text-secondary" /><h3 class="mt-3 font-semibold text-primaryDark">Belum ada jadwal ruangan.</h3><p class="mt-2 text-base leading-6 text-slate-600">Tidak ada booking pada tanggal dan ruangan pilihan ini. Ketersediaan tetap diperiksa saat booking dikirim.</p></div>
@else
    <ol class="space-y-3" aria-label="Jam penggunaan ruangan">
        @foreach($slots as $slot)
            <li wire:key="slot-{{ $slot['id'] }}" @class(['rounded-xl border-l-4 bg-background/60 p-4', 'border-warning' => $slot['state'] === 'pending', 'border-danger' => $slot['state'] === 'ongoing', 'border-primary' => $slot['state'] === 'scheduled', 'border-slate-300' => $slot['state'] === 'elapsed'])>
                <div class="flex flex-wrap items-center justify-between gap-3"><p class="text-base font-bold tabular-nums text-primaryDark">{{ $slot['start'] }} &ndash; {{ $slot['end'] }} <span class="text-sm font-normal">WIB</span></p><x-schedule-status :state="$slot['state']" :label="$slot['label']" /></div>
                <p class="mt-2 break-words text-base font-semibold text-text">{{ $slot['room_name'] }}</p>
                @if(! $slot['room_operational'])<p class="mt-2 text-sm text-danger">Ruangan saat ini tidak operasional. Jadwal tersimpan tetap ditampilkan.</p>@endif
                @if($slot['state'] === 'pending')<p class="mt-2 text-sm leading-5 text-slate-600">Slot ditahan selama menunggu persetujuan PIC.</p>@endif
            </li>
        @endforeach
    </ol>
@endif
