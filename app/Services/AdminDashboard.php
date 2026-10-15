<?php

namespace App\Services;

use App\Models\OrganizationalUnit;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AdminDashboard
{
    public function data(): array
    {
        $activeRooms = Room::where('is_active', true);
        $activePic = fn (Builder $query) => $query->where('role', User::ROLE_ROOM_PIC)->where('is_active', true);
        $missingPic = (clone $activeRooms)->whereDoesntHave('pics', $activePic)->count();
        $missingFacilities = (clone $activeRooms)->whereDoesntHave('facilities')->count();
        $conditions = (clone $activeRooms)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $activeCount = (clone $activeRooms)->count();

        return [
            'stats' => [
                ['label' => 'Ruangan terdaftar', 'icon' => 'room', 'value' => Room::count(), 'description' => $activeCount.' ruangan aktif'],
                ['label' => 'Akun aktif', 'icon' => 'grid', 'value' => User::where('is_active', true)->count(), 'description' => 'Pegawai, PIC, dan Admin'],
                ['label' => 'Unit kerja aktif', 'icon' => 'grid', 'value' => OrganizationalUnit::where('is_active', true)->count(), 'description' => 'Master unit organisasi'],
            ],
            'roomConditions' => collect(['available' => 'Operasional', 'maintenance' => 'Dalam perawatan', 'unavailable' => 'Tidak dapat digunakan'])->map(fn ($label, $status) => [
                'label' => $label, 'count' => (int) ($conditions[$status] ?? 0),
                'percentage' => $activeCount ? (int) round(($conditions[$status] ?? 0) / $activeCount * 100) : 0,
            ])->values(),
            'activeRoomCount' => $activeCount,
            'missingPicCount' => $missingPic,
            'missingFacilitiesCount' => $missingFacilities,
            'attentionRooms' => (clone $activeRooms)->where(fn (Builder $query) => $query
                ->whereDoesntHave('pics', $activePic)->orWhereDoesntHave('facilities'))
                ->withCount(['pics as active_pics_count' => $activePic, 'facilities'])
                ->orderBy('name')->orderBy('id')->limit(5)->get(),
        ];
    }
}
