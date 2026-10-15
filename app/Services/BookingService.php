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
use Ramsey\Uuid\Uuid;

class BookingService
{
    public function __construct(private BookingPreparation $preparation) {}

    public function submit(User $actor, int $roomId, array $input, string $token): Booking
    {
        Validator::make(['token' => $token], ['token' => ['required', 'uuid']])->validate();
        // Only the expected form fields contribute to the idempotency fingerprint.
        $input = array_intersect_key($input, array_flip(['date', 'start_time', 'end_time', 'agenda', 'notes', 'meeting_preparation', 'participant_count', 'consumption_snack', 'consumption_meal', 'series_hash']));
        $input['agenda'] = is_string($input['agenda'] ?? null) ? trim($input['agenda']) : ($input['agenda'] ?? null);
        $input['notes'] = is_string($input['notes'] ?? null) ? trim($input['notes']) : ($input['notes'] ?? '');
        $input += ['meeting_preparation' => false, 'participant_count' => null, 'consumption_snack' => false, 'consumption_meal' => false];
        $fingerprint = $input;
        // Keep retries of submissions made before preparation fields were introduced valid.
        if ($input['meeting_preparation'] === false && $input['participant_count'] === null && $input['consumption_snack'] === false && $input['consumption_meal'] === false) {
            unset($fingerprint['meeting_preparation'], $fingerprint['participant_count'], $fingerprint['consumption_snack'], $fingerprint['consumption_meal']);
        }
        ksort($fingerprint);
        $hash = hash('sha256', json_encode([$roomId, $fingerprint], JSON_THROW_ON_ERROR));

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
            $data = $this->preparation->validate($input, $room);
            $this->assertRoomAllowed($room, $user);
            $requiresApproval = $room->requires_approval || $data['consumption_snack'] || $data['consumption_meal'];
            $approvalRoom = clone $room;
            $approvalRoom->requires_approval = $requiresApproval;
            $this->assertPicAvailable($approvalRoom);
            $this->assertNoConflict($roomId, $data);

            $booking = Booking::create([
                'user_id' => $user->id, 'room_id' => $room->id,
                'organizational_unit_id' => $user->organizational_unit_id,
                'unit_name' => $user->organizationalUnit?->name,
                'agenda' => $data['agenda'], 'date' => $data['date'],
                'start_time' => $data['start_time'], 'end_time' => $data['end_time'],
                'notes' => $data['notes'] ?: null,
                'meeting_preparation' => $data['meeting_preparation'],
                'participant_count' => $data['participant_count'],
                'consumption_snack' => $data['consumption_snack'],
                'consumption_meal' => $data['consumption_meal'],
                'status' => $requiresApproval ? 'pending' : 'approved',
                'requires_approval' => $requiresApproval,
                'submission_token' => $token, 'request_hash' => $hash,
            ]);
            app(BookingWhatsapp::class)->record($booking);

            return $booking;
        }, 3);
    }

    public function submitMany(User $actor, int $roomId, array $input, string $token, array $additionalDates): Booking
    {
        Validator::make(['dates' => $additionalDates, 'token' => $token], [
            'dates' => ['array', 'max:365'], 'dates.*' => ['required', 'date_format:Y-m-d', 'distinct'],
            'token' => ['required', 'uuid'],
        ])->validate();
        if ($additionalDates === []) {
            return $this->submit($actor, $roomId, $input, $token);
        }
        $dates = [$input['date'], ...$additionalDates];
        if (count(array_unique($dates)) !== count($dates)) {
            throw ValidationException::withMessages(['additionalDates' => 'Tanggal booking tidak boleh berulang.']);
        }
        // The first record fingerprints the entire series, including its order.
        $seriesHash = hash('sha256', json_encode($dates, JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($actor, $roomId, $input, $token, $dates, $seriesHash) {
            Room::lockForUpdate()->findOrFail($roomId);
            $first = null;
            foreach ($dates as $index => $date) {
                $childToken = $index === 0 ? $token : (string) Uuid::uuid5(Uuid::NAMESPACE_URL, $token.':'.$index);
                try {
                    $booking = $this->submit($actor, $roomId, array_replace($input, ['date' => $date, 'series_hash' => $seriesHash]), $childToken);
                } catch (ValidationException $exception) {
                    throw ValidationException::withMessages(collect($exception->errors())->map(fn ($messages) => array_map(fn ($message) => $date.': '.$message, $messages))->all());
                }
                $first ??= $booking;
            }

            return $first;
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

    public function decide(User $actor, Booking $record, string $decision, ?string $reason, array $consumption = []): Booking
    {
        $reason = $reason !== null ? trim($reason) : null;
        Validator::make(['decision' => $decision, 'rejection_reason' => $reason], [
            'decision' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
        ], ['rejection_reason.required_if' => 'Alasan penolakan wajib diisi.'])->validate();

        return DB::transaction(function () use ($actor, $record, $decision, $reason, $consumption) {
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
            $approved = [];
            foreach (['snack', 'meal'] as $type) {
                $field = 'approved_consumption_'.$type;
                $value = $consumption[$field] ?? false;
                Validator::make([$field => $value], [$field => ['required', 'boolean']])->validate();
                if ($decision === 'approved' && $value && ! $booking->{'consumption_'.$type}) {
                    throw ValidationException::withMessages([$field => 'Hanya konsumsi yang diminta pegawai dapat disetujui.']);
                }
                $approved[$field] = $decision === 'approved' && (bool) $value;
            }
            $booking->update($approved + [
                'decision_notes' => $reason !== '' ? $reason : null,
                'status' => $decision, 'rejection_reason' => $decision === 'rejected' ? $reason : null,
                'decided_by' => $reviewer->id, 'decided_at' => now(),
            ]);
            app(BookingWhatsapp::class)->record($booking);

            return $booking;
        }, 3);
    }
}
