<x-workspace-panel title="Informasi Dasar" description="Kolom bertanda * wajib diisi.">
<div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
<x-workspace-field name="name" label="Nama Ruangan" type="text" required maxlength="255" :value="old('name', data_get($room ?? null, 'name'))" />
<x-workspace-field name="code" label="Kode Ruangan" type="text" maxlength="255" :value="old('code', data_get($room ?? null, 'code'))" />
<x-workspace-field type="select" name="floor_id" label="Lantai" required>
    <option value="">Pilih Lantai</option>
    @forelse($floors ?? [] as $option)
        <option value="{{ $option->id }}" @selected((string) (old('floor_id', data_get($room ?? null, 'floor_id'))) === (string) $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' - Nonaktif' }}</option>
    @empty
        <option disabled>Belum ada pilihan tersedia.</option>
    @endforelse
</x-workspace-field>
<x-workspace-field name="capacity" label="Kapasitas" type="number" required max="2147483647" step="1" :value="old('capacity', data_get($room ?? null, 'capacity'))" min="0" />
<div class="sm:col-span-2"><x-workspace-field type="textarea" name="description" label="Deskripsi" maxlength="5000" :value="old('description', data_get($room ?? null, 'description'))" /></div>
</div>
</x-workspace-panel>
<fieldset class="min-w-0 rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm sm:p-6" aria-describedby="facilities-hint{{ $errors->has('facility_ids') || $errors->has('facility_ids.*') ? ' facilities-error' : '' }}">
    <legend class="px-2 text-lg font-bold text-primaryDark">Fasilitas</legend>
    <p id="facilities-hint" class="mb-5 text-sm leading-6 text-slate-600">Pilih perlengkapan yang dimiliki ruangan. Kosongkan semua pilihan untuk melepas seluruh fasilitas.</p>
    <div class="grid gap-3 sm:grid-cols-2">
    @forelse($facilities ?? [] as $facility)
        <label class="flex min-h-[52px] cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 text-sm text-slate-700 transition hover:border-secondary hover:bg-secondaryLight/10">
        <input class="mt-0.5 h-4 w-4 shrink-0 accent-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" type="checkbox" name="facility_ids[]" value="{{ $facility->id }}" @checked(in_array($facility->id, (array) old('facility_ids', session()->hasOldInput() ? [] : ($selectedFacilityIds ?? []))))><span class="min-w-0 break-words">{{ $facility->name }}{{ $facility->is_active ? '' : ' - Nonaktif' }}</span></label>
    @empty
        <p>Belum ada fasilitas yang dapat dipilih.</p>
    @endforelse
    </div>
    @if($errors->has('facility_ids') || $errors->has('facility_ids.*'))
        <div id="facilities-error" class="mt-3 space-y-1 text-sm text-danger">
            @foreach(array_merge($errors->get('facility_ids'), $errors->get('facility_ids.*')) as $message)
                @foreach((array) $message as $detail)<p>{{ $detail }}</p>@endforeach
            @endforeach
        </div>
    @endif
</fieldset>
<x-workspace-panel title="Akses dan Operasional" description="Atur penggunaan ruangan dan kebutuhan persetujuan.">
<div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
<x-workspace-field type="select" name="access_type" label="Jenis Akses" required>
    <option value="">Pilih Jenis Akses</option>
    <option value="all" @selected(old('access_type', data_get($room ?? null, 'access_type', 'all')) === 'all')>Semua pegawai</option>
    <option value="restricted" @selected(old('access_type', data_get($room ?? null, 'access_type', 'all')) === 'restricted')>Unit tertentu (Restricted)</option>
</x-workspace-field>

<x-workspace-field type="select" name="requires_approval" label="Perlu Approval" required>
    <option value="">Pilih Kebutuhan Approval</option>
    <option value="1" @selected((string) old('requires_approval', isset($room) ? (int) $room->requires_approval : 0) === '1')>Ya</option>
    <option value="0" @selected((string) old('requires_approval', isset($room) ? (int) $room->requires_approval : 0) === '0')>Tidak</option>
</x-workspace-field>
<x-workspace-field type="select" name="status" label="Status Operasional" required>
    <option value="">Pilih Status Operasional</option>
    <option value="available" @selected(old('status', data_get($room ?? null, 'status', 'available')) === 'available')>Operasional</option>
    <option value="maintenance" @selected(old('status', data_get($room ?? null, 'status', 'available')) === 'maintenance')>Dalam perawatan</option>
    <option value="unavailable" @selected(old('status', data_get($room ?? null, 'status', 'available')) === 'unavailable')>Tidak dapat digunakan</option>
</x-workspace-field>
<x-workspace-field type="select" name="is_active" label="Status Aktif" required>
    <option value="">Pilih Status Aktif</option>
    <option value="1" @selected((string) old('is_active', isset($room) ? (int) $room->is_active : 1) === '1')>Aktif</option>
    <option value="0" @selected((string) old('is_active', isset($room) ? (int) $room->is_active : 1) === '0')>Nonaktif</option>
</x-workspace-field>
<p class="rounded-xl bg-background p-4 text-xs leading-6 text-slate-600 sm:col-span-2">PIC dan unit akses dikelola melalui halaman detail setelah perubahan disimpan. Status operasional tidak menunjukkan ketersediaan jadwal.</p>
</div>
</x-workspace-panel>
