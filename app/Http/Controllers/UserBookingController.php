<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserBookingController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(! $request->user()->can('access-admin') && $request->filled('branch_id'), 403);
        $request->validate(['branch_id' => ['nullable', 'integer', 'exists:branches,id']]);
        $bookings = Booking::with(['room.branch', 'applicant', 'organizationalUnit'])
            ->whereIn('room_id', Room::query()->visibleTo($request->user())->select('rooms.id'))
            ->where('user_id', $request->user()->id)->when($request->filled('branch_id'), fn ($query) => $query->whereHas('room', fn ($rooms) => $rooms->where('branch_id', $request->integer('branch_id'))))->latest('id')->paginate(15)->withQueryString();

        return view('user.bookings.index', compact('bookings') + ['branches' => ($request->user()->can('access-admin') ? Branch::orderBy('name')->get() : collect())]);
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($request->user()->canAccessBranch($booking->room->branch_id), 404);
        $booking->load(['room', 'applicant', 'organizationalUnit']);

        return view('user.bookings.show', compact('booking'));
    }
}
