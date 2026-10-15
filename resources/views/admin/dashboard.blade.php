@extends('layouts.schedule')

@section('title', $title)
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="mb-7 flex flex-wrap items-center justify-between gap-5">
        <div>
            <p class="mb-2 text-sm font-bold uppercase tracking-[0.2em] text-primary">Administrasi Workspace</p>
            <h1 class="text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">{{ $title }}</h1>
            <p class="mt-3 text-base leading-6 text-slate-600">Pantau kondisi ruangan, kelengkapan pengaturan, dan data pengguna.</p>
        </div>
        <a href="{{ route('admin.rooms.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primaryDark"><x-schedule-icon name="plus" />Tambah Ruangan</a>
    </div>

    @if(session('status'))
        <div role="status" class="mb-6 rounded-xl border border-secondaryLight bg-surface p-4 text-base text-primaryDark">{{ session('status') }}</div>
    @endif

<section aria-label="Ringkasan administrasi" class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach($stats as $stat)
            <div class="rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3"><h2 class="text-base font-medium text-slate-600">{{ $stat['label'] }}</h2><span class="rounded-lg bg-secondaryLight/20 p-2 text-primary"><x-schedule-icon :name="$stat['icon']" /></span></div>
                <p class="mt-4 text-3xl font-semibold text-primaryDark">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ $stat['description'] }}</p>
            </div>
        @endforeach
    </section>

    <div class="grid items-start gap-6 xl:grid-cols-2">
            <x-workspace-panel title="Kondisi Ruangan" description="Kondisi operasional ruangan aktif, terpisah dari ketersediaan jadwal.">
                <div class="space-y-5 p-5 sm:p-6">
                    @if($activeRoomCount)
                        @foreach($roomConditions as $condition)
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-3 text-base"><span class="text-slate-600">{{ $condition['label'] }}</span><span class="font-semibold text-primaryDark">{{ $condition['count'] }} ruangan</span></div>
                                <div class="h-3 overflow-hidden rounded-full bg-background" aria-hidden="true"><div class="h-full rounded-full bg-primary" style="width: {{ $condition['percentage'] }}%"></div></div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-base text-slate-600">Belum ada ruangan aktif. Tambahkan atau aktifkan ruangan melalui Daftar Ruangan.</p>
                    @endif
                    <a href="{{ route('admin.rooms.index') }}" class="workspace-button-secondary">Kelola kondisi ruangan</a>
                </div>
            </x-workspace-panel>
            <x-workspace-panel title="Kelengkapan Pengaturan Ruangan" description="Lengkapi penanggung jawab dan fasilitas agar informasi ruangan siap digunakan.">
                <div class="grid gap-4 border-b border-slate-100 p-5 sm:grid-cols-2 sm:p-6">
                    <div class="rounded-xl bg-background p-4"><p class="text-sm text-slate-600">Belum memiliki PIC aktif</p><p class="mt-2 text-2xl font-bold text-primaryDark">{{ $missingPicCount }} <span class="text-base font-normal">ruangan</span></p></div>
                    <div class="rounded-xl bg-background p-4"><p class="text-sm text-slate-600">Fasilitas belum diatur</p><p class="mt-2 text-2xl font-bold text-primaryDark">{{ $missingFacilitiesCount }} <span class="text-base font-normal">ruangan</span></p></div>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($attentionRooms as $room)
                        <li class="flex flex-wrap items-center justify-between gap-3 p-5 sm:px-6">
                            <div class="min-w-0"><p class="break-words text-base font-semibold text-primaryDark">{{ $room->name }}</p><p class="mt-1 text-sm text-slate-600">{{ collect([(int) $room->active_pics_count === 0 ? 'PIC aktif belum ditetapkan' : null, (int) $room->facilities_count === 0 ? 'Fasilitas belum diatur' : null])->filter()->implode(' / ') }}</p></div>
                            <a href="{{ route('admin.rooms.show', $room) }}" class="workspace-button-secondary" aria-label="Lengkapi pengaturan {{ $room->name }}">Lengkapi</a>
                        </li>
                    @empty
                        <li class="p-5 text-base text-slate-600 sm:px-6">Tidak ada ruangan aktif yang perlu dilengkapi PIC atau fasilitasnya.</li>
                    @endforelse
                </ul>
                @if($attentionRooms->isNotEmpty())<p class="border-t border-slate-100 p-5 text-sm text-slate-600">Menampilkan maksimal 5 ruangan aktif. <a href="{{ route('admin.rooms.index') }}" class="font-semibold text-primary underline">Lihat seluruh ruangan</a></p>@endif
            </x-workspace-panel>

    </div>
@endsection
