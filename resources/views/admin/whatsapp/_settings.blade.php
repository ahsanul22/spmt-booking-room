@php
    $approval = $template->event === 'approval_request';
    $hasOld = old('_event') === $template->event;
    $savedIds = $selected->get($template->event)?->pluck('user_id')->all() ?? [];
    $picked = $hasOld ? old('recipient_ids', []) : $savedIds;
    $picked = is_array($picked) ? array_map('strval', array_filter($picked, fn ($id) => is_scalar($id))) : [];
    $bodyInput = $hasOld ? old('body') : $template->body;
    $bodyInput = is_string($bodyInput) ? $bodyInput : '';
    $options = $users->filter(fn ($user) => ! $approval || $user->role === \App\Models\User::ROLE_SUPER_ADMIN);
    $eligible = $options->filter(fn ($user) => $user->is_active && \App\Support\WhatsappNumber::valid($user->whatsapp_number));
    $unavailable = $options->diff($eligible);
    $savedRecipients = $eligible->whereIn('id', $savedIds)->count();
    $picReady = $pics->contains(fn ($room) => $room->pics->contains(fn ($pic) => \App\Support\WhatsappNumber::valid($pic->whatsapp_number)));
    $missingRooms = $pics->filter(fn ($room) => ! $room->pics->contains(fn ($pic) => \App\Support\WhatsappNumber::valid($pic->whatsapp_number)))->count();
    $hasSavedRecipient = $savedRecipients > 0 || ($approval && $template->include_room_pics && $picReady);
    $status = ! $template->is_active ? 'Belum diaktifkan' : (! $enabled || ! $hasToken ? 'Pengiriman belum siap' : (! $hasSavedRecipient ? 'Penerima belum siap' : 'Pengaturan lengkap'));
