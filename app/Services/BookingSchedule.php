<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class BookingSchedule
{
    public function validDate(mixed $date): bool
    {
        return is_string($date) && Validator::make(['date' => $date], [
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:1000-01-01', 'before_or_equal:9998-12-31'],
        ])->passes();
    }

    public function slots(string $from, string $to, ?int $roomId = null, bool $publicOnly = false): Collection
    {
        // Deliberately select occupancy fields only. Meeting names, people, units,
        // notes and submission tokens never enter the shared schedule response.
        $now = CarbonImmutable::now(config('booking.timezone'));
        return Booking::query()->select(['id', 'room_id', 'date', 'start_time', 'end_time', 'status'])
            ->with('room:id,name,is_active,status')->whereBetween('date', [$from, $to])
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->when($publicOnly, fn ($query) => $query->whereHas('room', fn ($rooms) => $rooms->where('is_active', true)->where('access_type', 'all')))
            ->when($roomId, fn ($query) => $query->where('room_id', $roomId))
            ->orderBy('date')->orderBy('start_time')->orderBy('room_id')->get()
            ->map(function ($booking) use ($now) {
                $start = CarbonImmutable::parse($booking->date.' '.$booking->start_time, config('booking.timezone'));
                $end = CarbonImmutable::parse($booking->date.' '.$booking->end_time, config('booking.timezone'));
                $state = match (true) {
                    $booking->status === 'completed', $end->lte($now) => 'elapsed',
                    $booking->status === 'pending' => 'pending',
                    $start->lte($now) => 'ongoing',
                    default => 'scheduled',
                };
                return [
                    'id' => $booking->id, 'room_id' => $booking->room_id,
                    'room_name' => $booking->room->name, 'room_operational' => $booking->room->is_active && $booking->room->status === 'available',
                    'date' => $booking->date, 'start' => substr($booking->start_time, 0, 5), 'end' => substr($booking->end_time, 0, 5),
                    'state' => $state, 'label' => match ($state) {
                        'ongoing' => 'Sedang berlangsung', 'pending' => 'Menunggu PIC', 'scheduled' => 'Terjadwal',
                        default => $booking->status === 'pending' ? 'Pending - waktu lewat' : 'Sudah lewat',
                    },
                ];
            });
    }
}
