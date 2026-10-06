<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MonthlyBookingReport
{
    public const STATUSES = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled', 'completed' => 'Completed'];

    public function rooms(User $user): Builder
    {
        return Room::query()->when(! $user->can('access-admin'), fn ($query) => $query
            ->whereHas('pics', fn ($pics) => $pics->where('users.id', $user->id)));
    }

    public function bookings(User $user, CarbonImmutable $month, ?int $roomId): Builder
    {
        return Booking::query()->whereIn('room_id', $this->rooms($user)->select('rooms.id'))
            ->whereBetween('date', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()])
            ->when($roomId, fn ($query) => $query->where('room_id', $roomId));
    }

    public function summary(Builder $query, Collection $rooms): array
    {
        $counts = (clone $query)->select('room_id', 'status')->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(EXTRACT(EPOCH FROM (end_time - start_time)) / 3600) AS hours')
            ->groupBy('room_id', 'status')->get();
        $byRoom = $rooms->map(function ($room) use ($counts) {
            $rows = $counts->where('room_id', $room->id);
            return [
                'room' => $room,
                'counts' => collect(self::STATUSES)->map(fn ($label, $status) => (int) $rows->where('status', $status)->sum('total'))->all(),
                'total' => (int) $rows->sum('total'),
                'hours' => (float) $rows->whereIn('status', ['approved', 'completed'])->sum('hours'),
            ];
        });

        return [
            'byRoom' => $byRoom,
            'counts' => collect(self::STATUSES)->map(fn ($label, $status) => (int) $counts->where('status', $status)->sum('total'))->all(),
            'total' => (int) $counts->sum('total'),
            'hours' => (float) $counts->whereIn('status', ['approved', 'completed'])->sum('hours'),
        ];
    }

    public static function csvCell(mixed $value): string
    {
        $value = (string) $value;
        // Prevent spreadsheet formulas from user-supplied names, units, or agendas.
        return preg_match('/^[\s\x{FEFF}]*[=+@-]/u', $value) || preg_match('/^[\t\r\n]/', $value) ? "'".$value : $value;
    }
}
