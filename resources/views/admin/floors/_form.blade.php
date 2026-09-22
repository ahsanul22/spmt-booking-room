<x-field name="floor_number" label="Nomor Lantai" type="number" required step="1" min="-2147483648" max="2147483647" :value="old('floor_number', data_get($floor ?? null, 'floor_number'))" />
<x-field name="name" label="Nama" type="text" required maxlength="255" :value="old('name', data_get($floor ?? null, 'name'))" />
<x-textarea name="description" label="Deskripsi" maxlength="5000" :value="old('description', data_get($floor ?? null, 'description'))" />
<x-select name="is_active" label="Status Aktif" required>
    <option value="">Pilih Status Aktif</option>
    <option value="1" @selected((string) old('is_active', isset($floor) ? (int) $floor->is_active : 1) === '1')>Aktif</option>
    <option value="0" @selected((string) old('is_active', isset($floor) ? (int) $floor->is_active : 1) === '0')>Nonaktif</option>
</x-select>
