@extends('layouts.schedule')
@section('breadcrumb', 'Lantai')
@section('title', 'Edit Lantai')
@section('content')
    <x-workspace-heading title="Edit Lantai" description="Lengkapi nama dan deskripsi lantai ruangan.">
        <a class="workspace-button-secondary" href="{{ route('admin.floors.index') }}">Kembali ke Lantai</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <x-workspace-panel title="Informasi Lantai" description="Kolom bertanda * wajib diisi.">
        <form class="workspace-form" method="POST" action="{{ route('admin.floors.update', $floor) }}">
            @csrf
            @method('PUT')
            @include('admin.floors._form', ['editing' => true])
            <div class="workspace-form-actions">
                <button class="workspace-button" type="submit">Simpan</button>
                <a class="workspace-button-secondary" href="{{ route('admin.floors.index') }}">Batal</a>
            </div>
        </form>
    </x-workspace-panel>
@endsection
