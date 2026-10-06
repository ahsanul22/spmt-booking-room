@extends($printing ? 'layouts.report' : 'layouts.schedule')
@section('title', 'Laporan Bulanan')
@section('breadcrumb', 'Laporan Bulanan')
@section('content')
    <x-workspace-heading title="Laporan Bulanan" description="Rekap booking berdasarkan tanggal rapat untuk pelaporan setiap bulan.">
        <div class="flex flex-wrap gap-3 print:hidden">
            @if($printing)
                <button type="button" data-print-report class="workspace-button">Cetak / Simpan PDF</button>
                <a class="workspace-button-secondary" href="{{ route($routePrefix.'.reports.index', ['month' => $month->format('Y-m'), 'room_id' => $roomId]) }}">Kembali ke Laporan</a>
            @else
                <a class="workspace-button-secondary" href="{{ route($routePrefix.'.reports.export', ['month' => $month->format('Y-m'), 'room_id' => $roomId]) }}">Unduh CSV</a>
                <a class="workspace-button" href="{{ route($routePrefix.'.reports.print', ['month' => $month->format('Y-m'), 'room_id' => $roomId]) }}">Versi Cetak / PDF</a>
            @endif
        </div>
    </x-workspace-heading>
    @unless($printing)
        <div class="workspace-feedback"><x-form-feedback /></div>
        <form method="GET" action="{{ route($routePrefix.'.reports.index') }}" class="mb-6 grid items-end gap-5 rounded-2xl border border-slate-200 bg-surface p-5 sm:grid-cols-2 xl:grid-cols-[1fr_2fr_auto]">
            <x-workspace-field type="month" name="month" label="Bulan rapat" required min="1000-01" max="9998-12" :value="old('month', $month->format('Y-m'))" />
            <x-workspace-field type="select" name="room_id" label="Ruangan">
                <option value="">Semua ruangan dalam cakupan Anda</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" @selected((string) old('room_id', $roomId) === (string) $room->id)>{{ $room->name }}{{ $room->is_active ? '' : ' - Nonaktif' }}</option>
                @endforeach
            </x-workspace-field>
            <button class="workspace-button" type="submit">Tampilkan Laporan</button>
        </form>
    @endunless
    <section class="mb-6 rounded-2xl border border-secondaryLight bg-secondaryLight/20 p-5" aria-label="Periode dan cakupan laporan">
        <h2 class="text-xl font-bold text-primaryDark">{{ $month->locale('id')->translatedFormat('F Y') }}</h2>
        <p class="mt-2 break-words">{{ $scope }}{{ $selectedRoom ? ' · '.$selectedRoom->name : '' }}</p>
        <p class="mt-2 text-base text-slate-600">Dibuat oleh {{ auth()->user()->name }} · {{ $generatedAt->format('d/m/Y H:i') }} WIB. Status mengikuti data saat laporan dibuka.</p>
        <p class="mt-2 text-base leading-6 text-slate-600">Jam terjadwal adalah total durasi Approved dan Completed, bukan bukti pemakaian aktual. Pending yang waktunya lewat tetap dihitung sebagai Pending. Perubahan status atau penugasan PIC dapat mengubah laporan berikutnya.</p>
    </section>
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-workspace-panel title="Total Booking"><p class="p-5 text-3xl font-bold text-primaryDark">{{ $summary['total'] }}</p></x-workspace-panel>
        <x-workspace-panel title="Jam Terjadwal"><p class="p-5 text-3xl font-bold text-primaryDark">{{ number_format($summary['hours'], 2, ',', '.') }}</p></x-workspace-panel>
        <x-workspace-panel title="Approved / Completed"><p class="p-5 text-3xl font-bold text-primaryDark">{{ $summary['counts']['approved'] + $summary['counts']['completed'] }}</p></x-workspace-panel>
    </div>
    <div class="space-y-6">
        <x-workspace-panel title="Rekap per Ruangan" description="Seluruh status dihitung, termasuk pengajuan ditolak dan dibatalkan.">
            <div class="workspace-table" tabindex="0" role="region" aria-label="Rekap bulanan per ruangan">
                <table>
                    <thead><tr><th scope="col">Ruangan</th>@foreach($statuses as $label)<th scope="col">{{ $label }}</th>@endforeach<th scope="col">Total</th><th scope="col">Jam terjadwal</th></tr></thead>
                    <tbody>
                        @forelse($summary['byRoom'] as $row)
                            <tr><th scope="row">{{ $row['room']->name }}</th>@foreach($statuses as $status => $label)<td>{{ $row['counts'][$status] }}</td>@endforeach<td>{{ $row['total'] }}</td><td>{{ number_format($row['hours'], 2, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="8"><x-workspace-empty title="Belum ada ruangan dalam cakupan Anda." description="PIC hanya dapat melaporkan ruangan yang ditugaskan kepadanya." /></td></tr>
                        @endforelse
                    </tbody>
                    <tfoot><tr><th scope="row">Total</th>@foreach($statuses as $status => $label)<td>{{ $summary['counts'][$status] }}</td>@endforeach<td>{{ $summary['total'] }}</td><td>{{ number_format($summary['hours'], 2, ',', '.') }}</td></tr></tfoot>
                </table>
            </div>
        </x-workspace-panel>
        <x-workspace-panel title="Rincian Booking" description="CSV dan versi cetak mencakup semua booking sesuai filter, bukan hanya halaman yang terlihat.">
            <div class="workspace-table" tabindex="0" role="region" aria-label="Rincian booking bulanan">
                <table>
                    <thead><tr><th scope="col">Tanggal / Jam WIB</th><th scope="col">Ruangan</th><th scope="col">Pemohon / Unit</th><th scope="col">Agenda</th><th scope="col">Status</th></tr></thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>{{ \Carbon\CarbonImmutable::parse($booking->date)->format('d/m/Y') }}<p class="mt-1 text-base">{{ substr($booking->start_time, 0, 5) }}–{{ substr($booking->end_time, 0, 5) }}</p></td>
                                <td>{{ $booking->room->name }}</td>
                                <td>{{ $booking->applicant->name }}<p class="mt-1 text-base text-slate-600">{{ $booking->unit_name ?? 'Unit belum ditentukan' }}</p></td>
                                <td>{{ $booking->agenda }}</td><td><x-booking-status :status="$booking->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-workspace-empty title="Belum ada booking pada periode ini." description="Pilih bulan atau ruangan lain untuk melihat laporan." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @unless($printing)<div class="p-5">{{ $bookings->links() }}</div>@endunless
        </x-workspace-panel>
    </div>
@endsection
