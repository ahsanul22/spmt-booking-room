@extends('layouts.schedule')
@section('title', 'Detail Fasilitas')
@section('breadcrumb', 'Fasilitas / Detail')
@section('content')
    <x-workspace-heading title="Detail Fasilitas" description="Informasi perlengkapan pendukung yang dapat dikaitkan dengan ruangan.">
        <a class="workspace-button-secondary" href="{{ route('admin.facilities.index') }}">Kembali</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <x-workspace-panel title="Informasi Fasilitas">
        <x-slot:headingActions><x-workspace-status :active="$facility->is_active" /></x-slot:headingActions>
        <div class="p-5 sm:p-6">
            <dl class="workspace-detail">
                <div class="sm:col-span-2"><dt>Nama</dt><dd>{{ $facility->name }}</dd></div>
                <div class="sm:col-span-2"><dt>Deskripsi</dt><dd class="whitespace-pre-line">{{ $facility->description ?? 'Belum ada deskripsi.' }}</dd></div>
            </dl>
            <p class="mt-5 text-sm text-slate-600">Status Aktif: {{ $facility->is_active ? 'Aktif' : 'Nonaktif' }}</p>
            <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
                <a class="workspace-button" href="{{ route('admin.facilities.edit', $facility->id) }}">Edit</a>
                <div class="workspace-record-actions">@include('admin.facilities._status')</div>
            </div>
        </div>
    </x-workspace-panel>
@endsection
