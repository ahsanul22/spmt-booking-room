@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.base')
@section('title', 'Daftar Ruangan')
@section('content')
    <h1>Daftar Ruangan</h1>
    <x-skeleton-notice />
    <a href="{{ route('rooms.show', 'preview') }}">Pratinjau Detail Ruangan</a>
    <x-table :headers="['Nama Ruangan', 'Lantai', 'Kapasitas', 'Status Operasional', 'Akses', 'Approval', 'Aksi']">
        @forelse($rooms ?? [] as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->floor?->name ?? '—' }}</td>
                <td>{{ $item->capacity }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->access_type }}</td>
                <td>{{ $item->requires_approval ? 'Ya' : 'Tidak' }}</td>
                <td>
                    <a href="{{ route('rooms.show', $item->id) }}">Detail</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Belum ada data ruangan.</td>
            </tr>
        @endforelse
    </x-table>
@endsection
