<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRoomPicsRequest;
use App\Models\Room;
use App\Models\User;
use App\Services\RoomAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomPicController extends Controller
{
    public function edit(Room $room): View
    {
        $room->load('pics');

        return view('admin.rooms.pics', [
            'room' => $room,
            'eligiblePics' => User::where('role', User::ROLE_ROOM_PIC)->orderBy('name')->get(),
            'selectedPicIds' => $room->pics->pluck('id')->all(),
        ]);
    }

    public function update(SaveRoomPicsRequest $request, Room $room, RoomAssignmentService $service): RedirectResponse
    {
        $service->syncPics($room, $request->validated('pic_ids'));

        return redirect()->route('admin.rooms.show', $room)->with('status', 'PIC ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room, User $pic, RoomAssignmentService $service): RedirectResponse
    {
        $service->removePic($room, $pic);

        return redirect()->route('admin.rooms.show', $room)->with('status', 'PIC berhasil dilepas dari ruangan.');
    }
}
