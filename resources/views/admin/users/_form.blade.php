<x-workspace-field name="name" label="Nama" type="text" autocomplete="name" required maxlength="255" :value="old('name', data_get($userRecord ?? null, 'name'))" />
<x-workspace-field name="email" label="Email" type="email" autocomplete="email" required maxlength="255" :value="old('email', data_get($userRecord ?? null, 'email'))" />
<p>Password awal dan konfirmasi diperlukan saat tambah user; pada edit, kosongkan bila tidak diubah.</p>
<x-workspace-field name="password" minlength="8" hint="Minimal 8 karakter. Kosongkan saat edit jika password tetap." label="Password Awal / Baru" type="password" :required="! ($editing ?? false)" autocomplete="new-password" />
<x-workspace-field name="password_confirmation" label="Konfirmasi Password" type="password" :required="! ($editing ?? false)" autocomplete="new-password" />
<x-workspace-field type="select" name="organizational_unit_id" label="Unit Kerja">
    <option value="">Tanpa Unit Kerja</option>
    @forelse($units ?? [] as $option)
        <option value="{{ $option->id }}" @selected((string) (old('organizational_unit_id', data_get($userRecord ?? null, 'organizational_unit_id'))) === (string) $option->id)>{{ $option->name }} ({{ $option->type }}){{ $option->is_active ? '' : ' - Nonaktif' }}</option>
    @empty
        <option disabled>Belum ada pilihan tersedia.</option>
    @endforelse
</x-workspace-field>
<x-workspace-field type="select" name="role" label="Role" required>
    <option value="">Pilih Role</option>
    <option value="user" @selected(old('role', data_get($userRecord ?? null, 'role')) === 'user')>user</option>
    <option value="room_pic" @selected(old('role', data_get($userRecord ?? null, 'role')) === 'room_pic')>room_pic</option>
    <option value="super_admin" @selected(old('role', data_get($userRecord ?? null, 'role')) === 'super_admin')>super_admin</option>
</x-workspace-field>
<x-workspace-field type="select" name="is_active" label="Status Aktif" required>
    <option value="">Pilih Status Aktif</option>
    <option value="1" @selected((string) old('is_active', isset($userRecord) ? (int) $userRecord->is_active : 1) === '1')>Aktif</option>
    <option value="0" @selected((string) old('is_active', isset($userRecord) ? (int) $userRecord->is_active : 1) === '0')>Nonaktif</option>
</x-workspace-field>
