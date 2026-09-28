<x-workspace-panel title="Detail Booking #{{ $booking->id }}">
    <div class="p-5 sm:p-6">
        <div class="mb-6"><x-booking-status :status="$booking->status" />
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ match($booking->status) { 'approved' => 'Booking dikonfirmasi. Gunakan ruangan sesuai jadwal.', 'pending' => 'Menunggu persetujuan PIC. Slot waktu ditahan selama pengajuan Pending; belum berarti disetujui.', 'rejected' => 'Pengajuan ditolak. Slot waktu sudah dilepas.', default => 'Lihat status dan jadwal booking di bawah.' } }}</p>
        </div>
        <dl class="workspace-detail">
            <div><dt>Ruangan</dt><dd>{{ $booking->room->name }}</dd></div>
            <div><dt>Pemohon</dt><dd>{{ $booking->applicant->name }}</dd></div>
            <div><dt>Unit Kerja saat Pengajuan</dt><dd>{{ $booking->unit_name ?? 'Belum ditentukan' }}</dd></div>
            <div><dt>Agenda</dt><dd>{{ $booking->agenda }}</dd></div>
            <div><dt>Tanggal</dt><dd>{{ $booking->date }}</dd></div>
            <div><dt>Waktu (WIB)</dt><dd>{{ substr($booking->start_time, 0, 5) }} &ndash; {{ substr($booking->end_time, 0, 5) }}</dd></div>
            <div><dt>Catatan</dt><dd class="whitespace-pre-line">{{ $booking->notes ?? 'Tidak ada catatan.' }}</dd></div>
            @if($booking->rejection_reason)<div><dt>Alasan Penolakan</dt><dd class="whitespace-pre-line">{{ $booking->rejection_reason }}</dd></div>@endif
            @if($booking->decided_at)<div><dt>Waktu Keputusan (WIB)</dt><dd>{{ $booking->decided_at->timezone(config('booking.timezone'))->format('d/m/Y H:i') }}</dd></div>@endif
        </dl>
        <a class="workspace-button-secondary mt-6" href="{{ route(auth()->user()->can('access-admin') ? 'admin.schedule.index' : 'schedule.index', ['room_id' => $booking->room_id, 'date' => $booking->date]) }}">Lihat jadwal ruangan pada tanggal ini</a>
    </div>
</x-workspace-panel>
