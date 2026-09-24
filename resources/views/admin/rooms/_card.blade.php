<article class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-surface shadow-sm transition-shadow hover:shadow-md" aria-labelledby="room-title-{{ $item->id }}">
    <div class="relative border-b border-slate-100 bg-gradient-to-br from-secondaryLight/30 via-surface to-surface p-5 sm:p-6">
        <div aria-hidden="true" class="pointer-events-none absolute right-0 top-0 h-24 w-24 rounded-bl-full border-b border-l border-secondaryLight/40 bg-secondaryLight/10"></div>
        <div class="relative flex items-start justify-between gap-3">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primaryDark text-white shadow-sm"><x-schedule-icon name="room" class="h-6 w-6" /></span>
            <span @class(['inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold', 'border-secondaryLight bg-surface text-primaryDark' => $item->is_active, 'border-slate-200 bg-background text-slate-600' => ! $item->is_active])>
                <span @class(['h-1.5 w-1.5 rounded-full', 'bg-primary' => $item->is_active, 'bg-muted' => ! $item->is_active]) aria-hidden="true"></span>
                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
        <p class="relative mt-5 break-words text-[11px] font-semibold uppercase tracking-wider text-slate-500">Kode ruang · {{ $item->code ?? 'Belum ditentukan' }}</p>
        <h3 id="room-title-{{ $item->id }}" class="relative mt-1.5 break-words text-xl font-bold leading-7 tracking-tight text-primaryDark">
            <a href="{{ route('admin.rooms.show', $item->id) }}" class="hover:underline">{{ $item->name }}</a>
        </h3>
        <dl class="relative mt-5 grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-4 border-t border-secondaryLight/40 pt-4">
            <div><dt class="text-xs text-slate-500">Lantai</dt><dd class="mt-2 break-words text-sm font-semibold text-primaryDark">{{ $item->floor?->name ?? 'Belum ditentukan' }}</dd></div>
            <div class="border-l border-secondaryLight/50 pl-4"><dt class="text-xs text-slate-500">Kapasitas</dt><dd class="mt-1 text-primaryDark"><span class="text-2xl font-bold tabular-nums">{{ $item->capacity }}</span> <span class="text-xs font-medium text-slate-600">orang</span></dd></div>
        </dl>
    </div>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div @class(['flex flex-wrap items-center justify-between gap-x-3 gap-y-1 rounded-lg px-3 py-2.5 text-xs', 'bg-secondaryLight/20 text-primaryDark' => $item->status === 'available', 'bg-background text-slate-700' => $item->status === 'maintenance', 'bg-danger/5 text-danger' => $item->status === 'unavailable'])>
            <span>Status operasional</span>
            <span class="font-semibold" title="{{ $item->status }}">{{ ['available' => 'Operasional', 'maintenance' => 'Dalam perawatan', 'unavailable' => 'Tidak dapat digunakan'][$item->status] ?? $item->status }}</span>
        </div>
        <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-xs text-slate-500">Akses ruangan</dt><dd class="mt-1.5 font-semibold text-primaryDark" title="{{ $item->access_type }}">{{ $item->access_type === 'all' ? 'Semua pegawai' : 'Unit tertentu' }}</dd></div>
            <div><dt class="text-xs text-slate-500">Perlu approval</dt><dd class="mt-1.5 font-semibold text-primaryDark">{{ $item->requires_approval ? 'Ya, melalui PIC' : 'Tidak' }}</dd></div>
        </dl>

        <div class="mb-5 mt-5 border-t border-slate-100 pt-4">
            <div class="mb-3 flex items-center justify-between gap-3"><h4 class="text-xs font-semibold text-slate-600">Fasilitas</h4><span class="text-xs tabular-nums text-slate-500">{{ $item->facilities->count() }} fasilitas</span></div>
            <div class="flex flex-wrap gap-2">
                @forelse($item->facilities->take(3) as $facility)
                    <span class="max-w-full break-words rounded-lg border border-slate-200/80 bg-background/50 px-2.5 py-1.5 text-xs leading-5 text-slate-600">{{ $facility->name }}</span>
                @empty
                    <p class="text-xs leading-5 text-slate-500">Belum ada fasilitas.</p>
                @endforelse
            </div>
            @if($item->facilities->count() > 3)
                <details class="mt-2">
                    <summary class="w-fit cursor-pointer rounded py-2 text-xs font-semibold text-primary hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Lihat {{ $item->facilities->count() - 3 }} fasilitas lainnya<span class="sr-only"> untuk {{ $item->name }}</span></summary>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @foreach($item->facilities->skip(3) as $facility)
                            <span class="max-w-full break-words rounded-lg border border-slate-200/80 bg-background/50 px-2.5 py-1.5 text-xs leading-5 text-slate-600">{{ $facility->name }}</span>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>

        <div class="mt-auto grid grid-cols-[minmax(0,1fr)_auto] gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('admin.rooms.show', $item->id) }}" class="flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primaryDark" aria-label="Detail {{ $item->name }}">Detail Ruangan<x-schedule-icon name="arrow" class="h-4 w-4 shrink-0" /></a>
            <a href="{{ route('admin.rooms.edit', $item->id) }}" class="flex min-h-[44px] items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-primary transition hover:bg-background" aria-label="Edit {{ $item->name }}">Edit</a>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 border-t border-slate-100 bg-background/40 px-5 py-2 sm:px-6">
        <div class="flex flex-wrap items-center gap-x-4 text-xs font-semibold text-primary">
            <a class="inline-flex min-h-[44px] items-center hover:underline" href="{{ route('admin.rooms.pics', $item->id) }}">Kelola PIC<span class="sr-only"> {{ $item->name }}</span></a>
            <a class="inline-flex min-h-[44px] items-center hover:underline" href="{{ route('admin.rooms.access', $item->id) }}">Kelola Akses<span class="sr-only"> {{ $item->name }}</span></a>
        </div>
        <div class="room-card-status">@include('admin.rooms._status', ['room' => $item])</div>
    </div>
</article>
