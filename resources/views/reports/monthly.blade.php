@extends($printing ? 'layouts.report' : 'layouts.schedule')
@section('title', 'Laporan Bulanan')
@section('breadcrumb', 'Laporan Bulanan')
@section('content')
    <x-workspace-heading title="Laporan Bulanan" description="Rekap booking berdasarkan tanggal rapat untuk pelaporan setiap bulan.">
        <div class="flex flex-wrap gap-3 print:hidden">
            @if($printing)
                <button type="button" data-print-report class="workspace-button">Cetak / Simpan PDF</button>
                <a class="workspace-button-secondary" href="{{ route($routePrefix.'.reports.index', ['month' => $month->format('Y-m'), 'room_id' => $roomId, 'branch_id' => $branchId]) }}">Kembali ke Laporan</a>
            @else
                <a download class="workspace-button-secondary" href="{{ route($routePrefix.'.reports.export', ['month' => $month->format('Y-m'), 'room_id' => $roomId, 'branch_id' => $branchId]) }}">Unduh CSV</a>
                <a class="workspace-button" href="{{ route($routePrefix.'.reports.print', ['month' => $month->format('Y-m'), 'room_id' => $roomId, 'branch_id' => $branchId]) }}">Versi Cetak / PDF</a>
            @endif
        </div>
    </x-workspace-heading>
    @unless($printing)
        <div class="workspace-feedback"><x-form-feedback /></div>
        <livewire:listing-filters :destination="$routePrefix.'.reports.index'" kind="report" />
    @endunless
    {{-- <section class="mb-6 rounded-2xl border border-secondaryLight bg-secondaryLight/20 p-5" aria-label="Periode dan cakupan laporan">
        <h2 class="text-xl font-bold text-primaryDark">{{ $month->locale('id')->translatedFormat('F Y') }}</h2>
        <p class="mt-2 break-words">{{ $scope }}{{ $selectedRoom ? ' · '.$selectedRoom->name : '' }}</p>
        <p class="mt-2 text-base text-slate-600">Dibuat oleh {{ auth()->user()->name }} · {{ $generatedAt->format('d/m/Y H:i') }} WIB. Status mengikuti data saat laporan dibuka.</p>
        <p class="mt-2 text-base leading-6 text-slate-600">Jam terjadwal adalah total durasi Approved dan Completed, bukan bukti pemakaian aktual. Pending yang waktunya lewat tetap dihitung sebagai Pending. Perubahan status atau penugasan PIC dapat mengubah laporan berikutnya.</p>
    </section> --}}
    <section class="mb-6 rounded-2xl border border-secondaryLight bg-surface p-5" aria-label="Periode laporan">
        <h2 class="text-xl font-bold text-primaryDark">{{ $month->locale('id')->translatedFormat('F Y') }}</h2>
        <p class="mt-2">{{ $branchId ? $branches->firstWhere('id', $branchId)?->name : $scope }}{{ $selectedRoom ? ' · '.$selectedRoom->name : '' }}</p>
        <p class="mt-2 text-sm text-slate-600">Jumlah peserta merupakan perkiraan saat pengajuan. Kebutuhan dan persetujuan dicatat terpisah; bukan bukti layanan telah diberikan.</p>
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
                            <tr><th scope="row">{{ $row['room']->name }}<p class="mt-1 text-sm font-normal text-slate-600">{{ $row['room']->branch?->name }}</p></th>@foreach($statuses as $status => $label)<td>{{ $row['counts'][$status] }}</td>@endforeach<td>{{ $row['total'] }}</td><td>{{ number_format($row['hours'], 2, ',', '.') }}</td></tr>
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
                    <thead><tr><th scope="col">Tanggal / Jam WIB</th><th scope="col">Ruangan</th><th scope="col">Peminta / Pengaju / Unit</th><th scope="col">Agenda</th><th scope="col">Status</th><th scope="col">Peserta / Kebutuhan</th><th scope="col">Catatan</th></tr></thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>{{ \Carbon\CarbonImmutable::parse($booking->date)->format('d/m/Y') }}<p class="mt-1 text-base">{{ substr($booking->start_time, 0, 5) }}–{{ substr($booking->end_time, 0, 5) }}</p></td>
                                <td>{{ $booking->room->name }}<p class="mt-1 text-sm text-slate-600">{{ $booking->room->branch->name }}</p></td>
                                <td>@if($booking->guest_name)<p class="font-semibold">Tamu: {{ $booking->guest_name }}</p><p>{{ $booking->guest_organization }} &middot; {{ $booking->guest_reference }}</p><p class="mt-2">Diajukan oleh: {{ $booking->applicant->name }}</p>@else{{ $booking->applicant->name }}@endif<p class="mt-1 text-base text-slate-600">{{ $booking->unit_name ?? 'Unit belum ditentukan' }}</p></td>
                                <td>{{ $booking->agenda }}</td><td><x-booking-status :status="$booking->status" /></td>
                                <td><dl class="space-y-2">@include('shared.meeting-preparation-details')</dl></td>
                                <td>{{ $booking->notes ?: '—' }}<p class="mt-2">Keputusan: {{ $booking->decision_notes ?: '—' }}</p></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-workspace-empty title="Belum ada booking pada periode ini." description="Pilih bulan atau ruangan lain untuk melihat laporan." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @unless($printing)<div class="p-5">{{ $bookings->links() }}</div>@endunless
        </x-workspace-panel>
    </div>
@endsection
