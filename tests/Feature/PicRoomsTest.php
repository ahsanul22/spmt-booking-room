<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Tests\PostgresTestCase;

class PicRoomsTest extends PostgresTestCase
{
    public function test_only_assigned_rooms_are_shown_with_information_and_schedule_links(): void
    {
        $pic = User::where('role', User::ROLE_ROOM_PIC)->firstOrFail();
        $room = Room::with('facilities')->firstOrFail();
        $pic->managedRooms()->sync([$room->id]);
        $room->update(['name' => '<script>room</script>', 'is_active' => false]);
        $response = $this->actingAs($pic)->get('/pic/rooms')->assertOk()
            ->assertSee($room->name)->assertDontSee($room->name, false)
            ->assertSee($room->floor->name)->assertSee('Nonaktif')
            ->assertSee(route('schedule.index', ['room_id' => $room->id]), false)
            ->assertDontSee('My Booking')->assertDontSee('Booking Ruangan')->assertDontSee('Pratinjau Tahap 3');
        foreach ($room->facilities as $facility) {
            $response->assertSee($facility->name);
        }
        foreach (Room::whereKeyNot($room->id)->get() as $other) {
            $response->assertDontSee($other->name);
        }
        $pic->managedRooms()->detach();
        $this->get('/pic/rooms')->assertSee('Belum ada ruangan yang ditugaskan.')->assertDontSee($room->name);
    }

    public function test_assignment_list_is_paginated_and_includes_maintenance_rooms(): void
    {
        $pic = User::where('role', User::ROLE_ROOM_PIC)->firstOrFail();
        $pic->managedRooms()->detach();
        $template = Room::firstOrFail();
        for ($i = 1; $i <= 10; $i++) {
            $room = $template->replicate();
            $room->fill(['code' => 'PIC-TEST-'.$i, 'name' => sprintf('Ruangan Uji %02d', $i), 'status' => 'maintenance']);
            $room->save();
            $pic->managedRooms()->attach($room);
        }
        $this->actingAs($pic)->get('/pic/rooms')->assertOk()->assertSee('10 ruangan')
            ->assertSee('Dalam perawatan')->assertSee('Ruangan Uji 09')->assertDontSee('Ruangan Uji 10');
        $this->get('/pic/rooms?page=2')->assertOk()->assertSee('Ruangan Uji 10')->assertDontSee('Ruangan Uji 01');
    }

    public function test_access_stays_protected_and_admin_does_not_inherit_pic_assignments(): void
    {
        $this->get('/pic/rooms')->assertRedirect('/login');
        $this->actingAs(User::where('role', User::ROLE_USER)->firstOrFail())->get('/pic/rooms')->assertForbidden();
        $admin = User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail();
        $admin->managedRooms()->detach();
        $this->actingAs($admin)->get('/pic/rooms')->assertOk()->assertSee('Belum ada ruangan yang ditugaskan.');
    }
}
