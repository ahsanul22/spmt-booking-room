<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WhatsappRecipient;
use App\Models\WhatsappTemplate;
use App\Support\WhatsappNumber;
use Illuminate\Console\Command;

class WhatsappRecipientCommand extends Command
{
    protected $signature = 'whatsapp:recipient {purpose : approval_request or consumption} {user? : ID akun penerima} {--disable}';

    protected $description = 'Select recipients from user accounts; manage templates through Admin WhatsApp settings';

    public function handle(): int
    {
        $purpose = $this->argument('purpose');
        if (! in_array($purpose, ['approval_request', 'consumption'], true)) {
            $this->error('Purpose harus approval_request atau consumption.');

            return self::FAILURE;
        }
        if ($this->option('disable')) {
            WhatsappRecipient::where('purpose', $purpose)->update(['is_active' => false]);
            WhatsappTemplate::where('event', $purpose)->update(['is_active' => false]);

            return self::SUCCESS;
        }
        $id = $this->argument('user');
        $user = ctype_digit((string) $id) ? User::find($id) : null;
        if (! $user || ! $user->is_active || ! WhatsappNumber::valid($user->whatsapp_number)
            || ($purpose === 'approval_request' && $user->role !== User::ROLE_SUPER_ADMIN)) {
            $this->error('Pilih akun aktif dengan nomor valid (Admin untuk approval). Gunakan halaman /admin/whatsapp atau sertakan ID user.');

            return self::FAILURE;
        }
        WhatsappRecipient::updateOrCreate(['purpose' => $purpose, 'user_id' => $user->id], ['number' => null, 'is_active' => true]);
        $this->info('Akun penerima tersimpan. Aktifkan jenis notifikasi melalui /admin/whatsapp.');

        return self::SUCCESS;
    }
}
