<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestRegistrationRequest;
use App\Models\Booking;
use App\Models\GuestRegistration;
use App\Models\Room;
use App\Services\GuestRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GuestRegistrationController extends Controller
{
    public function create(): Response
    {
        return response()->view('guests.register')->header('Cache-Control', 'private, no-store');
    }

    public function store(StoreGuestRegistrationRequest $request, GuestRegistrationService $service): RedirectResponse
    {
        $guest = $service->register($request->validated());
        $request->session()->regenerate();
        $request->session()->put('guest_registration_id', $guest->id);

        return redirect()->route('guests.schedule');
    }

    public function schedule(Request $request): Response|RedirectResponse
    {
        $guest = GuestRegistration::whereKey($request->session()->get('guest_registration_id'))->where('expires_at', '>', now())->first();
        if (! $guest) {
            $request->session()->forget('guest_registration_id');

            return redirect()->route('guests.register')->with('status', 'Silakan isi identitas tamu untuk melihat jadwal.');
        }
        $data = $request->validate(['date' => ['bail', 'nullable', 'date_format:Y-m-d', 'after_or_equal:0001-01-01']]);
        $date = $data['date'] ?? now(config('booking.timezone'))->toDateString();
        $rooms = Room::where('branch_id', $guest->branch_id)->where('is_active', true)->where('access_type', 'all')->with('floor')->orderBy('name')->get();
        $slots = Booking::whereIn('room_id', $rooms->modelKeys())->where('date', $date)->whereIn('status', ['pending', 'approved'])->orderBy('start_time')->get(['room_id', 'start_time', 'end_time', 'status'])->groupBy('room_id');

        return response()->view('guests.schedule', compact('guest', 'date', 'rooms', 'slots'))->header('Cache-Control', 'private, no-store');
    }
}
