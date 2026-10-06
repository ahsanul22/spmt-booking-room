<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Services\BookingPreparation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomDetailsController extends Controller
{
    public function __invoke(Request $request, Room $room, BookingPreparation $preparation): View
    {
        abort_unless($room->is_active || $request->user()->can('access-admin')
            || ($request->user()->role === User::ROLE_ROOM_PIC && $request->user()->managedRooms()->whereKey($room->id)->exists()), 404);
        $room->load(['floor', 'facilities', 'allowedOrganizationalUnits', 'pics']);
        $reason = $preparation->unavailableReason($room, $request->user());

        return view('user.rooms.show', compact('room', 'reason'));
    }
}
