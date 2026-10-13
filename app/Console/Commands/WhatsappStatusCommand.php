<?php

namespace App\Console\Commands;

use App\Models\WhatsappMessage;
use App\Models\WhatsappRecipient;
use App\Models\WhatsappTemplate;
use Illuminate\Console\Command;

class WhatsappStatusCommand extends Command
{
    protected $signature = 'whatsapp:status';

    protected $description = 'Show WhatsApp configuration and recent outbox statuses without exposing messages';

    public function handle(): int
    {
        $this->info('Pengiriman: '.(config('whatsapp.enabled') ? 'aktif' : 'nonaktif'));
        $this->info('Token: '.(config('whatsapp.token') ? 'tersedia' : 'belum tersedia'));
        $this->table(['Jenis', 'Aktif', 'PIC otomatis'], WhatsappTemplate::all()->map(fn ($template) => [
            $template->label(), $template->is_active ? 'Ya' : 'Tidak', $template->include_room_pics ? 'Ya' : 'Tidak',
        ])->all());
        $this->table(['Tujuan', 'Akun', 'Nomor (disamarkan)', 'Aktif'], WhatsappRecipient::with('user')->get()->map(fn ($recipient) => [
            $recipient->purpose, $recipient->user?->name ?? 'Penerima lama, pilih ulang', '***'.substr($recipient->user?->whatsapp_number ?? $recipient->number ?? '', -4), $recipient->is_active ? 'Ya' : 'Tidak',
        ])->all());
        $this->table(['ID', 'Booking', 'Event', 'Status', 'Percobaan', 'Error'], WhatsappMessage::latest('id')->limit(20)->get()->map(fn ($message) => [
            $message->id, $message->booking_id, $message->event, $message->status, $message->attempts, $message->error_code,
        ])->all());

        return self::SUCCESS;
    }
}
