<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchFilterRequest;
use App\Models\Booking;
use App\Models\Branch;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(BranchFilterRequest $request): View
    {
        $bookings = Booking::with(['room.branch', 'applicant', 'organizationalUnit'])->when($request->filled('branch_id'), fn ($query) => $query->whereHas('room', fn ($rooms) => $rooms->where('branch_id', $request->integer('branch_id'))))->latest('id')->paginate(20)->withQueryString();

        return view('admin.bookings.index', compact('bookings') + ['branches' => Branch::orderBy('name')->get()]);
    }

    public function show(Booking $booking): View
    {
        $booking->load(['room', 'applicant', 'organizationalUnit']);

        return view('admin.bookings.show', compact('booking'));
    }
}
