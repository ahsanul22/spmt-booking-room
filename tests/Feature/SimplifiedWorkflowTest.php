<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\PostgresTestCase;

class SimplifiedWorkflowTest extends PostgresTestCase
{
    public function test_integrated_room_management_and_safe_deletion(): void
    {
        $room = Room::where('access_type', 'restricted')->firstOrFail();
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $url = route('admin.rooms.destroy', $room);
        $this->delete($url, ['confirm_delete' => 1])->assertRedirect(route('login'));
        foreach (['user', 'room_pic'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail())->delete($url, ['confirm_delete' => 1])->assertForbidden();
        }
        $this->actingAs($admin);
        $this->get(route('admin.rooms.show', $room))->assertOk()->assertSee(['name="pic_ids[]"', 'name="organizational_unit_ids[]"', 'name="is_active"', 'id="delete-room"'], false);
        foreach (['pics', 'access'] as $section) {
            $this->get(route('admin.rooms.'.$section, $room))->assertRedirect(route('admin.rooms.show', $room).'#'.$section);
        }
        $this->delete($url)->assertSessionHasErrors('confirm_delete');
        $this->assertModelExists($room);
        $facilities = $room->facilities->pluck('id');
        $pics = $room->pics->pluck('id');
        $units = $room->allowedOrganizationalUnits->pluck('id');
        $this->delete($url, ['confirm_delete' => 1])->assertRedirect(route('admin.rooms.index'));
        $this->assertModelMissing($room);
        foreach (['facility_room', 'room_pics', 'room_unit_access'] as $table) {
            $this->assertDatabaseMissing($table, ['room_id' => $room->id]);
        }
        $this->assertSame($facilities->count(), DB::table('facilities')->whereIn('id', $facilities)->count());
        $this->assertSame($pics->count(), DB::table('users')->whereIn('id', $pics)->count());
        $this->assertSame($units->count(), DB::table('organizational_units')->whereIn('id', $units)->count());
    }

    public function test_validation_in_one_room_section_preserves_other_selections(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $room = Room::where('access_type', 'restricted')->firstOrFail();
        $url = route('admin.rooms.show', $room);
        $facility = $room->facilities->first();
        $pic = $room->pics->first();
        $unit = $room->allowedOrganizationalUnits->first();
        $this->from($url)->put(route('admin.rooms.pics.update', $room), ['pic_ids' => ['invalid']])->assertSessionHasErrors('pic_ids.0');
        $this->get($url)->assertOk()
            ->assertSee('name="facility_ids[]" value="'.$facility->id.'" checked', false)
            ->assertSee('name="organizational_unit_ids[]" value="'.$unit->id.'" checked', false);
        $this->from($url)->put(route('admin.rooms.access.update', $room), ['organizational_unit_ids' => ['invalid']])->assertSessionHasErrors('organizational_unit_ids.0');
        $this->get($url)->assertOk()->assertSee('name="pic_ids[]" value="'.$pic->id.'" checked', false);
        $this->from($url)->put(route('admin.rooms.update', $room), ['name' => '', 'facility_ids' => []])->assertSessionHasErrors('name');
        $response = $this->get($url)->assertOk()->assertSee('name="pic_ids[]" value="'.$pic->id.'" checked', false);
        $this->assertDoesNotMatchRegularExpression('/name="facility_ids\[\]"[^>]* checked/', $response->getContent());
        $this->assertSame(3, $room->facilities()->count());
    }

    public function test_review_notes_booking_history_and_delete_guard(): void
    {
        $this->travelTo(now()->setTimezone('Asia/Jakarta')->startOfDay()->addHours(8));
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $room->update(['requires_approval' => true]);
        $user = User::where('role', 'user')->firstOrFail();
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $service = app(BookingService::class);
        $bookings = [];
        foreach (['12:00', '14:00'] as $start) {
            $bookings[] = $service->submit($user, $room->id, ['date' => now()->addDay()->format('Y-m-d'), 'start_time' => $start, 'end_time' => $start === '12:00' ? '13:00' : '15:00', 'agenda' => 'Review agenda', 'notes' => '<script>private notes</script>'], (string) Str::uuid());
        }
        $this->actingAs($admin)->get(route('pic.approvals.index'))->assertOk()->assertSee('Review')->assertSee($user->organizationalUnit->name)->assertDontSee('<script>private notes</script>', false);
        $this->from(route('admin.rooms.show', $room))->delete(route('admin.rooms.destroy', $room), ['confirm_delete' => 1])->assertSessionHasErrors('room');
        $this->assertModelExists($room);
        $this->assertDatabaseCount('bookings', 2);
        $this->post(route('pic.approvals.decide', $bookings[0]), ['decision' => 'approved', 'decision_notes' => 'Gunakan sesuai agenda.', 'return_to' => 'list'])->assertSessionHasNoErrors();
        $this->assertSame('Gunakan sesuai agenda.', $bookings[0]->fresh()->decision_notes);
        $this->assertNull($bookings[0]->fresh()->rejection_reason);
        $this->post(route('pic.approvals.decide', $bookings[1]), ['decision' => 'rejected', 'decision_notes' => '   '])->assertSessionHasErrors('rejection_reason');
        $this->assertSame('pending', $bookings[1]->fresh()->status);
        $this->post(route('pic.approvals.decide', $bookings[1]), ['decision' => 'rejected', 'decision_notes' => str_repeat('x', 2001)])->assertSessionHasErrors('decision_notes');
        $this->post(route('pic.approvals.decide', $bookings[1]), ['decision' => 'rejected', 'decision_notes' => 'Jadwalkan ulang.'])->assertSessionHasNoErrors();
        $this->assertSame('Jadwalkan ulang.', $bookings[1]->fresh()->decision_notes);
        $this->assertSame('Jadwalkan ulang.', $bookings[1]->fresh()->rejection_reason);
        $this->actingAs($user)->get(route('my-bookings.show', $bookings[0]))->assertSee('Gunakan sesuai agenda.');
        $this->actingAs($admin)->delete(route('admin.rooms.destroy', $room), ['confirm_delete' => 1])->assertSessionHasErrors('room');
        $this->assertDatabaseCount('bookings', 2);
        $this->travelBack();
    }
}
