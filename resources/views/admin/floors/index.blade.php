@extends('layouts.schedule')
@section('breadcrumb', 'Lantai')
@section('title', 'Daftar Lantai')
@section('content')
    <x-workspace-heading title="Lantai" description="Kelola informasi lantai gedung.">
        <a class="workspace-button" href="{{ route('admin.floors.create') }}"><x-schedule-icon name="plus" />Tambah Lantai</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <x-workspace-panel title="Daftar Lantai" description="Kelola informasi lantai gedung.">
        <x-slot:headingActions><span class="rounded-lg bg-background px-3 py-2 text-sm font-semibold text-slate-600">{{ $floors->total() }} lantai</span></x-slot:headingActions>
        @if($floors->count())
        <div class="workspace-table" role="region" aria-label="Daftar Lantai, geser untuk melihat semua kolom" tabindex="0">
        <x-table :headers="['Nomor Lantai', 'Nama', 'Deskripsi', 'Status Aktif', 'Aksi']">
            @foreach($floors ?? [] as $item)
                <tr><td>{{ $item->floor_number }}</td>
                    <td>{{ data_get($item, 'name') ?? '—' }}</td>
                    <td>{{ data_get($item, 'description') ?? '—' }}</td>
                    <td><x-workspace-status :active="$item->is_active" /></td>
                    <td><div class="workspace-record-actions">
                        <a href="{{ route('admin.floors.show', $item) }}">Detail<span class="sr-only"> {{ $item->name }}</span></a>
                        <a href="{{ route('admin.floors.edit', $item) }}">Edit<span class="sr-only"> {{ $item->name }}</span></a>
                        @include('admin.floors._status', ['floor' => $item])
                    </div></td>
                </tr>
            @endforeach
        </x-table>
        </div>
        @else
            <x-workspace-empty icon="grid" title="Belum ada data lantai." description="Gunakan tombol Tambah Lantai untuk melengkapi data administrasi." />
        @endif
        @if($floors->hasPages())<div class="border-t border-slate-100 p-5 sm:px-6">{{ $floors->links() }}</div>@endif
    </x-workspace-panel>
@endsection
