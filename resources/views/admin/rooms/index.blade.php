@extends('layouts.schedule')
@section('title', 'Daftar Ruangan')
@section('breadcrumb', 'Daftar Ruangan')
@section('content')
    <x-workspace-heading title="Daftar Ruangan" description="Kelola informasi, fasilitas, dan pengaturan ruang rapat dalam satu tempat.">
        <a href="{{ route('admin.rooms.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primaryDark"><x-schedule-icon name="plus" />Tambah Ruangan</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <section aria-labelledby="rooms-title">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div><h2 id="rooms-title" class="text-lg font-bold text-primaryDark">Direktori Ruangan</h2><p class="mt-1 text-xs leading-5 text-slate-600">Status operasional tidak menunjukkan ketersediaan jadwal.</p></div>
            <a href="{{ route('admin.schedule.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-surface px-4 py-2.5 text-sm font-semibold text-primary hover:bg-background"><x-schedule-icon name="calendar" class="h-4 w-4" />Lihat Jadwal</a>
        </div>
        <div class="grid gap-5 xl:grid-cols-2 2xl:grid-cols-3">
            @forelse($rooms ?? [] as $item)
                @include('admin.rooms._card', ['item' => $item])
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-surface"><x-workspace-empty title="Belum ada data ruangan." description="Tambahkan ruangan pertama, lalu lengkapi kapasitas, fasilitas, PIC, dan aturan aksesnya." /></div>
            @endforelse
        </div>
        @if($rooms->hasPages())<div class="mt-6 rounded-2xl border border-slate-200 bg-surface p-5 sm:px-6">{{ $rooms->links() }}</div>@endif
    </section>
@endsection
