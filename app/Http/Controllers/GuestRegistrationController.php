<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\GuestRegistration;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GuestRegistrationController extends Controller
{
    public function create()
    {
        return response()->view('guests.register')->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:50'],
            'nip' => ['nullable', 'string', 'max:50'],
            'purpose' => ['required', 'string', 'max:2000'],
        ]);
        // Resolve the reception office on the server, never from a hidden input.
        $branch = Branch::query()->when(config('guests.branch_id') !== null,
            fn ($query) => $query->whereKey(config('guests.branch_id')))->orderBy('id')->first();
        if (! $branch) {
            throw ValidationException::withMessages(['registration' => 'Pendataan tamu belum tersedia. Silakan hubungi resepsionis.']);
        }
        $data['branch_id'] = $branch->id;
        $now = CarbonImmutable::now(config('booking.timezone'));
        $expires = $now->addDays(max(1, config('guests.active_days')));
        if (config('guests.expiry_mode') === 'end_of_day') {
            $expires = $now->setTime(17, 0);
            if ($expires->lte($now)) {
                $expires = $expires->addDay();
            }
        }
        // Eloquent serializes timestamps in the application timezone (UTC).
        $guest = GuestRegistration::create($data + ['reference' => Str::upper(Str::random(12)), 'expires_at' => $expires->setTimezone(config('app.timezone'))]);
        $request->session()->regenerate();
        $request->session()->put('guest_registration_id', $guest->id);

        return redirect()->route('guests.schedule');
    }

    public function schedule(Request $request)
    {
        $guest = GuestRegistration::whereKey($request->session()->get('guest_registration_id'))->where('expires_at', '>', now())->first();
        if (! $guest) {
            $request->session()->forget('guest_registration_id');

            return redirect()->route('guests.register')->with('status', 'Silakan isi identitas tamu untuk melihat jadwal.');
        }
        $data = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);
        $date = $data['date'] ?? now(config('booking.timezone'))->toDateString();
        $rooms = Room::where('branch_id', $guest->branch_id)->where('is_active', true)->where('access_type', 'all')->with('floor')->orderBy('name')->get();
        $slots = Booking::whereIn('room_id', $rooms->modelKeys())->where('date', $date)->whereIn('status', ['pending', 'approved'])->orderBy('start_time')->get(['room_id', 'start_time', 'end_time', 'status'])->groupBy('room_id');

        return response()->view('guests.schedule', compact('guest', 'date', 'rooms', 'slots'))->header('Cache-Control', 'private, no-store');
    }
}
