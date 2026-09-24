@extends('layouts.schedule')
@section('title', 'Detail Unit Organisasi')
@section('breadcrumb', 'Unit Organisasi / Detail')
@section('content')
    <x-workspace-heading title="Detail Unit Organisasi" description="Tinjau posisi unit dan hubungan dalam struktur organisasi.">
        <a class="workspace-button-secondary" href="{{ route('admin.organizational-units.index') }}">Kembali</a>
    </x-workspace-heading>
    <div class="workspace-feedback">@include('admin.organizational-units._feedback')</div>
    <div class="grid items-start gap-6 xl:grid-cols-2">
        <x-workspace-panel title="Informasi Unit">
            <x-slot:headingActions><x-workspace-status :active="$unit->is_active" /></x-slot:headingActions>
            <div class="p-5 sm:p-6">
                <dl class="workspace-detail">
                    <div><dt>Nama</dt><dd>{{ $unit->name }}</dd></div>
                    <div><dt>Tipe Unit</dt><dd>{{ $unit->type }}</dd></div>
                    <div class="sm:col-span-2"><dt>Unit Induk</dt><dd>
                        @if($unit->parent)
                            <a class="text-primary hover:underline" href="{{ route('admin.organizational-units.show', $unit->parent) }}">{{ $unit->parent->name }}</a>
                        @else
                            Tanpa unit induk
                        @endif
                    </dd></div>
                </dl>
                <p class="mt-5 text-sm text-slate-600">Status Aktif: {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
                    <a class="workspace-button" href="{{ route('admin.organizational-units.edit', $unit->id) }}">Edit</a>
                    <div class="workspace-record-actions">@include('admin.organizational-units._status')</div>
                </div>
            </div>
        </x-workspace-panel>
        <x-workspace-panel title="Unit di Bawahnya" description="Unit yang terhubung langsung dengan unit ini.">
            @if($unit->children->isNotEmpty())
                <ul class="divide-y divide-slate-100">
                    @foreach($unit->children as $child)
                        <li class="flex flex-wrap items-center justify-between gap-3 p-5 sm:px-6">
                            <div class="min-w-0"><a class="break-words text-sm font-semibold text-primary hover:underline" href="{{ route('admin.organizational-units.show', $child) }}">{{ $child->name }}</a><p class="mt-1 text-xs text-slate-600">{{ $child->type }}</p></div>
                            <x-workspace-status :active="$child->is_active" />
                        </li>
                    @endforeach
                </ul>
            @else
                <x-workspace-empty icon="grid" title="Belum ada unit di bawahnya." description="Unit turunan akan tampil di sini setelah dihubungkan melalui pengaturan unit induk." />
            @endif
        </x-workspace-panel>
    </div>
@endsection
