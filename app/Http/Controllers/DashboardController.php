<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('organizationalUnit');
        $stats = [];

        if ($user->can('access-pic')) {
            $today = \Carbon\CarbonImmutable::now(config('booking.timezone'))->toDateString();
            $isAdmin = $user->can('access-admin');

            $stats = [
                [
                    'label' => 'Menunggu approval',
                    'icon' => 'clock',
                    'value' => \App\Models\Booking::query()
                        ->where('requires_approval', true)
                        ->where('status', 'pending')
                        ->when(! $isAdmin, fn ($query) => $query
                            ->whereHas('room.pics', fn ($pics) => $pics->where('users.id', $user->id)))
                        ->count(),
                    'description' => 'Pengajuan perlu ditinjau',
                ],
                [
                    'label' => 'Ruangan tanggung jawab',
                    'icon' => 'room',
                    'value' => $user->managedRooms()->count(),
                    'description' => 'Ruangan ditugaskan',
                ],
                [
                    'label' => 'Jadwal hari ini',
                    'icon' => 'calendar',
                    'value' => \App\Models\Booking::query()
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
            'title' => $request->route('title'),
            'stats' => $stats,
        ]);
    }
}
