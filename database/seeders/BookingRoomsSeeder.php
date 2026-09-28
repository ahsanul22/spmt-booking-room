<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingRoomsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Preserve the existing Selat Malaka PIC assignment, never choose an
            // unrelated account or grant a new role while populating room data.
            $existing = Room::where('code', 'DEMO-SM')->first();
            $picIds = $existing?->pics()->where('role', User::ROLE_ROOM_PIC)->where('is_active', true)->pluck('users.id')->all() ?? [];
            if (! $picIds && ! Room::where('code', 'SPMT-L7-01')->whereHas('pics', fn ($query) => $query->where('role', User::ROLE_ROOM_PIC)->where('is_active', true))->exists()) {
                throw new RuntimeException('Tetapkan PIC aktif Selat Malaka sebelum memasukkan ruangan.');
            }
            // User-provided distribution; ten rooms, despite the initial total of nine.
            foreach ([2 => 1, 3 => 2, 4 => 2, 6 => 2, 7 => 3] as $number => $count) {
                $floor = Floor::firstOrCreate(['floor_number' => $number], ['name' => 'Lantai '.$number]);
                foreach (range(1, $count) as $index) {
                    $code = sprintf('SPMT-L%d-%02d', $number, $index);
                    $room = Room::firstOrCreate(['code' => $code], [
                        'name' => $number === 7 ? 'Selat Malaka '.['I', 'II', 'III'][$index - 1] : 'Ruang Rapat Lantai '.$number.' - '.$index,
                        'floor_id' => $floor->id, 'capacity' => 0,
                        'description' => 'Nama sementara sesuai lantai. Kapasitas dan fasilitas belum dikonfirmasi.',
                        'access_type' => 'all', 'requires_approval' => $number === 7,
                        'status' => 'available', 'is_active' => true,
                    ]);
                    if ($room->wasRecentlyCreated && $number === 7) {
                        $room->pics()->syncWithoutDetaching($picIds);
                    }
                }
            }
            // Retain old demo rows and their relations/history; only remove them
            // from the active catalog. Do not deactivate user-created rooms.
            Room::whereIn('code', ['DEMO-SM', 'DEMO-01', 'DEMO-02', 'DEMO-03'])
                ->where('description', 'Data dummy development; bukan data resmi perusahaan.')
                ->update(['is_active' => false]);
        });
    }
}
