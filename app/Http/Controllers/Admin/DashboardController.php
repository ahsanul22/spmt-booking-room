<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $now = now(config('booking.timezone'));
        return view('admin.dashboard', [
            'user' => $request->user()->load('organizationalUnit'),
            'title' => 'Dashboard Admin',
            'stats' => [
                ['label' => 'Booking hari ini', 'icon' => 'calendar', 'value' => Booking::where('date', $now->toDateString())->whereIn('status', ['pending', 'approved', 'completed'])->count()],
                ['label' => 'Menunggu approval', 'icon' => 'clock', 'value' => Booking::where('status', 'pending')->count()],
                ['label' => 'Rapat berlangsung', 'icon' => 'room', 'value' => Booking::where('date', $now->toDateString())->where('status', 'approved')->where('start_time', '<=', $now->format('H:i:s'))->where('end_time', '>', $now->format('H:i:s'))->distinct()->count('room_id')],
            ],
            'recentBookings' => Booking::with(['room', 'applicant'])->latest('id')->limit(5)->get(),
        ]);
    }
}
