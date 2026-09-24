<x-workspace-field name="name" label="Nama Unit" type="text" required maxlength="255" :value="old('name', data_get($unit ?? null, 'name'))" />
<x-workspace-field type="select" name="type" label="Type" required>
    <option value="">Pilih Type</option>
    <option value="directorate" @selected(old('type', data_get($unit ?? null, 'type')) === 'directorate')>directorate</option>
    <option value="division" @selected(old('type', data_get($unit ?? null, 'type')) === 'division')>division</option>
    <option value="department" @selected(old('type', data_get($unit ?? null, 'type')) === 'department')>department</option>
</x-workspace-field>
<x-workspace-field type="select" name="parent_id" label="Parent Unit">
    <option value="">Tanpa parent (Directorate)</option>
    @forelse($units ?? [] as $option)
        <option value="{{ $option->id }}" @selected((string) (old('parent_id', data_get($unit ?? null, 'parent_id'))) === (string) $option->id)>{{ $option->name }} ({{ $option->type }}){{ $option->is_active ? '' : ' — Nonaktif' }}</option>
    @empty
        <option disabled>Belum ada pilihan tersedia.</option>
    @endforelse
</x-workspace-field>
<x-workspace-field type="select" name="is_active" label="Status Aktif" required>
    <option value="">Pilih Status Aktif</option>
    <option value="1" @selected((string) old('is_active', isset($unit) ? (int) $unit->is_active : 1) === '1')>Aktif</option>
    <option value="0" @selected((string) old('is_active', isset($unit) ? (int) $unit->is_active : 1) === '0')>Nonaktif</option>
</x-workspace-field>

<p>Directorate tanpa parent; Division wajib memiliki parent Directorate; Department wajib memiliki parent Division.</p>
