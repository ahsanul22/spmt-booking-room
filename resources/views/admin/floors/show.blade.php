@extends('layouts.schedule')
@section('title', 'Detail Lantai')
@section('breadcrumb', 'Lantai / Detail')
@section('content')
    <x-workspace-heading title="Detail Lantai" description="Informasi lantai gedung yang digunakan oleh ruangan.">
        <a class="workspace-button-secondary" href="{{ route('admin.floors.index') }}">Kembali</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <x-workspace-panel title="Informasi Lantai">
        <x-slot:headingActions><x-workspace-status :active="$floor->is_active" /></x-slot:headingActions>
        <div class="p-5 sm:p-6">
            <dl class="workspace-detail"><div><dt>Nomor Lantai</dt><dd>{{ $floor->floor_number }}</dd></div>
                <div class="sm:col-span-2"><dt>Nama</dt><dd>{{ $floor->name }}</dd></div>
                <div class="sm:col-span-2"><dt>Deskripsi</dt><dd class="whitespace-pre-line">{{ $floor->description ?? 'Belum ada deskripsi.' }}</dd></div>
            </dl>
            <p class="mt-5 text-base text-slate-600">Status Aktif: {{ $floor->is_active ? 'Aktif' : 'Nonaktif' }}</p>
            <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
                <a class="workspace-button" href="{{ route('admin.floors.edit', $floor->id) }}">Edit</a>
                <div class="workspace-record-actions">@include('admin.floors._status')</div>
            </div>
        </div>
    </x-workspace-panel>
@endsection
