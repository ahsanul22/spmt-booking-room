<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserBookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::with(['room', 'applicant', 'organizationalUnit'])
            ->where('user_id', $request->user()->id)->latest('id')->paginate(15);

        return view('user.bookings.index', compact('bookings'));
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        $booking->load(['room', 'applicant', 'organizationalUnit']);

        return view('user.bookings.show', compact('booking'));
    }
}
