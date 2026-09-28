@extends('layouts.schedule')
@section('title', 'Kelola PIC')
@section('breadcrumb', 'Daftar Ruangan / Kelola PIC')
@section('content')
    <x-workspace-heading title="Kelola PIC" description="Tentukan penanggung jawab untuk ruangan ini. Satu ruangan dapat memiliki lebih dari satu PIC.">
        <a href="{{ route('admin.rooms.show', $room) }}" class="workspace-button-secondary">Kembali ke Detail Ruangan</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <section class="mb-6 rounded-2xl border border-secondaryLight bg-secondaryLight/20 p-5 sm:p-6" aria-labelledby="room-name">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-4">
                <span class="shrink-0 rounded-xl bg-primaryDark p-3 text-white"><x-schedule-icon name="room" class="h-6 w-6" /></span>
                <div class="min-w-0"><p class="text-xs font-semibold text-primary">Ruangan yang dikelola</p><h2 id="room-name" class="mt-1 break-words text-xl font-bold text-primaryDark">{{ $room->name }}</h2></div>
            </div>
            <x-workspace-status :active="$room->is_active" />
        </div>
        <p class="mt-4 text-sm leading-6 text-slate-600">{{ $room->requires_approval ? 'Pengajuan ruangan ini memerlukan persetujuan PIC.' : 'Pengajuan ruangan ini tidak memerlukan persetujuan PIC.' }}</p>
    </section>
    <div class="grid items-start gap-6 xl:grid-cols-2">
        <x-workspace-panel title="Pilih PIC" description="Centang seluruh PIC yang ingin ditugaskan atau dipertahankan.">
            <form method="POST" action="{{ route('admin.rooms.pics.update', $room) }}" class="p-5 sm:p-6">
                @csrf
                @method('PUT')
                <fieldset aria-describedby="pic-help{{ $errors->has('pic_ids') || $errors->has('pic_ids.*') ? ' pic-error' : '' }}">
                    <legend class="mb-4 text-sm font-semibold text-primaryDark">Akun PIC Ruangan</legend>
                    <div class="space-y-3">
                        @forelse($eligiblePics as $pic)
                            <label for="pic-{{ $pic->id }}" class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-primary focus-within:ring-2 focus-within:ring-primary">
                                <input id="pic-{{ $pic->id }}" type="checkbox" name="pic_ids[]" value="{{ $pic->id }}" @checked(in_array($pic->id, (array) old('pic_ids', session()->hasOldInput() ? [] : $selectedPicIds))) class="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 text-primary focus:ring-primary">
                                <span class="min-w-0 flex-1"><span class="block break-words text-sm font-semibold text-primaryDark">{{ $pic->name }}</span><span class="mt-1 block break-all text-xs text-slate-600">{{ $pic->email }}</span>@if(! $pic->is_active)<span class="mt-2 inline-block text-xs font-semibold text-danger">Akun nonaktif</span>@endif</span>
                            </label>
                        @empty
                            <p class="rounded-xl bg-background p-4 text-sm leading-6 text-slate-600">Belum ada akun PIC ruangan. Tambahkan akun dengan role Room PIC melalui Kelola User.</p>
                        @endforelse
                    </div>
                    <p id="pic-help" class="mt-4 text-xs leading-6 text-slate-600">Pilihan ini menggantikan daftar PIC ruangan. Kosongkan semua centang untuk melepas seluruh PIC. Akun nonaktif tidak dapat memproses approval.</p>
                    @if($errors->has('pic_ids') || $errors->has('pic_ids.*'))
                        <p id="pic-error" role="alert" class="mt-2 text-sm text-danger">{{ $errors->first('pic_ids') ?: $errors->first('pic_ids.*') }}</p>
                    @endif
                </fieldset>
                <div class="mt-6 flex flex-wrap gap-3 border-t border-slate-100 pt-5">
                    <button type="submit" class="workspace-button">Simpan PIC</button>
                    <a href="{{ route('admin.rooms.show', $room) }}" class="workspace-button-secondary">Batal</a>
                </div>
            </form>
        </x-workspace-panel>
        <div class="min-w-0 space-y-6">
            <x-workspace-panel title="PIC Saat Ini" description="Daftar penanggung jawab yang sudah tersimpan.">
                <x-slot:headingActions><span class="rounded-full bg-background px-3 py-1 text-xs font-semibold text-primaryDark">{{ $room->pics->count() }} PIC</span></x-slot:headingActions>
                <ul class="divide-y divide-slate-100">
                    @forelse($room->pics as $pic)
                        <li class="p-5 sm:px-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0 flex-1"><p class="break-words text-sm font-semibold text-primaryDark">{{ $pic->name }}</p><p class="mt-1 break-all text-xs text-slate-600">{{ $pic->email }}</p><p class="mt-2 text-xs text-slate-500">Role: {{ $pic->roleLabel() }}</p></div>
                                <x-workspace-status :active="$pic->is_active" />
                            </div>
                            <form method="POST" action="{{ route('admin.rooms.pics.destroy', [$room, $pic]) }}" class="mt-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex min-h-[44px] items-center rounded-lg px-3 text-sm font-semibold text-danger hover:bg-danger/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-danger" aria-label="Lepas PIC {{ $pic->name }} dari ruangan ini">Lepas PIC</button>
                            </form>
                        </li>
                    @empty
                        <li><x-workspace-empty title="Belum ada PIC." description="Pilih akun di formulir, lalu simpan untuk menugaskan penanggung jawab ruangan." /></li>
                    @endforelse
                </ul>
            </x-workspace-panel>
            <section class="rounded-2xl border border-secondaryLight bg-secondaryLight/20 p-5 sm:p-6" aria-labelledby="assignment-note">
                <h2 id="assignment-note" class="font-semibold text-primaryDark">Penugasan per ruangan</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">Ruangan ini akan muncul pada halaman Ruangan Saya milik PIC yang ditugaskan. Perubahan di sini tidak mengubah akun, role, atau penugasan PIC pada ruangan lain.</p>
            </section>
        </div>
    </div>
@endsection
