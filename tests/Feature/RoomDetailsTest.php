<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Tests\PostgresTestCase;

class RoomDetailsTest extends PostgresTestCase
{
    public function test_detail_reads_real_data_escapes_text_and_handles_unknown_rooms(): void
    {
        $room = Room::where('is_active', true)->firstOrFail();
        $room->update(['name' => '<script>unsafe</script>']);
        $this->get(route('rooms.show', $room))->assertRedirect(route('login'));
        foreach (['user', 'room_pic', 'super_admin'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail())->get(route('rooms.show', $room))->assertOk()
                ->assertSee($room->name)->assertDontSee($room->name, false)->assertSee($room->floor->name)
                ->assertDontSee('Pratinjau Tahap 3');
        }
        $this->get('/rooms/preview')->assertNotFound();
        $this->get('/rooms/999999')->assertNotFound();
    }

    public function test_inactive_room_is_only_visible_to_admin_or_assigned_pic(): void
    {
        $room = Room::firstOrFail();
        $room->update(['is_active' => false]);
        $this->actingAs(User::where('role', 'user')->firstOrFail())->get(route('rooms.show', $room))->assertNotFound();
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $room->pics()->detach();
        $this->actingAs($pic)->get(route('rooms.show', $room))->assertNotFound();
        $room->pics()->attach($pic);
        $this->get(route('rooms.show', $room))->assertOk()->assertDontSee(route('rooms.book', $room), false);
        $pic->role = User::ROLE_USER;
        $pic->save();
        $this->actingAs($pic)->get(route('rooms.show', $room))->assertNotFound();
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail())->get(route('rooms.show', $room))->assertOk();
    }
}
