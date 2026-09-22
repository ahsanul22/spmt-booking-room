@extends('layouts.base')
@section('title', 'Detail Approval')
@section('content')
    <h1>Detail Approval</h1>
    <x-skeleton-notice />
    @include('shared.booking-details', ['booking' => $approval ?? null])
    <x-skeleton-form>
        <x-pending-action>Approve</x-pending-action>
        <x-textarea name="rejection_reason" label="Alasan Penolakan" :value="old('rejection_reason')" />
        <x-pending-action>Reject</x-pending-action>
    </x-skeleton-form>
    <a href="{{ route('pic.approvals.index') }}">Kembali ke Permintaan Approval</a>
    <a href="{{ route('pic.approvals.history') }}">Riwayat Approval</a>
@endsection
