@extends('layouts.schedule')
@section('breadcrumb', 'Unit Organisasi')
@section('title', 'Daftar Unit Organisasi')
@section('content')
    <x-workspace-heading title="Unit Organisasi" description="Kelola susunan direktorat, divisi, dan departemen.">
        <a class="workspace-button" href="{{ route('admin.organizational-units.create') }}"><x-schedule-icon name="plus" />Tambah Unit Organisasi</a>
    </x-workspace-heading>
    <div class="workspace-feedback">@include('admin.organizational-units._feedback')</div>
    <x-workspace-panel title="Daftar Unit Organisasi" description="Kelola susunan direktorat, divisi, dan departemen.">
        <x-slot:headingActions><span class="rounded-lg bg-background px-3 py-2 text-xs font-semibold text-slate-600">{{ $units->total() }} unit</span></x-slot:headingActions>
        @if($units->count())
        <div class="workspace-table" role="region" aria-label="Daftar Unit Organisasi, geser untuk melihat semua kolom" tabindex="0">
        <x-table :headers="['Nama', 'Tipe Unit', 'Unit Induk', 'Status Aktif', 'Aksi']">
            @foreach($units ?? [] as $item)
                <tr>
                    <td>{{ data_get($item, 'name') ?? '—' }}</td>
                    <td>{{ data_get($item, 'type') ?? '—' }}</td>
                    <td>@if($item->parent)<a class="hover:underline" href="{{ route('admin.organizational-units.show', $item->parent) }}">{{ $item->parent->name }}</a>@else<span class="text-slate-500">Tanpa unit induk</span>@endif</td>
                    <td><x-workspace-status :active="$item->is_active" /></td>
                    <td><div class="workspace-record-actions">
                        <a href="{{ route('admin.organizational-units.show', $item->id) }}">Detail<span class="sr-only"> {{ $item->name }}</span></a>
                        <a href="{{ route('admin.organizational-units.edit', $item->id) }}">Edit<span class="sr-only"> {{ $item->name }}</span></a>
                        @include('admin.organizational-units._status', ['unit' => $item])
                    </div></td>
                </tr>
            @endforeach
        </x-table>
        </div>
        @else
            <x-workspace-empty icon="grid" title="Belum ada data unit organisasi." description="Gunakan tombol Tambah Unit Organisasi untuk melengkapi data administrasi." />
        @endif
        @if($units->hasPages())<div class="border-t border-slate-100 p-5 sm:px-6">{{ $units->links() }}</div>@endif
    </x-workspace-panel>
@endsection
