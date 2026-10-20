@extends('layouts.visitor')
@section('title', 'Jadwal Ruangan untuk Tamu')
@section('content')
    <div class="mb-6 rounded-2xl border border-secondaryLight bg-surface p-5">
        <p class="text-lg font-semibold text-primaryDark">{{ $guest->name }} · {{ $guest->organization }}</p>
        <p class="mt-2">Silakan temui resepsionis dan sebutkan nama serta instansi Anda untuk dibantu mengajukan booking.</p>
        <p class="mt-2">Aktif sampai {{ $guest->expires_at->setTimezone(config('booking.timezone'))->format('d/m/Y H:i') }} WIB.</p>
    </div>
    <form method="GET" action="{{ route('guests.schedule') }}" class="mb-6 flex flex-wrap items-end gap-4">
        <x-workspace-field name="date" label="Tanggal (WIB)" type="date" :value="$date" required />
        <button type="submit" class="workspace-button">Tampilkan</button>
    </form>
    <p class="mb-5 leading-7">Jadwal menampilkan ruangan umum di kantor tujuan. Jam Pending dan Approved belum dapat dipesan. Waktu kosong tetap perlu diperiksa resepsionis; ketersediaan diperiksa ulang saat pengajuan.</p>
    <div class="grid gap-5 md:grid-cols-2">
        @forelse($rooms as $room)
            <section class="min-w-0 rounded-2xl border border-slate-200 bg-surface p-5">
                <h2 class="text-xl font-bold text-primaryDark">{{ $room->name }}</h2>
                <p class="mt-2">{{ $room->floor?->name }} · {{ match($room->status) { 'available' => 'Operasional', 'maintenance' => 'Dalam pemeliharaan', default => 'Tidak tersedia' } }}</p>
                <ul class="mt-4 space-y-3">
                    @forelse($slots->get($room->id, collect()) as $slot)
                        <li class="rounded-lg border border-secondaryLight p-3">{{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }} WIB · {{ $slot->status === 'pending' ? 'Pending' : 'Approved' }}</li>
                    @empty
                        <li>Belum ada booking aktif pada tanggal ini.</li>
                    @endforelse
                </ul>
            </section>
        @empty
            <p class="rounded-2xl bg-surface p-5">Belum ada ruangan umum yang dapat ditampilkan. Silakan hubungi resepsionis.</p>
        @endforelse
    </div>
@endsection
