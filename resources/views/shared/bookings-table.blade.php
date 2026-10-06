@if($bookings->isEmpty())
    <x-workspace-empty icon="calendar" :title="$emptyTitle ?? 'Belum ada booking.'" description="Pengajuan yang sesuai akan muncul di sini setelah dikirim." />
@else
    <div class="workspace-table" role="region" aria-label="Daftar booking, geser untuk melihat seluruh kolom" tabindex="0">
        <x-table :headers="['Ruangan / Agenda', 'Pemohon / Unit', 'Tanggal', 'Waktu (WIB)', 'Status', 'Aksi']">
            @foreach($bookings as $item)
                <tr>
                    <td><p class="font-semibold text-primaryDark">{{ $item->room->name }}</p><p class="mt-1">{{ $item->agenda }}</p></td>
                    <td>{{ $item->applicant->name }}<p class="mt-1 text-sm text-slate-600">{{ $item->unit_name ?? 'Unit belum ditentukan' }}</p></td>
                    <td>{{ $item->date }}</td><td>{{ substr($item->start_time, 0, 5) }} &ndash; {{ substr($item->end_time, 0, 5) }}</td>
                    <td><x-booking-status :status="$item->status" /></td>
                    <td>
                        @if(($quickDecisions ?? false) && $item->status === 'pending')
                            <button hidden type="button" data-approval-dialog-trigger="approval-{{ $item->id }}" class="workspace-button" aria-haspopup="dialog" aria-label="Review pengajuan {{ $item->id }}">Review</button>
                            <noscript><a href="{{ route($detailRoute, $item->id) }}">Review pengajuan</a></noscript>
                        @else
                        <a class="font-semibold hover:underline" href="{{ route($detailRoute, $item->id) }}">Detail<span class="sr-only"> booking {{ $item->id }}</span></a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>
    <div class="p-5">{{ $bookings->links() }}</div>
@endif
