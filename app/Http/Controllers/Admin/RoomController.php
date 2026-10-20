<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRoomRequest;
use App\Models\Branch;
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
    public function index(Request $request): View
    {
        $request->validate(['branch_id' => ['nullable', 'integer', 'exists:branches,id']]);

        return view('admin.rooms.index', [
            'branches' => Branch::orderBy('name')->get(),
            'rooms' => Room::with(['branch', 'floor', 'facilities'])->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))->orderBy('name')->orderBy('id')->paginate(20)->withQueryString(),
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
            'eligiblePics' => User::where('role', User::ROLE_ROOM_PIC)->whereHas('branches', fn ($query) => $query->where('branches.id', $room->branch_id))->orderBy('name')->get(),
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
            'branches' => Branch::orderBy('name')->get(),
            'floors' => Floor::orderBy('floor_number')->get(),
            'facilities' => Facility::orderBy('name')->get(),
        ];
    }
}
