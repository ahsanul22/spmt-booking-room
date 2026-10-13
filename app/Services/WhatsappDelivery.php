<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class WhatsappDelivery
{
    public function send(int $id): void
    {
        if (! config('whatsapp.enabled') || ! config('whatsapp.token')) {
            return;
        }
        $message = DB::transaction(function () use ($id) {
            $record = WhatsappMessage::lockForUpdate()->findOrFail($id);
            if ($record->status !== 'pending') {
                return null;
            }
            if (! app(BookingWhatsapp::class)->eligible($record)) {
                $record->update(['status' => 'skipped', 'error_code' => 'recipient_changed']);

                return null;
            }
            $booking = Booking::findOrFail($record->booking_id);
            $expectedStatus = $record->event === 'approval_request' ? 'pending' : 'approved';
            if ($booking->status !== $expectedStatus) {
                $record->update(['status' => 'skipped', 'error_code' => 'booking_status_changed']);

                return null;
            }
            $record->update(['status' => 'processing', 'attempts' => $record->attempts + 1, 'error_code' => null]);

            return $record;
        });
        if (! $message) {
            return;
        }
        try {
            $response = Http::asForm()->withHeaders(['Authorization' => config('whatsapp.token')])
                ->connectTimeout(5)->timeout(20)->withOptions(['allow_redirects' => false])
                ->post('https://api.fonnte.com/send', [
                    'target' => ltrim($message->number, '+'), 'countryCode' => '0',
                    'message' => $message->message, 'connectOnly' => true,
                ]);
            if ($response->successful() && $response->json('status') === true) {
                $message->update(['status' => 'accepted', 'accepted_at' => now()]);
            } elseif ($response->successful() && $response->json('status') === false) {
                $message->update(['status' => 'failed', 'error_code' => 'provider_rejected']);
            } else {
                $message->update(['status' => 'unknown', 'error_code' => 'unexpected_response']);
            }
        } catch (Throwable) {
            // A timeout may follow acceptance. Never automatically resend an uncertain result.
            $message->update(['status' => 'unknown', 'error_code' => 'delivery_uncertain']);
        }
    }
}
