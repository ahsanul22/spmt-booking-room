@extends(auth()->user()->can('access-admin') ? 'layouts.schedule' : 'layouts.base')
@section('breadcrumb', 'Approval Ruangan')
@section('title', auth()->user()->can('access-admin') ? 'Approval Ruangan' : 'Permintaan Approval')
@section('content')
    @can('access-admin')
        @include('admin.approvals.index')
    @else
    <h1>Permintaan Approval</h1>
    <x-skeleton-notice />
    <a href="{{ route('pic.approvals.show', 'preview') }}">Pratinjau Detail Approval</a>
    <x-table :headers="['Pemohon', 'Unit Kerja', 'Ruangan', 'Agenda', 'Tanggal', 'Jam', 'Jumlah Peserta', 'Status', 'Aksi']">
        @forelse($approvals ?? [] as $item)
            <tr>
                <td>{{ $item->applicant?->name ?? '—' }}</td>
                <td>{{ $item->organizationalUnit?->name ?? '—' }}</td>
                <td>{{ $item->room?->name ?? '—' }}</td>
                <td>{{ $item->agenda }}</td>
                <td>{{ $item->date }}</td>
                <td>{{ ($item->start_time ?? '—').' – '.($item->end_time ?? '—') }}</td>
                <td>{{ $item->participant_count }}</td>
                <td>{{ $item->status }}</td>
                <td>
                    <a href="{{ route('pic.approvals.show', $item->id) }}">Detail</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9">Belum ada permintaan approval.</td>
            </tr>
        @endforelse
    </x-table>
    @endcan
@endsection
