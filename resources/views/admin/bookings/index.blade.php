@extends('layouts.schedule')
@section('breadcrumb', 'Semua Booking')
@section('title', 'Semua Booking')
@section('content')
    <x-workspace-heading title="Semua Booking" description="Pantau pengajuan dan status penggunaan ruang rapat lintas unit kerja.">
        <a href="{{ route('admin.schedule.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-surface px-5 py-3 text-sm font-semibold text-primary hover:bg-background"><x-schedule-icon name="calendar" />Lihat Jadwal</a>
    </x-workspace-heading>
    <div class="workspace-preview">
    <div class="workspace-notice"><x-skeleton-notice /></div>
    <section class="mb-6 rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm sm:p-6" aria-labelledby="filter-title">
        <h2 id="filter-title" class="text-lg font-bold text-primaryDark">Filter Booking</h2>
        <p class="mb-5 mt-1 text-xs leading-5 text-slate-600">Pratinjau filter. Pencarian dapat digunakan setelah data booking terhubung.</p>
    <x-skeleton-form>
        <x-field name="date" label="Tanggal" type="date" :value="old('date')" />
        <x-select name="status" label="Status">
            <option value="">Pilih Status</option>
            <option value="" @selected(old('status') === '')>Semua</option>
            <option value="pending" @selected(old('status') === 'pending')>Pending</option>
            <option value="approved" @selected(old('status') === 'approved')>Approved</option>
            <option value="completed" @selected(old('status') === 'completed')>Completed</option>
            <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
            <option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
        </x-select>
        <x-select name="room_id" label="Ruangan">
            <option value="">Pilih Ruangan</option>
            @forelse($rooms ?? [] as $option)
                <option value="{{ $option->id }}" @selected((string) (old('room_id')) === (string) $option->id)>{{ $option->name }}</option>
            @empty
                <option disabled>Belum ada pilihan tersedia.</option>
            @endforelse
        </x-select>
        <x-select name="organizational_unit_id" label="Unit Kerja">
            <option value="">Pilih Unit Kerja</option>
            @forelse($units ?? [] as $option)
                <option value="{{ $option->id }}" @selected((string) (old('organizational_unit_id')) === (string) $option->id)>{{ $option->name }}</option>
            @empty
                <option disabled>Belum ada pilihan tersedia.</option>
            @endforelse
        </x-select>
        <x-pending-action>Terapkan Filter</x-pending-action>
        <a href="{{ route('admin.bookings.index') }}">Batal</a>
    </x-skeleton-form>
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm" aria-labelledby="bookings-title">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:px-6"><h2 id="bookings-title" class="text-lg font-bold text-primaryDark">Daftar Booking</h2><a class="text-sm font-semibold text-primary hover:underline" href="{{ route('admin.bookings.show', 'preview') }}">Pratinjau Detail Booking</a></div>
@if(count($bookings ?? []) > 0)
    <div class="workspace-table" role="region" aria-label="Daftar booking, geser untuk melihat seluruh kolom" tabindex="0">
    <x-table :headers="['Pemohon', 'Unit', 'Ruangan', 'Agenda', 'Tanggal', 'Waktu', 'Status', 'Aksi']">
        @foreach($bookings ?? [] as $item)
            <tr>
                <td>{{ $item->applicant?->name ?? '—' }}</td>
                <td>{{ $item->organizationalUnit?->name ?? '—' }}</td>
                <td>{{ $item->room?->name ?? '—' }}</td>
                <td>{{ $item->agenda }}</td>
                <td>{{ $item->date }}</td>
                <td>{{ ($item->start_time ?? '—').' – '.($item->end_time ?? '—') }}</td>
                <td>{{ $item->status }}</td>
                <td>
                    <a href="{{ route('admin.bookings.show', $item->id) }}">Detail</a>
                </td>
            </tr>
        @endforeach
    </x-table>
    </div>
@else
<x-workspace-empty icon="calendar" title="Belum ada booking." description="Daftar pengajuan akan ditampilkan setelah modul booking terhubung. Data kosong ini belum menunjukkan aktivitas booking sebenarnya." />
@endif
    </section>
    </div>
@endsection
