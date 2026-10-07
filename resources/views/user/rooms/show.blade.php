@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule')
@section('title', 'Detail Ruangan')
@section('breadcrumb', 'Ruangan / Detail')
@section('content')
    <x-workspace-heading title="Detail Ruangan" description="Periksa informasi dan pengaturan ruangan sebelum merencanakan pertemuan.">
        <a class="workspace-button-secondary" href="{{ route('rooms.index') }}">Kembali ke Ruangan</a>
    </x-workspace-heading>
    <x-workspace-panel :title="$room->name">
        <div class="space-y-6 p-5 sm:p-6">
            <dl class="workspace-detail">
                <div><dt>Kode</dt><dd>{{ $room->code ?? 'Belum ditentukan' }}</dd></div>
                <div><dt>Lantai</dt><dd>{{ $room->floor?->name ?? 'Belum ditentukan' }}</dd></div>
                <div><dt>Status Operasional</dt><dd>{{ ['available' => 'Operasional', 'maintenance' => 'Dalam perawatan', 'unavailable' => 'Tidak dapat digunakan'][$room->status] }}</dd></div>
                <div><dt>Jenis Akses</dt><dd>{{ $room->access_type === 'all' ? 'Semua pegawai' : 'Unit tertentu' }}</dd></div>
                <div><dt>Persetujuan</dt><dd>{{ $room->requires_approval ? 'Memerlukan approval PIC' : 'Tanpa approval PIC' }}</dd></div>
                <div><dt>Status Aktif</dt><dd><x-workspace-status :active="$room->is_active" /></dd></div>
                <div class="sm:col-span-2"><dt>Deskripsi</dt><dd class="whitespace-pre-line">{{ $room->description ?: 'Belum ada deskripsi.' }}</dd></div>
            </dl>
            <section><h2 class="text-lg font-bold text-primaryDark">Fasilitas</h2><ul class="mt-3 flex flex-wrap gap-3">@forelse($room->facilities as $facility)<li class="max-w-full break-words rounded-xl bg-background px-4 py-3">{{ $facility->name }}</li>@empty<li>Belum ada fasilitas.</li>@endforelse</ul></section>
            @can('access-pic')
            <section><h2 class="text-lg font-bold text-primaryDark">PIC dan Akses Ruangan</h2>
                <p class="mt-3">PIC: {{ $room->pics->pluck('name')->join(', ') ?: 'Belum ditetapkan' }}</p>
                <p class="mt-2">Unit akses: {{ $room->access_type === 'all' ? 'Semua pegawai' : ($room->allowedOrganizationalUnits->pluck('name')->join(', ') ?: 'Belum ditetapkan') }}</p>
                @can('access-admin')<a class="workspace-button mt-4" href="{{ route('admin.rooms.show', $room) }}">Detail / Edit Ruangan</a>@else<p class="mt-3 text-sm text-slate-600">Perubahan data, PIC, akses, dan status ruangan dikelola oleh Admin.</p>@endcan
            </section>
            @endcan
            @if($reason)<p class="rounded-xl bg-background p-4 leading-7">{{ $reason }}</p>@endif
            <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-5">
                <a class="workspace-button-secondary" href="{{ route(auth()->user()->can('access-admin') ? 'admin.schedule.index' : 'schedule.index', ['room_id' => $room->id]) }}">Lihat Jadwal</a>
                @can('access-employee')
                    @unless($reason)<a class="workspace-button" href="{{ route('rooms.book', $room) }}">Booking Ruangan</a>@endunless
                @endcan
            </div>
        </div>
    </x-workspace-panel>
@endsection
