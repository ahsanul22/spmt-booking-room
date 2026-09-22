<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Support\Facades\DB;

class RoomService
{
    public function save(array $data, ?Room $room = null): Room
    {
        return DB::transaction(function () use ($data, $room) {
            $record = $room ? Room::lockForUpdate()->findOrFail($room->id) : new Room;
            $facilityIds = $data['facility_ids'];
            unset($data['facility_ids']);
            $record->fill($data);
            $record->save();
            $record->facilities()->sync($facilityIds);

            return $record;
        });
    }
}
