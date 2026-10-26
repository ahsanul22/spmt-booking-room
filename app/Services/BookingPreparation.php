<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;

class BookingPreparation
{
    public function unavailableReason(Room $room, User $user): ?string
    {
        if (! $user->canAccessBranch($room->branch_id)) {
            return 'Anda tidak memiliki izin akses ke lokasi ruangan ini.';
        }
        if (! $room->is_active) {
            return 'Ruangan sudah tidak aktif. Silakan pilih ruangan lain.';
        }
        if ($room->status !== 'available') {
            return 'Ruangan sedang tidak operasional dan belum dapat dipilih.';
        }
        if ($room->access_type === 'restricted' && (! $user->organizational_unit_id
            || ! $room->allowedOrganizationalUnits->contains('id', $user->organizational_unit_id))) {
            return 'Ruangan ini dibatasi untuk unit tertentu. Unit Anda belum memiliki akses.';
        }

        return null;
    }

    public function earliestStart(): CarbonImmutable
    {
        return CarbonImmutable::now(config('booking.timezone'))->ceilMinute();
    }

    public function validate(array $data, ?Room $room = null): array
    {
        $data += ['it_support' => false, 'meeting_preparation' => false, 'participant_count' => null, 'consumption_snack' => false, 'consumption_meal' => false];
        $enabled = in_array($data['meeting_preparation'], [true, 1, '1'], true);
        if (! $enabled) {
            $data['participant_count'] = null;
            $data['consumption_snack'] = false;
            $data['consumption_meal'] = false;
        }
        $data['agenda'] = is_string($data['agenda'] ?? null) ? trim($data['agenda']) : ($data['agenda'] ?? null);
        $validator = Validator::make($data, [
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'agenda' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'meeting_preparation' => ['required', 'boolean', function ($attribute, $value, $fail) use ($room, $enabled) {
                if ($enabled && ! $room?->allows_meeting_preparation) {
                    $fail('Persiapan rapat belum diaktifkan untuk ruangan ini.');
                }
            }],
            'participant_count' => [$enabled ? 'required' : 'nullable', 'integer', 'between:1,2147483647'],
            'consumption_snack' => ['required', 'boolean'],
            'consumption_meal' => ['required', 'boolean'],
            'it_support' => ['required', 'boolean', function ($attribute, $value, $fail) use ($room) {
                if ($value && ! $room?->allows_it_support) {
                    $fail('Permintaan teknisi IT belum diaktifkan untuk ruangan ini.');
                }
            }],
        ], [
            'required' => ':attribute wajib diisi.',
            'date_format' => 'Format :attribute tidak valid.',
            'max' => ':attribute maksimal :max karakter.',
        ], [
            'date' => 'Tanggal', 'start_time' => 'Jam mulai', 'end_time' => 'Jam selesai',
            'agenda' => 'Agenda', 'notes' => 'Catatan',
            'participant_count' => 'Jumlah peserta', 'meeting_preparation' => 'Persiapan rapat', 'consumption_snack' => 'Konsumsi snack', 'consumption_meal' => 'Konsumsi makan',
        ]);
        $validator->after(function ($validator) use ($data) {
            if ($validator->errors()->hasAny(['date', 'start_time', 'end_time'])) {
                return;
            }
            $start = CarbonImmutable::createFromFormat('!Y-m-d H:i', $data['date'].' '.$data['start_time'], config('booking.timezone'));
            $end = CarbonImmutable::createFromFormat('!Y-m-d H:i', $data['date'].' '.$data['end_time'], config('booking.timezone'));
            if ($start->lt($this->earliestStart())) {
                $validator->errors()->add('start_time', 'Jam mulai sudah lewat. Pilih waktu sekarang atau setelahnya.');
            }
            if ($end->lte($start)) {
                $validator->errors()->add('end_time', 'Jam selesai harus setelah jam mulai pada tanggal yang sama. Rapat lintas hari belum didukung.');
            }
        });

        return $validator->validate();
    }
}
