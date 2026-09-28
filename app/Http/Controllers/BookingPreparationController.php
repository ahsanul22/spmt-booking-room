<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingPreparationController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if (! $request->filled('room_id')) {
            return redirect()->route('rooms.index');
        }
        $id = $request->query('room_id');
        abort_unless(is_string($id) && filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false, 404);
        $room = Room::findOrFail($id);

        return view('user.bookings.create', compact('room'));
    }
}
