<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRoomRequest;
use App\Models\Facility;
use App\Models\Floor;
use App\Models\Room;
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
        return view('admin.rooms.show', ['room' => $room->load(['floor', 'facilities', 'pics', 'allowedOrganizationalUnits'])]);
    }

    public function edit(Room $room): View
    {
        return view('admin.rooms.edit', $this->formOptions() + [
            'room' => $room,
            'selectedFacilityIds' => $room->facilities()->pluck('facilities.id')->all(),
        ]);
    }

    public function update(SaveRoomRequest $request, Room $room, RoomService $service): RedirectResponse
    {
        $service->save($request->validated(), $room);

        return redirect()->route('admin.rooms.show', $room)->with('status', 'Ruangan berhasil diperbarui.');
    }

    public function status(Request $request, Room $room): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $room->update($data);

        return redirect()->route('admin.rooms.show', $room)
            ->with('status', $room->is_active ? 'Ruangan berhasil diaktifkan.' : 'Ruangan berhasil dinonaktifkan.');
    }

    private function formOptions(): array
    {
        return [
            'floors' => Floor::orderBy('floor_number')->get(),
            'facilities' => Facility::orderBy('name')->get(),
        ];
    }
}
