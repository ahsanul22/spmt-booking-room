<dl>
    <dt>Nama</dt>
    <dd>{{ data_get($room ?? null, 'name') ?? '—' }}</dd>
    <dt>Kode</dt>
    <dd>{{ data_get($room ?? null, 'code') ?? '—' }}</dd>
    <dt>Lantai</dt>
    <dd>{{ data_get($room ?? null, 'floor.name') ?? '—' }}</dd>
    <dt>Kapasitas</dt>
    <dd>{{ data_get($room ?? null, 'capacity') ?? '—' }}</dd>
    <dt>Deskripsi</dt>
    <dd>{{ data_get($room ?? null, 'description') ?? '—' }}</dd>
    <dt>Status Operasional</dt>
    <dd>{{ data_get($room ?? null, 'status') ?? '—' }}</dd>
    <dt>Jenis Akses</dt>
    <dd>{{ data_get($room ?? null, 'access_type') ?? '—' }}</dd>
</dl>
<p>Requires Approval: {{ isset($room) ? ($room->requires_approval ? 'Ya' : 'Tidak') : '—' }}</p>
<h2>Fasilitas</h2>
<ul>
    @forelse($room->facilities ?? [] as $facility)
        <li>{{ $facility->name }}</li>
    @empty
        <li>Belum ada fasilitas.</li>
    @endforelse
</ul>
