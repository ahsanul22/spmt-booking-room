<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use App\Support\WhatsappNumber;
use Illuminate\Support\Collection;

class BookingWhatsapp
{
    public const VARIABLES = ['booking_id', 'room', 'applicant', 'unit', 'agenda', 'date', 'start_time', 'end_time', 'participant_count', 'review_url'];

    public const FIELDS = [
        ['label' => 'Nomor booking', 'tokens' => ['{booking_id}'], 'line' => 'Booking #{booking_id}'],
        ['label' => 'Ruangan', 'tokens' => ['{room}'], 'line' => 'Ruangan: {room}'],
        ['label' => 'Pemohon', 'tokens' => ['{applicant}'], 'line' => 'Pemohon: {applicant}'],
        ['label' => 'Unit kerja', 'tokens' => ['{unit}'], 'line' => 'Unit kerja: {unit}'],
        ['label' => 'Agenda', 'tokens' => ['{agenda}'], 'line' => 'Agenda: {agenda}'],
        ['label' => 'Tanggal', 'tokens' => ['{date}'], 'line' => 'Tanggal: {date}'],
        ['label' => 'Jam rapat', 'tokens' => ['{start_time}', '{end_time}'], 'line' => 'Jam: {start_time}–{end_time} WIB'],
        ['label' => 'Jumlah peserta', 'tokens' => ['{participant_count}'], 'line' => 'Perkiraan peserta: {participant_count}'],
        ['label' => 'Tautan review', 'tokens' => ['{review_url}'], 'line' => 'Review: {review_url}'],
    ];

    public function recipients(Booking $booking, string $event, WhatsappTemplate $template): Collection
    {
        $selected = User::where('is_active', true)->whereHas('notificationRecipients', fn ($query) => $query
            ->where('purpose', $event)->where('is_active', true))
            ->when($event === 'approval_request', fn ($query) => $query->where('role', User::ROLE_SUPER_ADMIN))->get();
        if ($event === 'approval_request' && $template->include_room_pics) {
            $selected = $selected->merge($booking->room->pics()->where('role', User::ROLE_ROOM_PIC)->where('is_active', true)->get());
        }

        return $selected->unique('id')->filter(fn ($user) => $user->canAccessBranch($booking->room->branch_id) && WhatsappNumber::valid($user->whatsapp_number));
    }

    public function render(Booking $booking, string $body, ?User $recipient = null): string
    {
        $booking->loadMissing('room', 'applicant');
        $values = [
            'booking_id' => (string) $booking->id, 'room' => $booking->room->name,
            'applicant' => $booking->applicant->name, 'unit' => $booking->unit_name ?? 'Tanpa unit kerja',
            'agenda' => $booking->agenda, 'date' => $booking->date,
            'start_time' => substr($booking->start_time, 0, 5), 'end_time' => substr($booking->end_time, 0, 5),
            'participant_count' => (string) ($booking->participant_count ?? '-'),
            'review_url' => route($recipient?->role === User::ROLE_SUPER_ADMIN ? 'admin.approvals.show' : 'pic.approvals.show', $booking->id),
        ];

        return strtr($body, collect($values)->mapWithKeys(fn ($value, $key) => ['{'.$key.'}' => $value])->all());
    }

    // Invoked inside the booking transaction; failed bookings leave no messages.
    public function record(Booking $booking): void
    {
        $event = match (true) {
            $booking->status === 'pending' => 'approval_request',
            $booking->status === 'approved' && $booking->meeting_preparation && ($booking->approved_consumption_snack || $booking->approved_consumption_meal) => 'consumption',
            default => null,
        };
        if ($event === null) {
            return;
        }
        $template = WhatsappTemplate::where('event', $event)->where('is_active', true)->first();
        if (! $template) {
            return;
        }
        $booking->loadMissing('room');
        foreach ($this->recipients($booking, $event, $template)->unique('whatsapp_number') as $recipient) {
            WhatsappMessage::firstOrCreate([
                'booking_id' => $booking->id, 'event' => $event, 'number' => $recipient->whatsapp_number,
            ], ['recipient_user_id' => $recipient->id, 'message' => $this->render($booking, $template->body, $recipient).($event === 'consumption' ? "\nKonsumsi disetujui: ".collect([$booking->approved_consumption_snack ? 'Snack' : null, $booking->approved_consumption_meal ? 'Makanan' : null])->filter()->implode(', ') : '')]);
        }
    }

    public function eligible(WhatsappMessage $message): bool
    {
        $booking = $message->booking;
        $template = WhatsappTemplate::where('event', $message->event)->where('is_active', true)->first();
        if (! $booking || ! $template || ! $message->recipient_user_id) {
            return false;
        }

        return $this->recipients($booking, $message->event, $template)->contains(fn ($user) => $user->id === $message->recipient_user_id && $user->whatsapp_number === $message->number);
    }
}
