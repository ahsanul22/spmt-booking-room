<form wire:submit="applyFilters" data-listing-filter class="mb-5 flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-surface p-5" aria-label="Filter daftar">
    @if($kind === 'whatsapp')
        <x-workspace-field type="select" name="event" label="Jenis pesan" wire:model="event">
            <option value="">Semua jenis</option><option value="approval_request">Pengajuan approval</option><option value="consumption">Konsumsi</option>
        </x-workspace-field>
    @else
        @can('access-admin')
            <x-workspace-field type="select" name="branch" label="Kantor / Cabang" wire:model.live="branch">
                <option value="">Semua lokasi</option>
                @foreach($branches as $location)<option value="{{ $location->id }}">{{ $location->name }}</option>@endforeach
            </x-workspace-field>
        @endcan
        @if($kind === 'report')
            <x-workspace-field type="month" name="month" label="Bulan rapat" required min="1000-01" max="9998-12" wire:model="month" />
            <x-workspace-field type="select" name="room" label="Ruangan" wire:model="room">
                <option value="">Semua ruangan dalam cakupan Anda</option>
                @foreach($rooms as $locationRoom)<option value="{{ $locationRoom->id }}">{{ $locationRoom->branch?->name }} · {{ $locationRoom->name }}{{ $locationRoom->is_active ? '' : ' - Nonaktif' }}</option>@endforeach
            </x-workspace-field>
        @endif
    @endif
    <button type="submit" class="workspace-button-secondary" wire:loading.attr="disabled">Tampilkan</button>
</form>
