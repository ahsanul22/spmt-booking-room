<div><dt>Persiapan Rapat</dt><dd>{{ $booking->meeting_preparation ? 'Diminta' : 'Tidak diminta' }}</dd></div>
@if($booking->meeting_preparation)
    <div><dt>Perkiraan Jumlah Peserta</dt><dd>{{ $booking->participant_count }} orang</dd></div>
    <div><dt>Konsumsi</dt><dd>{{ $booking->needs_consumption ? 'Diperlukan' : 'Tidak diperlukan' }}</dd></div>
@endif
