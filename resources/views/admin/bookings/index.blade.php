@extends('layouts.schedule')
@section('breadcrumb', 'Semua Booking')
@section('title', 'Semua Booking')
@section('content')
    <x-workspace-heading title="Semua Booking" description="Pengajuan dan status penggunaan ruang rapat seluruh pegawai." />
    <x-workspace-panel title="Daftar Booking">@include('shared.bookings-table', ['detailRoute' => 'admin.bookings.show'])</x-workspace-panel>
@endsection
