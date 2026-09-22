<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomAssignmentService
{
    public function syncPics(Room $room, array $ids): void
    {
        DB::transaction(function () use ($room, $ids) {
            $record = Room::lockForUpdate()->findOrFail($room->id);
            // Recheck roles while holding user locks, in case they changed after validation.
            $users = User::whereIn('id', $ids)->orderBy('id')->sharedLock()->get();
            if ($users->count() !== count($ids) || $users->contains(fn ($user) => $user->role !== User::ROLE_ROOM_PIC)) {
                throw ValidationException::withMessages(['pic_ids' => 'Semua PIC harus merupakan user dengan role room_pic.']);
            }
            $record->pics()->sync($ids);
        });
    }

    public function removePic(Room $room, User $pic): void
    {
        DB::transaction(function () use ($room, $pic) {
            $record = Room::lockForUpdate()->findOrFail($room->id);
            abort_unless($record->pics()->whereKey($pic->id)->exists(), 404);
            $record->pics()->detach($pic->id);
        });
    }

    public function syncUnits(Room $room, array $ids): void
    {
        DB::transaction(function () use ($room, $ids) {
            $record = Room::lockForUpdate()->findOrFail($room->id);
            if ($record->access_type !== 'restricted') {
                throw ValidationException::withMessages(['organizational_unit_ids' => 'Unit akses hanya dapat diatur untuk ruangan restricted.']);
            }
            $record->allowedOrganizationalUnits()->sync($ids);
        });
    }
}
