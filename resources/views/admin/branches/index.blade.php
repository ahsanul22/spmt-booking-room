@extends('layouts.schedule')
@section('title', 'Kantor Pusat & Cabang')
@section('breadcrumb', 'Kantor Pusat & Cabang')
@section('content')
    <x-workspace-heading title="Kantor Pusat & Cabang" description="Pisahkan ruangan berdasarkan lokasi. Admin mengelola seluruh lokasi; PIC mengikuti penugasan ruangan." />
    <x-form-feedback />
    <form method="POST" action="{{ route('admin.branches.store') }}" class="mb-6 flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-surface p-5">
        @csrf
        <x-workspace-field name="name" label="Nama kantor / cabang baru" required maxlength="255" :value="old('editing_branch_id') ? '' : old('name')" />
        <button class="workspace-button" type="submit">Tambah Lokasi</button>
    </form>
    <div class="space-y-4">
        @foreach($branches as $branch)
            <form method="POST" action="{{ route('admin.branches.update', $branch) }}" class="flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-surface p-5">
                @csrf @method('PUT')
                <input type="hidden" name="editing_branch_id" value="{{ $branch->id }}">
                <label for="branch-{{ $branch->id }}" class="text-base font-semibold text-primaryDark">Nama lokasi · {{ $branch->rooms_count }} ruangan
                    <input id="branch-{{ $branch->id }}" name="name" value="{{ (string) old('editing_branch_id') === (string) $branch->id ? old('name', $branch->name) : $branch->name }}" required maxlength="255" class="mt-2 block w-full rounded-lg border-slate-300 focus:border-primary focus:ring-primary">
                </label>
                <button type="submit" class="workspace-button-secondary">Simpan Nama</button>
            </form>
        @endforeach
    </div>
    <div class="mt-5">{{ $branches->links() }}</div>
@endsection
