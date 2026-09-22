<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRoomAccessRequest;
use App\Models\OrganizationalUnit;
use App\Models\Room;
use App\Services\RoomAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomAccessController extends Controller
{
    public function edit(Room $room): View
    {
        $room->load('allowedOrganizationalUnits');

        return view('admin.rooms.access', [
            'room' => $room,
            'units' => OrganizationalUnit::orderBy('name')->get(),
            'selectedUnitIds' => $room->allowedOrganizationalUnits->pluck('id')->all(),
        ]);
    }

    public function update(SaveRoomAccessRequest $request, Room $room, RoomAssignmentService $service): RedirectResponse
    {
        $service->syncUnits($room, $request->validated('organizational_unit_ids'));

        return redirect()->route('admin.rooms.show', $room)->with('status', 'Unit akses ruangan berhasil diperbarui.');
    }
}
