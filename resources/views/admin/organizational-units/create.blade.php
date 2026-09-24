@extends('layouts.schedule')
@section('breadcrumb', 'Unit Organisasi')
@section('title', 'Tambah Unit Organisasi')
@section('content')
    <x-workspace-heading title="Tambah Unit Organisasi" description="Tempatkan unit pada hierarki organisasi yang sesuai.">
        <a class="workspace-button-secondary" href="{{ route('admin.organizational-units.index') }}">Kembali ke Unit Organisasi</a>
    </x-workspace-heading>
    <div class="workspace-feedback">@include('admin.organizational-units._feedback')</div>
    <x-workspace-panel title="Informasi Unit" description="Kolom bertanda * wajib diisi.">
        <form class="workspace-form" method="POST" action="{{ route('admin.organizational-units.store') }}">
            @csrf
            @include('admin.organizational-units._form', ['editing' => false])
            <div class="workspace-form-actions">
                <button class="workspace-button" type="submit">Simpan</button>
                <a class="workspace-button-secondary" href="{{ route('admin.organizational-units.index') }}">Batal</a>
            </div>
        </form>
    </x-workspace-panel>
@endsection
