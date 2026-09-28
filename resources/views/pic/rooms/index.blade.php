@extends('layouts.schedule')
@section('title', 'Ruangan Saya')
@section('breadcrumb', 'Ruangan Saya')
@section('content')
    <div class="mb-7 flex flex-wrap items-center justify-between gap-5">
        <div class="max-w-2xl">
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-primary">Area PIC Ruangan</p>
            <h1 class="text-3xl font-bold text-primaryDark sm:text-4xl">Ruangan Saya</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">Informasi dan jadwal ruangan yang menjadi tanggung jawab Anda. Tinjau penggunaannya sebelum memberikan keputusan pengajuan.</p>
        </div>
        <a href="{{ route('pic.approvals.index') }}" class="workspace-button"><x-schedule-icon name="clock" class="h-4 w-4" />Permintaan Approval</a>
    </div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-secondaryLight bg-secondaryLight/20 p-5">
        <p class="text-sm font-semibold text-primaryDark">{{ $rooms->total() }} ruangan ditugaskan kepada Anda</p>
        <p class="max-w-xl text-xs leading-6 text-slate-600">Status operasional menunjukkan kondisi ruangan. Periksa jadwal untuk melihat jam penggunaannya.</p>
    </div>
    <section aria-label="Daftar ruangan tanggung jawab Anda">
        <div class="grid items-stretch gap-5 md:grid-cols-2 2xl:grid-cols-3">
            @forelse($rooms as $room)
                @include('shared.room-catalog-card', ['public' => false, 'managed' => true])
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-surface">
                    <x-workspace-empty title="Belum ada ruangan yang ditugaskan." description="Ruangan akan tampil setelah administrator menetapkan Anda sebagai PIC. Hubungi administrator jika penugasan Anda belum sesuai." />
                </div>
            @endforelse
        </div>
        <div class="mt-6">{{ $rooms->links() }}</div>
    </section>
@endsection
