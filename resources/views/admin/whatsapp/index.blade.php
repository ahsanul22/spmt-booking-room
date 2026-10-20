@extends('layouts.schedule')
@section('title', 'Notifikasi')
@section('breadcrumb', 'Notifikasi')
@section('content')
    <x-workspace-heading title="Notifikasi" description="Atur notifikasi WhatsApp, pilih penerima, lalu simpan pengaturan.">
        <a class="workspace-button-secondary" href="{{ route('admin.users.index') }}">Kelola nomor pengguna</a>
    </x-workspace-heading>
    <div class="workspace-feedback"><x-form-feedback /></div>
    @if(! $enabled || ! $hasToken)
        <p role="status" class="mb-6 rounded-xl border border-warning bg-surface p-4 text-warningDark">Pengiriman belum aktif. Hubungi pengelola untuk melengkapi konfigurasi WhatsApp.</p>
    @endif
    @if($legacyRecipients->isNotEmpty())
        <p role="status" class="mb-6 rounded-xl border border-warning bg-surface p-4 text-warningDark">Pilih ulang penerima uji coba dari akun yang nomornya sudah diisi.</p>
    @endif
    <div class="grid items-start gap-6 xl:grid-cols-2">
        @foreach($templates as $template)
            @include('admin.whatsapp._settings')
        @endforeach
    </div>
    <details id="pic-readiness" class="mt-6 rounded-2xl border border-slate-200 bg-surface">
        <summary class="cursor-pointer p-5 font-semibold text-primaryDark">Periksa nomor PIC</summary>
            <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6 xl:grid-cols-3">
                @forelse($pics as $room)
                    <div class="rounded-xl border border-slate-200 p-4"><a class="font-semibold text-primary" href="{{ route('admin.rooms.edit', $room) }}">{{ $room->name }}</a>
                        @forelse($room->pics as $pic)<p class="mt-2 break-words">{{ $pic->name }}: {{ $pic->whatsapp_number ?? 'Nomor belum diisi' }}@if(! \App\Support\WhatsappNumber::valid($pic->whatsapp_number))<a class="mt-1 block text-sm font-semibold text-primary underline" href="{{ route('admin.users.edit', $pic) }}">Isi nomor PIC</a>@endif</p>@empty<p class="mt-2 text-warningDark">PIC aktif belum ditetapkan.</p>@endforelse
                    </div>
                @empty<p class="text-slate-600">Belum ada ruangan aktif yang membutuhkan approval.</p>@endforelse
            </div>
    </details>
    <div class="mt-6">
        <x-workspace-panel title="Riwayat pengiriman">
            <livewire:listing-filters destination="admin.whatsapp.index" kind="whatsapp" />
            <div class="workspace-table overflow-x-auto">
                <table class="w-full text-left text-base"><caption class="sr-only">Riwayat pesan WhatsApp</caption><thead class="bg-background text-primaryDark"><tr><th class="p-4">Pesan / Booking</th><th class="p-4">Penerima</th><th class="p-4">Status</th><th class="p-4">Isi / aksi</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($messages as $message)
                            <tr><td class="p-4 align-top">#{{ $message->id }}<a class="mt-2 block text-primary" href="{{ route('admin.bookings.show', $message->booking_id) }}">Booking #{{ $message->booking_id }}</a><span class="mt-1 block text-sm">{{ $message->event === 'approval_request' ? 'Pengajuan approval' : 'Konsumsi' }}</span></td>
                                <td class="p-4 align-top"><span class="block">{{ $message->recipient?->name ?? 'Penerima lama / akun tidak tersedia' }}</span><span class="block">{{ $message->number }}</span></td>
                                <td class="p-4 align-top"><span class="font-semibold">{{ $message->status }}</span><span class="mt-1 block text-sm">Percobaan: {{ $message->attempts }}</span>@if($message->error_code)<span class="mt-1 block break-words text-sm text-danger">{{ $message->error_code }}</span>@endif<span class="mt-1 block whitespace-nowrap text-sm text-slate-600">{{ $message->created_at->timezone(config('booking.timezone'))->format('d/m/Y H:i') }} WIB</span></td>
                                <td class="p-4 align-top"><details><summary class="cursor-pointer text-primary">Lihat isi pesan</summary><p class="mt-3 min-w-[220px] max-w-lg whitespace-pre-wrap break-words">{{ $message->message }}</p></details>
                                    @if($message->status === 'failed')<form method="POST" action="{{ route('admin.whatsapp.retry', $message) }}" class="mt-3">@csrf<button type="submit" class="workspace-button-secondary">Antrekan ulang</button></form>@endif
                                </td></tr>
                        @empty<tr><td colspan="4" class="p-6 text-slate-600">Belum ada pesan untuk filter ini.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-5 sm:p-6"><p class="mb-3 text-sm text-slate-600">Accepted: diterima layanan, belum bukti sampai atau dibaca.</p>{{ $messages->links() }}</div>
        </x-workspace-panel>
    </div>
@endsection
