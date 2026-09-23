<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        return view('admin.bookings.index');
    }

    public function show(string $booking): View
    {
        // Identifier hanya untuk navigasi pratinjau, belum mengambil record booking.
        return view('admin.bookings.show');
    }
}
