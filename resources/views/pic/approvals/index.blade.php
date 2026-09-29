@extends('layouts.schedule')

@section('title', $history ? 'Riwayat Approval' : 'Permintaan Approval')
@section('breadcrumb', $history ? 'Riwayat Approval' : 'Permintaan Approval')

@section('content')
    <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-3xl font-bold text-primaryDark">{{ $history ? 'Riwayat Approval' : 'Permintaan Approval' }}</h1><p class="mt-3 text-sm text-slate-600">{{ auth()->user()->can('access-admin') ? 'Pantau pengajuan. Keputusan diberikan oleh PIC yang ditugaskan.' : 'Pengajuan untuk ruangan yang menjadi tanggung jawab Anda.' }}</p></div>
        <a href="{{ route($history ? 'pic.approvals.index' : 'pic.approvals.history') }}" class="workspace-button-secondary">{{ $history ? 'Permintaan Approval' : 'Riwayat Approval' }}</a>
    </div>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <x-workspace-panel :title="$history ? 'Keputusan Pengajuan' : 'Menunggu Keputusan'">
        @include('shared.bookings-table', ['bookings' => $approvals, 'detailRoute' => 'pic.approvals.show', 'emptyTitle' => $history ? 'Belum ada riwayat approval.' : 'Belum ada permintaan approval.'])
    </x-workspace-panel>
@endsection
