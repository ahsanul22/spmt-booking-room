    <div class="grid items-start gap-6 xl:grid-cols-2">
        @if($room->access_type === 'restricted')
            <x-workspace-panel title="Unit yang Diperbolehkan" description="Centang semua unit yang ingin diberi atau dipertahankan aksesnya.">
                <form method="POST" action="{{ route('admin.rooms.access.update', $room) }}" class="p-5 sm:p-6">
                    @csrf
                <input type="hidden" name="_section" value="access">
                    @method('PUT')
                    <fieldset class="min-w-0" aria-describedby="access-hint{{ $errors->has('organizational_unit_ids') || $errors->has('organizational_unit_ids.*') ? ' access-error' : '' }}">
                        <legend class="mb-4 font-semibold text-primaryDark">Pilih unit organisasi</legend>
                        <div class="space-y-3">
                            @forelse($units as $unit)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 hover:border-primary focus-within:ring-2 focus-within:ring-primary">
                                    <input type="checkbox" class="mt-1 h-5 w-5 shrink-0 accent-primary" name="organizational_unit_ids[]" value="{{ $unit->id }}" @checked(in_array($unit->id, (array) old('organizational_unit_ids', old('_section') === 'access' ? [] : $selectedUnitIds)))>
                                    <span class="min-w-0 break-words"><span class="block font-semibold text-primaryDark">{{ $unit->name }}</span><span class="mt-1 block text-base text-slate-600">{{ $unit->type }}{{ $unit->is_active ? '' : ' - Nonaktif' }}</span></span>
                                </label>
                            @empty
                                <p class="rounded-xl bg-background p-4">Belum ada unit organisasi yang dapat dipilih.</p>
                            @endforelse
                        </div>
                        <p id="access-hint" class="mt-4 text-base leading-6 text-slate-600">Akses hanya untuk unit yang dicentang, tidak otomatis mencakup unit turunannya. Kosongkan semua pilihan untuk melepas seluruh akses unit.</p>
                        @if($errors->has('organizational_unit_ids') || $errors->has('organizational_unit_ids.*'))
                            <p id="access-error" role="alert" class="mt-3 text-danger">{{ $errors->first('organizational_unit_ids') ?: $errors->first('organizational_unit_ids.*') }}</p>
                        @endif
                    </fieldset>
                    <div class="mt-6 flex flex-wrap gap-3 border-t border-slate-100 pt-5">
                        <button type="submit" class="workspace-button">Simpan Unit Akses</button>
                        
                    </div>
                </form>
            </x-workspace-panel>
        @else
            <x-workspace-panel title="Akses Terbuka">
                <div class="space-y-4 p-5 sm:p-6">
                    <p class="leading-7">Akses terbuka untuk semua unit. Daftar unit tersimpan di bawah tidak digunakan.</p>
                    <p class="text-base leading-6 text-slate-600">Untuk membatasi booking ke unit tertentu, ubah jenis akses menjadi Unit tertentu pada pengaturan ruangan.</p>
                    <a class="workspace-button" href="{{ route('admin.rooms.show', $room).'#room-data' }}">Edit tipe akses ruangan</a>
                </div>
            </x-workspace-panel>
        @endif
        <x-workspace-panel title="Unit Tersimpan" description="Daftar ini menunjukkan pengaturan yang sudah disimpan.">
            <ul class="divide-y divide-slate-100">
                @forelse($room->allowedOrganizationalUnits as $unit)
                    <li class="flex flex-wrap items-center justify-between gap-3 p-5 sm:px-6"><div class="min-w-0"><p class="break-words font-semibold text-primaryDark">{{ $unit->name }}</p><p class="mt-1 text-base text-slate-600">{{ $unit->type }}</p></div><x-workspace-status :active="$unit->is_active" /></li>
                @empty
                    <li><x-workspace-empty title="Belum ada unit akses." :description="$room->access_type === 'restricted' ? 'Belum ada unit yang diberi akses booking. Pilih unit di formulir lalu simpan.' : 'Ruangan ini terbuka untuk semua unit.'" /></li>
                @endforelse
            </ul>
        </x-workspace-panel>
    </div>
