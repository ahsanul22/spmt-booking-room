<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(private BookingPreparation $preparation) {}

    public function submit(User $actor, int $roomId, array $input, string $token): Booking
    {
        Validator::make(['token' => $token], ['token' => ['required', 'uuid']])->validate();
        // Only the expected form fields contribute to the idempotency fingerprint.
        $input = array_intersect_key($input, array_flip(['date', 'start_time', 'end_time', 'agenda', 'notes']));
        $input['agenda'] = is_string($input['agenda'] ?? null) ? trim($input['agenda']) : ($input['agenda'] ?? null);
        $input['notes'] = is_string($input['notes'] ?? null) ? trim($input['notes']) : ($input['notes'] ?? '');
        ksort($input);
        $hash = hash('sha256', json_encode([$roomId, $input], JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($actor, $roomId, $input, $token, $hash) {
            // All booking writes/decisions lock the room first. Overlap check and insert
            // therefore serialize, even when the room initially has no bookings.
            $room = Room::lockForUpdate()->findOrFail($roomId);
            $user = User::lockForUpdate()->findOrFail($actor->id);
            Gate::forUser($user)->authorize('access-employee');
            $existing = Booking::where('user_id', $user->id)->where('submission_token', $token)->first();
            if ($existing) {
                if (! hash_equals($existing->request_hash, $hash)) {
                    throw ValidationException::withMessages(['agenda' => 'Form ini sudah dikirim dengan isian berbeda. Buka form booking baru.']);
                }
                return $existing;
            }
            $data = $this->preparation->validate($input);
            $this->assertRoomAllowed($room, $user);
            $this->assertPicAvailable($room);
            $this->assertNoConflict($roomId, $data);

            return Booking::create([
                'user_id' => $user->id, 'room_id' => $room->id,
                'organizational_unit_id' => $user->organizational_unit_id,
                'unit_name' => $user->organizationalUnit?->name,
                'agenda' => $data['agenda'], 'date' => $data['date'],
                'start_time' => $data['start_time'], 'end_time' => $data['end_time'],
                'notes' => $data['notes'] ?: null,
                'status' => $room->requires_approval ? 'pending' : 'approved',
                'requires_approval' => $room->requires_approval,
                'submission_token' => $token, 'request_hash' => $hash,
            ]);
        }, 3);
    }

    public function assertPicAvailable(Room $room): void
    {
        if ($room->requires_approval && ! $room->pics()->where('role', User::ROLE_ROOM_PIC)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['room' => 'PIC aktif belum ditetapkan. Hubungi administrator sebelum mengajukan ruangan ini.']);
        }
    }

    public function assertRoomAllowed(Room $room, User $user): void
    {
        $reason = $this->preparation->unavailableReason($room, $user);
        if ($reason) {
            throw ValidationException::withMessages(['room' => $reason]);
        }
    }

    public function assertNoConflict(int $roomId, array $data, ?int $except = null): void
    {
        $conflict = Booking::where('room_id', $roomId)->where('date', $data['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_time', '<', $data['end_time'])->where('end_time', '>', $data['start_time'])
            ->when($except, fn ($query) => $query->where('id', '!=', $except))->exists();
        if ($conflict) {
            throw ValidationException::withMessages(['start_time' => 'Ruangan sudah dipesan atau menunggu approval pada waktu tersebut. Pilih waktu lain.']);
        }
    }

    public function decide(User $actor, Booking $record, string $decision, ?string $reason): Booking
    {
        $reason = $reason !== null ? trim($reason) : null;
        Validator::make(['decision' => $decision, 'rejection_reason' => $reason], [
            'decision' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
        ], ['rejection_reason.required_if' => 'Alasan penolakan wajib diisi.'])->validate();

        return DB::transaction(function () use ($actor, $record, $decision, $reason) {
            $room = Room::lockForUpdate()->findOrFail($record->room_id);
            $booking = Booking::lockForUpdate()->findOrFail($record->id);
            $reviewer = User::sharedLock()->findOrFail($actor->id);
            abort_unless($reviewer->is_active && ($reviewer->role === User::ROLE_SUPER_ADMIN
                || ($reviewer->role === User::ROLE_ROOM_PIC && $room->pics()->whereKey($reviewer->id)->exists())), 403);
            abort_unless($booking->requires_approval, 403);
            if ($booking->status !== 'pending') {
                throw ValidationException::withMessages(['decision' => 'Pengajuan sudah diproses. Muat ulang halaman untuk melihat status terbaru.']);
            }
            if ($decision === 'approved') {
                $applicant = User::sharedLock()->findOrFail($booking->user_id);
                Gate::forUser($applicant)->authorize('access-employee');
                $this->assertRoomAllowed($room, $applicant);
                $start = CarbonImmutable::parse($booking->date.' '.$booking->start_time, config('booking.timezone'));
                if ($start->lte(CarbonImmutable::now(config('booking.timezone')))) {
                    throw ValidationException::withMessages(['decision' => 'Waktu rapat sudah dimulai atau lewat. Pengajuan tidak dapat disetujui.']);
                }
                $this->assertNoConflict($room->id, $booking->toArray(), $booking->id);
            }
            $booking->update([
                'decision_notes' => $reason !== '' ? $reason : null,
                'status' => $decision, 'rejection_reason' => $decision === 'rejected' ? $reason : null,
                'decided_by' => $reviewer->id, 'decided_at' => now(),
            ]);

            return $booking;
        }, 3);
    }
}
