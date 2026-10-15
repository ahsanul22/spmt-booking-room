<div><dt>Persiapan Rapat</dt><dd>{{ $booking->meeting_preparation ? 'Diminta' : 'Tidak diminta' }}</dd></div>
@if($booking->meeting_preparation)
    <div><dt>Perkiraan Jumlah Peserta</dt><dd>{{ $booking->participant_count }} orang</dd></div>
    <div><dt>Permintaan konsumsi</dt><dd>@if($booking->consumption_snack || $booking->consumption_meal){{ collect([($booking->consumption_snack ? 'Snack' : null), ($booking->consumption_meal ? 'Makanan' : null)])->filter()->implode(', ') }}@else Tidak diperlukan @endif</dd></div>
@endif

@if($booking->meeting_preparation)
    <div><dt>Konsumsi disetujui</dt><dd>{{ $booking->status === 'pending' ? 'Menunggu review PIC / Admin' : (collect([$booking->approved_consumption_snack ? 'Snack' : null, $booking->approved_consumption_meal ? 'Makanan' : null])->filter()->implode(', ') ?: 'Tidak ada konsumsi yang disetujui') }}</dd></div>
@endif
