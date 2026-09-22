<h2>Informasi Dasar</h2>
<x-field name="name" label="Nama Ruangan" type="text" required maxlength="255" :value="old('name', data_get($room ?? null, 'name'))" />
<x-field name="code" label="Kode Ruangan" type="text" maxlength="255" :value="old('code', data_get($room ?? null, 'code'))" />
<x-select name="floor_id" label="Lantai" required>
    <option value="">Pilih Lantai</option>
    @forelse($floors ?? [] as $option)
        <option value="{{ $option->id }}" @selected((string) (old('floor_id', data_get($room ?? null, 'floor_id'))) === (string) $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' - Nonaktif' }}</option>
    @empty
        <option disabled>Belum ada pilihan tersedia.</option>
    @endforelse
</x-select>
<x-field name="capacity" label="Kapasitas" type="number" required max="2147483647" step="1" :value="old('capacity', data_get($room ?? null, 'capacity'))" min="0" />
<x-textarea name="description" label="Deskripsi" maxlength="5000" :value="old('description', data_get($room ?? null, 'description'))" />
<fieldset>
    <legend>Fasilitas</legend>
    @forelse($facilities ?? [] as $facility)
        <label>
        <input type="checkbox" name="facility_ids[]" value="{{ $facility->id }}" @checked(in_array($facility->id, (array) old('facility_ids', session()->hasOldInput() ? [] : ($selectedFacilityIds ?? []))))> {{ $facility->name }}{{ $facility->is_active ? '' : ' - Nonaktif' }}</label>
    @empty
        <p>Belum ada fasilitas yang dapat dipilih.</p>
    @endforelse
</fieldset>
<x-select name="access_type" label="Access Type" required>
    <option value="">Pilih Access Type</option>
    <option value="all" @selected(old('access_type', data_get($room ?? null, 'access_type', 'all')) === 'all')>All</option>
    <option value="restricted" @selected(old('access_type', data_get($room ?? null, 'access_type', 'all')) === 'restricted')>Restricted</option>
</x-select>
<p>PIC dan unit restricted diatur melalui Kelola PIC dan Kelola Akses setelah ruangan disimpan.</p>
<x-select name="requires_approval" label="Requires Approval" required>
    <option value="">Pilih Requires Approval</option>
    <option value="1" @selected((string) old('requires_approval', isset($room) ? (int) $room->requires_approval : 0) === '1')>Ya</option>
    <option value="0" @selected((string) old('requires_approval', isset($room) ? (int) $room->requires_approval : 0) === '0')>Tidak</option>
</x-select>
<x-select name="status" label="Status Operasional" required>
    <option value="">Pilih Status Operasional</option>
    <option value="available" @selected(old('status', data_get($room ?? null, 'status', 'available')) === 'available')>available</option>
    <option value="maintenance" @selected(old('status', data_get($room ?? null, 'status', 'available')) === 'maintenance')>maintenance</option>
    <option value="unavailable" @selected(old('status', data_get($room ?? null, 'status', 'available')) === 'unavailable')>unavailable</option>
</x-select>
<x-select name="is_active" label="Status Aktif" required>
    <option value="">Pilih Status Aktif</option>
    <option value="1" @selected((string) old('is_active', isset($room) ? (int) $room->is_active : 1) === '1')>Aktif</option>
    <option value="0" @selected((string) old('is_active', isset($room) ? (int) $room->is_active : 1) === '0')>Nonaktif</option>
</x-select>
