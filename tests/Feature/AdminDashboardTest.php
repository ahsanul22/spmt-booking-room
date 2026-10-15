<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\OrganizationalUnit;
use App\Models\Room;
use App\Models\User;
use Tests\PostgresTestCase;

class AdminDashboardTest extends PostgresTestCase
{
    public function test_dashboard_focuses_on_administration_and_counts_incomplete_active_rooms(): void
    {
        Room::query()->update(['is_active' => false]);
        $floorId = Room::firstOrFail()->floor_id;
        $make = fn ($name, $status, $active = true) => Room::create([
            'name' => $name, 'floor_id' => $floorId, 'capacity' => 0, 'status' => $status,
            'is_active' => $active, 'requires_approval' => true, 'access_type' => 'all',
        ]);
        $ready = $make('Ruangan lengkap', 'available');
        $missing = $make('Ruangan perlu dilengkapi', 'maintenance');
        $inactivePicRoom = $make('Ruangan PIC nonaktif', 'unavailable');
        $make('Ruangan nonaktif', 'available', false);
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $ready->pics()->attach($pic);
        $ready->facilities()->attach(Facility::firstOrFail());
        $inactivePic = new User(['name' => 'PIC nonaktif', 'email' => 'inactive-pic@example.test', 'password' => 'password']);
        $inactivePic->forceFill(['role' => User::ROLE_ROOM_PIC, 'is_active' => false])->save();
        $inactivePicRoom->pics()->attach($inactivePic);
        $inactivePicRoom->facilities()->attach(Facility::firstOrFail());
        // An active employee assigned in the pivot still does not qualify as a PIC.
        $missing->pics()->attach(User::where('role', 'user')->firstOrFail());
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $this->get('/admin/dashboard')->assertOk()
            ->assertViewHas('stats', fn ($stats) => array_column($stats, 'value') === [Room::count(), User::where('is_active', true)->count(), OrganizationalUnit::where('is_active', true)->count()])
            ->assertViewHas('activeRoomCount', 3)
            ->assertViewHas('missingPicCount', 2)->assertViewHas('missingFacilitiesCount', 1)
            ->assertViewHas('roomConditions', fn ($conditions) => $conditions->pluck('count')->all() === [1, 1, 1])
            ->assertViewHas('attentionRooms', fn ($rooms) => $rooms->pluck('id')->sort()->values()->all() === collect([$missing->id, $inactivePicRoom->id])->sort()->values()->all())
            ->assertSee('Kondisi Ruangan')->assertSee('Kelengkapan Pengaturan Ruangan')
            ->assertSee(route('admin.rooms.show', $missing), false)
            ->assertDontSee('Aktivitas Booking')->assertDontSee('Booking hari ini')
            ->assertDontSee('Menunggu approval')->assertDontSee('Rapat berlangsung')->assertDontSee('Kelola Workspace');
        $this->get('/dashboard')->assertOk()->assertViewIs('admin.dashboard');
        $this->get('/pic/dashboard')->assertOk()->assertViewIs('admin.dashboard');
    }

    public function test_dashboard_handles_no_active_rooms_without_dividing_by_zero(): void
    {
        Room::query()->update(['is_active' => false]);
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail())
            ->get('/admin/dashboard')->assertOk()
            ->assertViewHas('activeRoomCount', 0)->assertViewHas('missingPicCount', 0)
            ->assertViewHas('missingFacilitiesCount', 0)
            ->assertViewHas('roomConditions', fn ($conditions) => $conditions->pluck('percentage')->all() === [0, 0, 0])
            ->assertViewHas('attentionRooms', fn ($rooms) => $rooms->isEmpty())
            ->assertSee('Belum ada ruangan aktif.');
    }
}
