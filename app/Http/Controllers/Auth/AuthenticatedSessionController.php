<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        if ($request->has('room')) {
            $id = $request->query('room');
            $room = is_string($id) && filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false
                ? Room::where('is_active', true)->where('access_type', 'all')->where('status', 'available')->find($id) : null;
            if ($room) {
                $request->session()->put('booking.login_room', $room->id);
            } else {
                $request->session()->forget('booking.login_room');
            }
        }
        $selectedRoom = Room::where('is_active', true)->where('access_type', 'all')
            ->find($request->session()->get('booking.login_room'));
        return view('auth.login', compact('selectedRoom'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $roomId = $request->session()->pull('booking.login_room');
        if ($roomId && $request->user()->can('access-employee')) {
            $room = Room::where('is_active', true)->where('status', 'available')->find($roomId);
            return $room ? redirect()->route('my-bookings.create', ['room_id' => $room->id])
                : redirect()->route('rooms.index');
        }

        return redirect()->route($request->user()->dashboardRouteName());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
