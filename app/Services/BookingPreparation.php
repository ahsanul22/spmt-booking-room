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
        return CarbonImmutable::now(config('booking.timezone'))
            ->addHours(config('booking.minimum_notice_hours'))->ceilMinute();
    }

    public function validate(array $data): array
    {
        $data['agenda'] = is_string($data['agenda'] ?? null) ? trim($data['agenda']) : ($data['agenda'] ?? null);
        $validator = Validator::make($data, [
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'agenda' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'required' => ':attribute wajib diisi.',
            'date_format' => 'Format :attribute tidak valid.',
            'max' => ':attribute maksimal :max karakter.',
        ], [
            'date' => 'Tanggal', 'start_time' => 'Jam mulai', 'end_time' => 'Jam selesai',
            'agenda' => 'Agenda', 'notes' => 'Catatan',
        ]);
        $validator->after(function ($validator) use ($data) {
            if ($validator->errors()->hasAny(['date', 'start_time', 'end_time'])) {
                return;
            }
            $start = CarbonImmutable::createFromFormat('!Y-m-d H:i', $data['date'].' '.$data['start_time'], config('booking.timezone'));
            $end = CarbonImmutable::createFromFormat('!Y-m-d H:i', $data['date'].' '.$data['end_time'], config('booking.timezone'));
            if ($start->lt($this->earliestStart())) {
                $validator->errors()->add('start_time', 'Rapat harus dimulai minimal '.config('booking.minimum_notice_hours').' jam dari waktu pemeriksaan. Pilih waktu yang lebih lambat.');
            }
            if ($end->lte($start)) {
                $validator->errors()->add('end_time', 'Jam selesai harus setelah jam mulai pada tanggal yang sama. Rapat lintas hari belum didukung.');
            }
        });

        return $validator->validate();
    }
}
