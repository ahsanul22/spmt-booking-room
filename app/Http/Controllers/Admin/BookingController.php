<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::with(['room', 'applicant', 'organizationalUnit'])->latest('id')->paginate(20);
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['room', 'applicant', 'organizationalUnit']);
        return view('admin.bookings.show', compact('booking'));
    }
}
