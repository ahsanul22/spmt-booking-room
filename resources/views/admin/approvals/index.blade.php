<x-workspace-heading title="Approval Ruangan" description="Tinjau pengajuan penggunaan ruangan yang memerlukan persetujuan PIC.">
    <a href="{{ route('admin.rooms.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-surface px-5 py-3 text-sm font-semibold text-primary hover:bg-background"><x-schedule-icon name="room" />Kelola Ruangan</a>
</x-workspace-heading>
<div class="workspace-preview">
    <div class="workspace-notice"><x-skeleton-notice /></div>
    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_280px]">
        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200/80 bg-surface shadow-sm" aria-labelledby="approval-title">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6"><div><h2 id="approval-title" class="text-lg font-bold text-primaryDark">Permintaan Approval</h2><p class="mt-1 text-xs leading-5 text-slate-600">Pengajuan dan keputusan belum terhubung.</p></div><span class="rounded-md bg-background px-3 py-1 text-xs font-medium text-slate-600">Pratinjau</span></div>
@if(count($approvals ?? []) > 0)
            <div class="workspace-table" role="region" aria-label="Permintaan approval, geser untuk melihat seluruh kolom" tabindex="0">
                <x-table :headers="['Pemohon / Unit', 'Ruangan / Agenda', 'Jadwal', 'Peserta', 'Status', 'Aksi']">
                    @foreach($approvals ?? [] as $item)
                        <tr>
                            <td><p class="font-semibold">{{ $item->applicant?->name ?? '—' }}</p><p class="mt-1 text-xs text-slate-600">{{ $item->organizationalUnit?->name ?? '—' }}</p></td>
                            <td><p class="font-semibold">{{ $item->room?->name ?? '—' }}</p><p class="mt-1 text-xs text-slate-600">{{ $item->agenda }}</p></td>
                            <td>{{ $item->date }}<p class="mt-1 text-xs text-slate-600">{{ ($item->start_time ?? '—').' – '.($item->end_time ?? '—') }}</p></td>
                            <td>{{ $item->participant_count }}</td><td>{{ $item->status }}</td>
                            <td><a class="font-semibold text-primary hover:underline" href="{{ route('pic.approvals.show', $item->id) }}">Detail</a></td>
                        </tr>
                    @endforeach
                </x-table>
            </div>
@else
<x-workspace-empty icon="clock" title="Belum ada permintaan approval." description="Permintaan akan muncul setelah modul approval terhubung. Persetujuan dan penolakan belum dapat diproses.">
                            <a class="mt-5 inline-flex rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-primary hover:bg-background" href="{{ route('pic.approvals.show', 'preview') }}">Pratinjau Detail Approval</a>
                        </x-workspace-empty>
@endif
        </section>
        <aside class="space-y-5">
            <section class="rounded-2xl bg-primaryDark p-6 text-white" aria-labelledby="flow-title">
                <span class="inline-flex rounded-xl bg-white/10 p-3 text-secondaryLight"><x-schedule-icon name="clock" /></span>
                <h2 id="flow-title" class="mt-5 text-lg font-semibold">Alur Persetujuan</h2>
                <ol class="mt-4 list-decimal space-y-4 pl-4 text-sm leading-6 text-secondaryLight"><li>Pegawai mengajukan booking ruangan yang memerlukan approval.</li><li>PIC ruangan meninjau pengajuan.</li><li>Status diperbarui setelah keputusan diberikan.</li></ol>
            </section>
            <section class="rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm"><h2 class="font-bold text-primaryDark">Pengaturan Ruangan</h2><p class="mt-2 text-sm leading-6 text-slate-600">Atur kebutuhan approval dan penanggung jawab melalui data ruangan. Akses halaman ini tidak memberikan kewenangan override keputusan.</p><a class="mt-4 inline-flex py-1 text-sm font-semibold text-primary hover:underline" href="{{ route('admin.rooms.index') }}">Buka Daftar Ruangan &rarr;</a></section>
        </aside>
    </div>
</div>
