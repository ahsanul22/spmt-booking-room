@extends('layouts.base')
@section('title', 'Riwayat Approval')
@section('content')
    <h1>Riwayat Approval</h1>
    <x-skeleton-notice />
    <x-table :headers="['Pemohon', 'Ruangan', 'Agenda', 'Tanggal', 'Status', 'Waktu Keputusan', 'Aksi']">
        @forelse($approvals ?? [] as $item)
            <tr>
                <td>{{ $item->applicant?->name ?? '—' }}</td>
                <td>{{ $item->room?->name ?? '—' }}</td>
                <td>{{ $item->agenda }}</td>
                <td>{{ $item->date }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->decided_at ?? '—' }}</td>
                <td>
                    <a href="{{ route('pic.approvals.show', ['approval' => $item->id, 'from' => 'history']) }}">Detail</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Belum ada riwayat approval.</td>
            </tr>
        @endforelse
    </x-table>
@endsection
