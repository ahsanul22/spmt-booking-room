@extends('layouts.schedule')

@section('title', 'Detail Approval')
@section('breadcrumb', 'Approval / Detail')

@section('content')
    <div class="mb-7 flex flex-wrap items-center justify-between gap-4"><h1 class="text-3xl font-bold text-primaryDark">Detail Approval</h1><a href="{{ route('pic.approvals.index') }}" class="workspace-button-secondary">Kembali ke Permintaan Approval</a></div>
    <div class="workspace-feedback"><x-form-feedback /></div>
    @include('shared.booking-details')
    @if($canDecide)
        <div class="mt-6"><x-workspace-panel title="Keputusan PIC" description="Satu keputusan PIC menyelesaikan pengajuan. Alasan wajib diisi untuk penolakan.">
            <div class="grid gap-6 p-5 sm:grid-cols-2 sm:p-6">
                <form method="POST" action="{{ route('pic.approvals.decide', $booking->id) }}">@csrf<input type="hidden" name="decision" value="approved"><p class="mb-4 text-sm leading-6 text-slate-600">Pastikan penggunaan ruangan sesuai dengan pengajuan.</p><button type="submit" class="workspace-button">Setujui Pengajuan</button></form>
                <form method="POST" action="{{ route('pic.approvals.decide', $booking->id) }}">@csrf<input type="hidden" name="decision" value="rejected"><x-workspace-field name="rejection_reason" label="Alasan Penolakan" type="textarea" :value="old('rejection_reason')" required maxlength="2000" /><button type="submit" class="mt-4 rounded-xl bg-danger px-5 py-3 text-sm font-semibold text-white">Tolak Pengajuan</button></form>
            </div>
        </x-workspace-panel></div>
    @endif
@endsection
