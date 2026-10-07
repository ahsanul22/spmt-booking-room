<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRoomRequest;
use App\Models\Facility;
use App\Models\Floor;
use App\Models\OrganizationalUnit;
use App\Models\Room;
use App\Models\User;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        return view('admin.rooms.index', [
            'rooms' => Room::with(['floor', 'facilities'])->orderBy('name')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.rooms.create', $this->formOptions());
    }

    public function store(SaveRoomRequest $request, RoomService $service): RedirectResponse
    {
        $room = $service->save($request->validated());

        return redirect()->route('admin.rooms.show', $room)->with('status', 'Ruangan berhasil ditambahkan.');
    }

    public function show(Room $room): View
    {
        return $this->edit($room);
    }

    public function edit(Room $room): View
    {
        return view('admin.rooms.edit', $this->formOptions() + [
            'room' => $room->load(['floor', 'facilities', 'pics', 'allowedOrganizationalUnits']),
            'eligiblePics' => User::where('role', User::ROLE_ROOM_PIC)->orderBy('name')->get(),
            'units' => OrganizationalUnit::orderBy('name')->get(),
            'selectedPicIds' => $room->pics->pluck('id')->all(),
            'selectedUnitIds' => $room->allowedOrganizationalUnits->pluck('id')->all(),
            'selectedFacilityIds' => $room->facilities->pluck('id')->all(),
        ]);
    }

    public function update(SaveRoomRequest $request, Room $room, RoomService $service): RedirectResponse
    {
        $room = $service->save($request->validated(), $room);

        return redirect()->route('admin.rooms.show', $room)->with('status', 'Ruangan berhasil diperbarui.');
    }

    public function status(Request $request, Room $room): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $room->update($data);

        return redirect()->route('admin.rooms.show', $room)
            ->with('status', $room->is_active ? 'Ruangan berhasil diaktifkan.' : 'Ruangan berhasil dinonaktifkan.');
    }

    public function destroy(Request $request, Room $room, RoomService $service): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['required', 'accepted']]);
        $service->delete($room);

        return redirect()->route('admin.rooms.index')->with('status', 'Ruangan berhasil dihapus.');
    }

    private function formOptions(): array
    {
        return [
            'floors' => Floor::orderBy('floor_number')->get(),
            'facilities' => Facility::orderBy('name')->get(),
        ];
    }
}