@endphp
<x-workspace-panel :title="$approval ? 'Pengajuan booking' : 'Kebutuhan konsumsi'" :description="$approval ? 'Saat pegawai mengajukan booking, PIC/Admin menerima pesan.' : 'Setelah booking disetujui, petugas menerima kebutuhan konsumsi.'">
    <x-slot:headingActions>
        <span @class(['rounded-full px-3 py-2 text-sm font-semibold', 'bg-secondaryLight/40 text-primaryDark' => $status === 'Pengaturan lengkap', 'bg-background text-slate-600' => $status === 'Belum diaktifkan', 'bg-warning/10 text-warningDark' => ! in_array($status, ['Pengaturan lengkap', 'Belum diaktifkan'])])>Tersimpan: {{ $status }}</span>
    </x-slot:headingActions>
    <form data-wa-settings data-wa-unsaved="{{ $hasOld ? '1' : '0' }}" method="POST" action="{{ route('admin.whatsapp.update', $template) }}" class="p-5 sm:p-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="_event" value="{{ $template->event }}">
        <div class="space-y-7">
            <section aria-labelledby="enable-{{ $template->id }}">
                <h3 id="enable-{{ $template->id }}" class="mb-3 flex items-center gap-3 font-semibold text-primaryDark"><span aria-hidden="true" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-sm text-white">1</span>Aktifkan pesan</h3>
                <input type="hidden" name="is_active" value="0">
                <label class="flex min-h-[44px] cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4"><input class="h-5 w-5 shrink-0 rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" name="is_active" value="1" @checked($hasOld ? old('is_active') : $template->is_active)><span>Kirim pesan {{ $approval ? 'pengajuan booking' : 'kebutuhan konsumsi' }}</span></label>
                <p data-wa-draft hidden role="status" class="mt-3 text-sm text-warningDark">Perubahan belum disimpan. <a href="#save-{{ $template->id }}" class="font-semibold underline">Simpan pengaturan</a></p>
            </section>
            <section aria-labelledby="choose-{{ $template->id }}">
                <h3 id="choose-{{ $template->id }}" class="mb-3 flex items-center gap-3 font-semibold text-primaryDark"><span aria-hidden="true" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-sm text-white">2</span>Pilih penerima</h3>
                <input type="hidden" name="include_room_pics" value="0">
                @if($approval)
                    <label class="mb-4 flex cursor-pointer items-start gap-3 rounded-xl border border-secondaryLight bg-secondaryLight/10 p-4"><input class="mt-1 h-5 w-5 shrink-0 rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" name="include_room_pics" value="1" @checked($hasOld ? old('include_room_pics') : $template->include_room_pics)><span><span class="block font-semibold text-primaryDark">PIC ruangan otomatis</span><span class="mt-1 block text-sm text-slate-600">Mengikuti PIC yang ditugaskan pada ruangan booking.</span></span></label>
                    @if($missingRooms)
                        <p class="mb-4 text-sm leading-6 text-warningDark">{{ $missingRooms }} ruangan belum punya PIC dengan nomor siap digunakan. <a data-wa-open-pics href="#pic-readiness" class="font-semibold underline">Lengkapi nomor PIC</a></p>
                    @endif
                @endif
                <fieldset class="min-w-0" aria-describedby="recipients-{{ $template->id }}-hint{{ $hasOld && $errors->has('recipient_ids') ? ' recipients-'.$template->id.'-error' : '' }}">
                    <legend class="font-semibold text-primaryDark">{{ $approval ? 'Admin tambahan (opsional)' : 'Petugas konsumsi' }}</legend>
                    <p id="recipients-{{ $template->id }}-hint" class="mt-1 text-sm text-slate-600">{{ $approval ? 'Pilih jika Admin juga perlu menerima pesan.' : 'Pilih akun yang bertugas menerima kebutuhan konsumsi.' }}</p>
                    <div class="mt-3 max-h-48 space-y-2 overflow-y-auto">
                        @forelse($eligible as $user)
                            <label class="flex min-h-[44px] cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 has-[:checked]:border-primary has-[:checked]:bg-secondaryLight/20"><input class="h-5 w-5 shrink-0 rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" name="recipient_ids[]" value="{{ $user->id }}" @checked(in_array((string) $user->id, $picked, true))><span class="min-w-0 break-words"><span class="block font-medium">{{ $user->name }}</span><span class="text-sm text-slate-600">{{ $user->whatsapp_number }}</span></span></label>
                        @empty
                            <div class="rounded-xl bg-background p-4"><p class="text-sm text-slate-600">Belum ada {{ $approval ? 'Admin' : 'akun' }} dengan nomor WhatsApp siap digunakan.</p></div>
                        @endforelse
                    </div>
                    @if($unavailable->isNotEmpty())
                        <details class="mt-3" @if($eligible->isEmpty()) open @endif><summary class="cursor-pointer py-2 text-sm font-medium text-primary">Lengkapi akun penerima ({{ $unavailable->count() }})</summary>
                            <div class="mt-2 space-y-2">
                                @foreach($unavailable as $user)
                                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-3"><div class="min-w-0"><p class="break-words font-medium">{{ $user->name }}</p><p class="mt-1 text-sm text-slate-600">{{ ! $user->is_active ? 'Akun nonaktif' : 'Nomor WhatsApp belum valid' }}</p></div><a class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-primary hover:bg-background" href="{{ route('admin.users.edit', $user) }}">{{ $user->is_active ? 'Isi nomor' : 'Edit akun' }}</a></div>
                                @endforeach
                            </div>
                        </details>
                    @endif
                    @if($hasOld) @error('recipient_ids')<p id="recipients-{{ $template->id }}-error" class="mt-2 text-danger">{{ $message }}</p>@enderror @endif
                </fieldset>
                <p data-wa-recipient-summary class="mt-3 text-sm font-medium text-primaryDark" aria-live="polite"></p>
            </section>
            <section data-wa-editor aria-labelledby="compose-{{ $template->id }}">
                <h3 id="compose-{{ $template->id }}" class="mb-3 flex items-center gap-3 font-semibold text-primaryDark"><span aria-hidden="true" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-sm text-white">3</span>Susun pesan</h3>
                <fieldset>
                    <legend class="sr-only">Data yang disertakan</legend>
                    <p class="mb-3 text-sm text-slate-600">Centang data yang ingin dimasukkan. Isi pesan otomatis diperbarui.</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($messageFields as $field)
                            <label class="flex min-h-[44px] cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm has-[:checked]:border-primary has-[:checked]:bg-secondaryLight/20"><input type="checkbox" disabled data-wa-field data-wa-tokens="{{ json_encode($field['tokens']) }}" data-wa-line="{{ $field['line'] }}" class="h-4 w-4 shrink-0 rounded border-slate-300 text-primary focus:ring-primary" @checked(collect($field['tokens'])->every(fn ($token) => str_contains($bodyInput, $token)))>{{ $field['label'] }}</label>
                        @endforeach
                    </div>
                    <noscript><p class="mt-2 text-sm text-slate-600">Aktifkan JavaScript untuk menggunakan checkbox. Teks pesan tetap dapat diedit.</p></noscript>
                    <p data-wa-notice role="status" aria-live="polite" class="mt-2 text-sm text-danger"></p>
                </fieldset>
                <div class="mt-4">
                    <div class="mb-2 flex items-center justify-between gap-3"><label for="body-{{ $template->id }}" class="font-semibold text-primaryDark">Isi pesan <span aria-hidden="true" class="text-danger">*</span></label><span data-wa-character-count class="text-sm text-slate-500"></span></div>
                    <textarea id="body-{{ $template->id }}" name="body" rows="7" required maxlength="2000" class="workspace-control" aria-describedby="body-{{ $template->id }}-hint{{ $hasOld && $errors->has('body') ? ' body-'.$template->id.'-error' : '' }}" aria-invalid="{{ $hasOld && $errors->has('body') ? 'true' : 'false' }}">{{ $bodyInput }}</textarea>
                    <p id="body-{{ $template->id }}-hint" class="mt-2 text-sm text-slate-600">Teks dapat diedit. Data dalam kurung otomatis diisi dari booking.</p>
                    @if($hasOld) @error('body')<p id="body-{{ $template->id }}-error" class="mt-2 text-danger">{{ $message }}</p>@enderror @endif
                </div>
            </section>
        </div>
        <div id="save-{{ $template->id }}" class="mt-6 border-t border-slate-100 pt-5">
            <p data-wa-save-state role="status" aria-live="polite" class="mb-3 text-sm text-slate-600">{{ $hasOld ? 'Isian ini belum disimpan.' : 'Simpan pengaturan sebelum mencoba booking baru.' }}</p>
            <div class="flex flex-wrap gap-3"><button type="submit" class="workspace-button flex-1" data-wa-save>Simpan {{ $approval ? 'pengajuan' : 'konsumsi' }}</button><button type="submit" class="workspace-button-secondary" formaction="{{ route('admin.whatsapp.preview', $template) }}" name="_method" value="POST">Lihat contoh</button></div>
        </div>
        @if(($preview['event'] ?? null) === $template->event)
            <div role="status" class="mt-5 rounded-xl border border-secondaryLight bg-background p-4"><p class="font-semibold text-primaryDark">Contoh pesan · Tidak dikirim</p>@if($preview['message'])<p class="mt-3 whitespace-pre-wrap break-words">{{ $preview['message'] }}</p>@else<p class="mt-2 text-slate-600">Belum ada booking untuk contoh pesan. Isian belum disimpan.</p>@endif<p class="mt-3 text-sm text-slate-600">Menggunakan booking terbaru.</p></div>
        @endif
    </form>
</x-workspace-panel>
