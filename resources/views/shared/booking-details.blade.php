<dl>
    <dt>ID Booking</dt>
    <dd>{{ data_get($booking ?? null, 'id') ?? '—' }}</dd>
    <dt>Pemohon</dt>
    <dd>{{ data_get($booking ?? null, 'applicant.name') ?? '—' }}</dd>
    <dt>Unit Kerja</dt>
    <dd>{{ data_get($booking ?? null, 'organizationalUnit.name') ?? '—' }}</dd>
    <dt>Ruangan</dt>
    <dd>{{ data_get($booking ?? null, 'room.name') ?? '—' }}</dd>
    <dt>Agenda</dt>
    <dd>{{ data_get($booking ?? null, 'agenda') ?? '—' }}</dd>
    <dt>Tanggal</dt>
    <dd>{{ data_get($booking ?? null, 'date') ?? '—' }}</dd>
    <dt>Jam Mulai</dt>
    <dd>{{ data_get($booking ?? null, 'start_time') ?? '—' }}</dd>
    <dt>Jam Selesai</dt>
    <dd>{{ data_get($booking ?? null, 'end_time') ?? '—' }}</dd>
    <dt>Jumlah Peserta</dt>
    <dd>{{ data_get($booking ?? null, 'participant_count') ?? '—' }}</dd>
    <dt>Catatan</dt>
    <dd>{{ data_get($booking ?? null, 'notes') ?? '—' }}</dd>
    <dt>Status</dt>
    <dd>{{ data_get($booking ?? null, 'status') ?? '—' }}</dd>
    <dt>Alasan Rejection</dt>
    <dd>{{ data_get($booking ?? null, 'rejection_reason') ?? '—' }}</dd>
</dl>
