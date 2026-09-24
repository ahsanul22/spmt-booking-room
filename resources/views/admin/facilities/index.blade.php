@extends('layouts.schedule')
@section('breadcrumb', 'Fasilitas')
@section('title', 'Daftar Fasilitas')
@section('content')
    <x-workspace-heading title="Fasilitas" description="Kelola perlengkapan pendukung ruang rapat.">
        <a class="workspace-button" href="{{ route('admin.facilities.create') }}"><x-schedule-icon name="plus" />Tambah Fasilitas</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <x-workspace-panel title="Daftar Fasilitas" description="Kelola perlengkapan pendukung ruang rapat.">
        <x-slot:headingActions><span class="rounded-lg bg-background px-3 py-2 text-xs font-semibold text-slate-600">{{ $facilities->total() }} fasilitas</span></x-slot:headingActions>
        @if($facilities->count())
        <div class="workspace-table" role="region" aria-label="Daftar Fasilitas, geser untuk melihat semua kolom" tabindex="0">
        <x-table :headers="['Nama', 'Deskripsi', 'Status Aktif', 'Aksi']">
            @foreach($facilities ?? [] as $item)
                <tr>
                    <td>{{ data_get($item, 'name') ?? '—' }}</td>
                    <td>{{ data_get($item, 'description') ?? '—' }}</td>
                    <td><x-workspace-status :active="$item->is_active" /></td>
                    <td><div class="workspace-record-actions">
                        <a href="{{ route('admin.facilities.show', $item->id) }}">Detail<span class="sr-only"> {{ $item->name }}</span></a>
                        <a href="{{ route('admin.facilities.edit', $item->id) }}">Edit<span class="sr-only"> {{ $item->name }}</span></a>
                        @include('admin.facilities._status', ['facility' => $item])
                    </div></td>
                </tr>
            @endforeach
        </x-table>
        </div>
        @else
            <x-workspace-empty icon="grid" title="Belum ada data fasilitas." description="Gunakan tombol Tambah Fasilitas untuk melengkapi data administrasi." />
        @endif
        @if($facilities->hasPages())<div class="border-t border-slate-100 p-5 sm:px-6">{{ $facilities->links() }}</div>@endif
    </x-workspace-panel>
@endsection
