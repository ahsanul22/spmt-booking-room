<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Floor;
use App\Models\OrganizationalUnit;
use App\Models\Room;
use App\Models\User;
use App\Rules\EligibleRoomPic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use LogicException;

class FoundationSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Seeder dummy hanya boleh dijalankan di environment local atau testing.');
        }

        DB::transaction(function () {
            // Development examples, not the official company structure.
            $directorate = OrganizationalUnit::firstOrCreate([
                'name' => 'Direktorat SDM', 'type' => 'directorate', 'parent_id' => null,
            ]);
            $division = OrganizationalUnit::firstOrCreate([
                'name' => 'Divisi Layanan SDM dan Umum', 'type' => 'division', 'parent_id' => $directorate->id,
            ]);
            $department = OrganizationalUnit::firstOrCreate([
                'name' => 'Departemen Pengadaan', 'type' => 'department', 'parent_id' => $division->id,
            ]);

            $itDivision = OrganizationalUnit::firstOrCreate([
                'name' => 'Divisi Teknologi Informasi', 'type' => 'division', 'parent_id' => $directorate->id,
            ]);
            OrganizationalUnit::firstOrCreate([
                'name' => 'Departemen Pengembangan Sistem', 'type' => 'department', 'parent_id' => $itDivision->id,
            ]);

            $operationsDirectorate = OrganizationalUnit::firstOrCreate([
                'name' => 'Direktorat Operasi', 'type' => 'directorate', 'parent_id' => null,
            ]);
            $operationsDivision = OrganizationalUnit::firstOrCreate([
                'name' => 'Divisi Operasional Terminal', 'type' => 'division', 'parent_id' => $operationsDirectorate->id,
            ]);
            foreach (['Departemen Perencanaan Operasi', 'Departemen Pelayanan Terminal'] as $name) {
                OrganizationalUnit::firstOrCreate([
                    'name' => $name, 'type' => 'department', 'parent_id' => $operationsDivision->id,
                ]);
            }

            $this->user('admin@example.test', 'Super Admin Demo', User::ROLE_SUPER_ADMIN, $directorate);
            $pic = $this->user('pic@example.test', 'Room PIC Demo', User::ROLE_ROOM_PIC, $division);
            $this->user('pegawai@example.test', 'Pegawai Demo', User::ROLE_USER, $department);

            $floors = collect(range(1, 8))->mapWithKeys(fn (int $number) => [
                $number => Floor::firstOrCreate(['floor_number' => $number], ['name' => "Lantai {$number}"]),
            ]);

            $facilities = collect(['Projector', 'TV / Display', 'HDMI', 'Whiteboard', 'Video Conference', 'Microphone', 'Speaker'])
                ->mapWithKeys(fn (string $name) => [$name => Facility::firstOrCreate(['name' => $name])]);

            $examples = [
                ['DEMO-SM', 'Selat Malaka', 7, 20, 'all', false, 'available'],
                ['DEMO-01', 'Ruang Demo Terbatas', 2, 10, 'restricted', true, 'available'],
                ['DEMO-02', 'Ruang Demo Bersama', 3, 15, 'all', true, 'maintenance'],
                ['DEMO-03', 'Ruang Demo Unit', 7, 8, 'restricted', false, 'unavailable'],
            ];

            foreach ($examples as [$code, $name, $floor, $capacity, $access, $approval, $status]) {
                $room = Room::firstOrCreate(['code' => $code], [
                    'name' => $name, 'floor_id' => $floors[$floor]->id, 'capacity' => $capacity,
                    'description' => 'Data dummy development; bukan data resmi perusahaan.',
                    'access_type' => $access, 'requires_approval' => $approval, 'status' => $status,
                ]);

                Validator::make(['pic_id' => $pic->id], ['pic_id' => [new EligibleRoomPic]])->validate();
                $room->pics()->syncWithoutDetaching([$pic->id]);
                $room->facilities()->syncWithoutDetaching([
                    $facilities['Projector']->id, $facilities['HDMI']->id, $facilities['Whiteboard']->id,
                ]);
                if ($room->access_type === 'restricted') {
                    $room->allowedOrganizationalUnits()->syncWithoutDetaching([$division->id, $department->id]);
                }
            }
        });
    }

    private function user(string $email, string $name, string $role, OrganizationalUnit $unit): User
    {
        // Keep existing passwords and roles intact when seeding again.
        $user = User::firstOrNew(['email' => $email]);
        if (! $user->exists) {
            $user->forceFill([
                'name' => $name, 'role' => $role, 'organizational_unit_id' => $unit->id,
                'password' => Hash::make('password'), 'is_active' => true,
            ])->save();
        }

        return $user;
    }
}
