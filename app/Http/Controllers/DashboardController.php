<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        if ($request->user()->can('access-admin')) {
            return app(Admin\DashboardController::class)->index($request);
        }
        $user = $request->user()->load('organizationalUnit');
        $stats = [];

        if ($user->can('access-pic')) {
            $today = CarbonImmutable::now(config('booking.timezone'))->toDateString();
            $isAdmin = $user->can('access-admin');

            $stats = [
                [
                    'label' => 'Menunggu approval',
                    'icon' => 'clock',
                    'value' => $user->pendingApprovalsCount(),
                    'description' => 'Pengajuan perlu ditinjau',
                ],
                [
                    'label' => 'Ruangan tanggung jawab',
                    'icon' => 'room',
                    'value' => $user->managedRooms()->visibleTo($user)->count(),
                    'description' => 'Ruangan ditugaskan',
                ],
                [
                    'label' => 'Jadwal hari ini',
                    'icon' => 'calendar',
                    'value' => Booking::query()->whereIn('room_id', Room::query()->visibleTo($user)->select('rooms.id'))
                        ->where('date', $today)
                        ->whereIn('status', ['pending', 'approved', 'completed'])
                        ->when(! $isAdmin, fn ($query) => $query
                            ->whereHas('room.pics', fn ($pics) => $pics->where('users.id', $user->id)))
                        ->count(),
                    'description' => 'Penggunaan hari ini',
                ],
            ];
        }

        return view('dashboard', [
            'user' => $user,
            'title' => $user->can('access-pic') ? 'Dashboard PIC' : 'Dashboard Pegawai',
            'stats' => $stats,
        ]);
    }
}
