<?php

namespace App\Services;

use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappRecipient;
use App\Models\WhatsappTemplate;
use App\Support\WhatsappNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WhatsappSettings
{
    public function save(WhatsappTemplate $template, array $data): void
    {
        DB::transaction(function () use ($template, $data) {
            $template = WhatsappTemplate::lockForUpdate()->findOrFail($template->id);
            $users = User::whereKey($data['recipient_ids'] ?? [])->sharedLock()->get();
            foreach ($users as $user) {
                if (! $user->is_active || ! WhatsappNumber::valid($user->whatsapp_number)
                    || ($template->event === 'approval_request' && $user->role !== User::ROLE_SUPER_ADMIN)) {
                    throw ValidationException::withMessages(['recipient_ids' => 'Akun penerima berubah. Muat ulang dan pilih penerima aktif dengan nomor valid.']);
                }
            }
            if ($users->count() !== count($data['recipient_ids'] ?? [])) {
                throw ValidationException::withMessages(['recipient_ids' => 'Akun penerima tidak tersedia.']);
            }
            $template->update(array_intersect_key($data, array_flip(['body', 'is_active', 'include_room_pics'])));
            WhatsappRecipient::where('purpose', $template->event)->update(['is_active' => false]);
            WhatsappRecipient::where('purpose', $template->event)->whereNull('user_id')->delete();
            foreach ($data['recipient_ids'] ?? [] as $id) {
                WhatsappRecipient::updateOrCreate(['purpose' => $template->event, 'user_id' => $id], ['number' => null, 'is_active' => true]);
            }
        });
    }

    public function retry(int $id): void
    {
        DB::transaction(function () use ($id) {
            $message = WhatsappMessage::lockForUpdate()->findOrFail($id);
            if ($message->status !== 'failed') {
                throw ValidationException::withMessages(['retry' => 'Hanya pesan gagal yang dapat dicoba ulang.']);
            }
            if (! app(BookingWhatsapp::class)->eligible($message)
                || $message->booking->status !== ($message->event === 'approval_request' ? 'pending' : 'approved')) {
                throw ValidationException::withMessages(['retry' => 'Booking atau penerima sudah berubah. Pesan ini tidak dapat dikirim ulang.']);
            }
            $message->update(['status' => 'pending', 'error_code' => null]);
        });
    }
}
