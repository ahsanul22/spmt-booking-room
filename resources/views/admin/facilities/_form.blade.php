<x-workspace-field name="name" label="Nama" type="text" required maxlength="255" :value="old('name', data_get($facility ?? null, 'name'))" />
<div class="sm:col-span-2"><x-workspace-field type="textarea" name="description" label="Deskripsi" maxlength="5000" :value="old('description', data_get($facility ?? null, 'description'))" /></div>
<x-workspace-field type="select" name="is_active" label="Status Aktif" required>
    <option value="">Pilih Status Aktif</option>
    <option value="1" @selected((string) old('is_active', isset($facility) ? (int) $facility->is_active : 1) === '1')>Aktif</option>
    <option value="0" @selected((string) old('is_active', isset($facility) ? (int) $facility->is_active : 1) === '0')>Nonaktif</option>
</x-workspace-field>
