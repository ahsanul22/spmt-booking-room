<input type="hidden" name="_section" value="room">
@php($hasRoomInput = old('_section') === 'room' || (! old('_section') && array_key_exists('name', session()->getOldInput())))
<div @class(['grid items-start gap-6', 'xl:grid-cols-2' => $roomManagement ?? false])>
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
<div class="sm:col-span-2"><x-workspace-field type="textarea" name="description" label="Deskripsi" maxlength="5000" rows="3" :value="old('description', data_get($room ?? null, 'description'))" /></div>
</div>
</x-workspace-panel>
<x-workspace-panel title="Akses dan Operasional" description="Atur penggunaan ruangan dan kebutuhan persetujuan.">
<div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
<x-workspace-field type="select" name="access_type" label="Jenis Akses" required>
    <option value="">Pilih Jenis Akses</option>
    <option value="all" @selected(old('access_type', data_get($room ?? null, 'access_type', 'all')) === 'all')>Semua pegawai</option>
    <option value="restricted" @selected(old('access_type', data_get($room ?? null, 'access_type', 'all')) === 'restricted')>Unit tertentu (Restricted)</option>
</x-workspace-field>

<x-workspace-field type="select" name="branch_id" label="Kantor / Cabang" required>
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $room->branch_id ?? $branches->sortBy('id')->first()?->id) === (string) $branch->id)>{{ $branch->name }}</option>
    @endforeach
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
<p class="rounded-xl bg-background p-4 text-sm leading-6 text-slate-600 sm:col-span-2">Status operasional tidak menunjukkan ketersediaan jadwal. Pengaturan PIC dan unit akses disimpan melalui formulir masing-masing.</p>
</div>
</x-workspace-panel>
</div>
<fieldset id="booking-options" class="scroll-mt-6 min-w-0 rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm sm:p-6" aria-describedby="booking-options-hint">
    <legend class="sr-only">Opsi Lanjutan Booking</legend>
    <h2 class="text-lg font-bold text-primaryDark">Opsi Lanjutan Booking</h2>
    <p id="booking-options-hint" class="mt-2 text-base leading-6 text-slate-600">Tentukan apakah pegawai boleh mengisi jumlah peserta dan meminta konsumsi saat booking ruangan ini.</p>
    <input type="hidden" name="allows_meeting_preparation" value="0">
    <label for="allows-meeting-preparation" class="mt-5 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-background/40 p-4 focus-within:ring-2 focus-within:ring-primary/30">
        <input id="allows-meeting-preparation" type="checkbox" name="allows_meeting_preparation" value="1" @checked((string) old('allows_meeting_preparation', isset($room) ? (int) $room->allows_meeting_preparation : 0) === '1') aria-describedby="booking-options-help{{ $errors->has('allows_meeting_preparation') ? ' booking-options-error' : '' }}" aria-invalid="{{ $errors->has('allows_meeting_preparation') ? 'true' : 'false' }}" class="mt-1 h-5 w-5 shrink-0 rounded border-slate-300 text-primary focus:ring-primary">
        <span class="min-w-0"><span class="block text-base font-semibold text-primaryDark">Tampilkan jumlah peserta dan permintaan konsumsi</span><span id="booking-options-help" class="mt-2 block text-sm leading-6 text-slate-600">Jika dicentang, pegawai dapat membuka persiapan rapat dan memilih Snack, Makanan, atau keduanya. Jika tidak dicentang, bagian ini tidak tersedia pada form booking ruangan.</span></span>
    </label>
    @error('allows_meeting_preparation')<p id="booking-options-error" role="alert" class="mt-3 text-base text-danger">{{ $message }}</p>@enderror
    <p class="mt-4 text-sm leading-6 text-slate-600">Konsumsi yang diminta tetap memerlukan persetujuan PIC/Admin. Pengaturan berlaku setelah formulir ruangan disimpan; riwayat pengajuan tetap tersimpan.</p>
</fieldset>
<fieldset id="facilities" class="scroll-mt-6 min-w-0 rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm sm:p-6" aria-describedby="facilities-hint{{ $errors->has('facility_ids') || $errors->has('facility_ids.*') ? ' facilities-error' : '' }}">
    <legend class="sr-only">Fasilitas Ruangan</legend>
    <h2 class="mb-2 text-lg font-bold text-primaryDark">Fasilitas Ruangan</h2>
    <p id="facilities-hint" class="mb-5 text-base leading-6 text-slate-600">Centang fasilitas yang tersedia di ruangan ini, lalu simpan formulir. Hanya pilihan yang disimpan akan tampil di halaman booking.</p>
    <div @class(['grid gap-3 sm:grid-cols-2', 'xl:grid-cols-3' => $roomManagement ?? false])>
    @forelse($facilities ?? [] as $facility)
        <label @class(['flex min-h-[52px] cursor-pointer items-start gap-3 rounded-xl border p-3 text-base text-slate-700 transition hover:border-secondary focus-within:ring-2 focus-within:ring-primary/30', 'border-primary/40 bg-secondaryLight/20' => in_array($facility->id, (array) old('facility_ids', $hasRoomInput ? [] : ($selectedFacilityIds ?? []))), 'border-slate-200 bg-surface' => ! in_array($facility->id, (array) old('facility_ids', $hasRoomInput ? [] : ($selectedFacilityIds ?? [])))])>
        <input class="mt-0.5 h-4 w-4 shrink-0 accent-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" type="checkbox" name="facility_ids[]" value="{{ $facility->id }}" @checked(in_array($facility->id, (array) old('facility_ids', $hasRoomInput ? [] : ($selectedFacilityIds ?? []))))><span class="min-w-0 break-words">{{ $facility->name }}{{ $facility->is_active ? '' : ' - Nonaktif' }}</span></label>
    @empty
        <p>Belum ada fasilitas yang dapat dipilih.</p>
    @endforelse
    </div>
    @if($errors->has('facility_ids') || $errors->has('facility_ids.*'))
        <div id="facilities-error" class="mt-3 space-y-1 text-base text-danger">
            @foreach(array_merge($errors->get('facility_ids'), $errors->get('facility_ids.*')) as $message)
                @foreach((array) $message as $detail)<p>{{ $detail }}</p>@endforeach
            @endforeach
        </div>
    @endif
</fieldset>
