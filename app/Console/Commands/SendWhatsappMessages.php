<?php

namespace App\Console\Commands;

use App\Models\WhatsappMessage;
use App\Services\WhatsappDelivery;
use App\Services\WhatsappSettings;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class SendWhatsappMessages extends Command
{
    protected $signature = 'whatsapp:send {--retry= : ID pesan dengan status failed}';

    protected $description = 'Process pending WhatsApp outbox messages';

    public function handle(WhatsappDelivery $delivery): int
    {
        if (! config('whatsapp.enabled') || ! config('whatsapp.token')) {
            $this->warn('WhatsApp nonaktif atau token belum tersedia.');

            return self::FAILURE;
        }
        if ($id = $this->option('retry')) {
            if (! ctype_digit((string) $id) || ! WhatsappMessage::whereKey($id)->exists()) {
                $this->error('Hanya pesan failed yang dapat dicoba ulang.');

                return self::FAILURE;
            }
            try {
                app(WhatsappSettings::class)->retry((int) $id);
            } catch (ValidationException $error) {
                $this->error($error->getMessage());

                return self::FAILURE;
            }
        }
        $ids = WhatsappMessage::where('status', 'pending')->orderBy('id')->limit(50)->pluck('id');
        foreach ($ids as $id) {
            $delivery->send($id);
        }
        $this->info('Pemrosesan antrean selesai. Periksa whatsapp_messages untuk hasil per pesan.');

        return self::SUCCESS;
    }
}
