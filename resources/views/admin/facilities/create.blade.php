@extends('layouts.schedule')
@section('breadcrumb', 'Fasilitas')
@section('title', 'Tambah Fasilitas')
@section('content')
    <x-workspace-heading title="Tambah Fasilitas" description="Lengkapi nama dan deskripsi fasilitas ruangan.">
        <a class="workspace-button-secondary" href="{{ route('admin.facilities.index') }}">Kembali ke Fasilitas</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    <x-workspace-panel title="Informasi Fasilitas" description="Kolom bertanda * wajib diisi.">
        <form class="workspace-form" method="POST" action="{{ route('admin.facilities.store') }}">
            @csrf
            @include('admin.facilities._form', ['editing' => false])
            <div class="workspace-form-actions">
                <button class="workspace-button" type="submit">Simpan</button>
                <a class="workspace-button-secondary" href="{{ route('admin.facilities.index') }}">Batal</a>
            </div>
        </form>
    </x-workspace-panel>
@endsection
