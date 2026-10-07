<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\Floor;
use App\Models\OrganizationalUnit;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\PostgresTestCase;

class ReadableRoutesTest extends PostgresTestCase
{
    public function test_readable_and_legacy_room_links_work_for_each_authorized_role(): void
    {
        $room = Room::where('is_active', true)->firstOrFail();
        $room->update(['name' => 'Selat Malaka I']);
        $url = route('rooms.show', $room);
        $this->assertStringEndsWith('/'.$room->id.'-selat-malaka-i', $url);
        $this->get($url)->assertRedirect(route('login'));

        foreach (['user', 'room_pic', 'super_admin'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            $this->get($url)->assertOk()->assertSee($room->name);
            $this->get(route('rooms.show', $room->id))->assertOk()->assertSee($room->name);
        }

        $room->update(['name' => 'Ruang Baru']);
        $this->get($url)->assertOk()->assertSee('Ruang Baru');
        $this->assertStringEndsWith('/'.$room->id.'-ruang-baru', route('rooms.show', $room));
        foreach (['preview', '0', '1--invalid', '999999999999999999999999999-room'] as $value) {
            $this->get(route('rooms.show', $value))->assertNotFound();
        }

        $duplicate = Room::where('id', '!=', $room->id)->firstOrFail();
        $duplicate->update(['name' => $room->name]);
        $this->assertNotSame(route('rooms.show', $room), route('rooms.show', $duplicate));
    }

    public function test_admin_master_data_links_forms_and_legacy_actions_use_correct_records(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        foreach ([
            'rooms' => Room::firstOrFail(),
            'floors' => Floor::firstOrFail(),
            'facilities' => Facility::firstOrFail(),
            'organizational-units' => OrganizationalUnit::firstOrFail(),
        ] as $resource => $record) {
            $this->get(route('admin.'.$resource.'.index'))->assertOk()
                ->assertSee(route('admin.'.$resource.'.show', $record), false);
            $this->get(route('admin.'.$resource.'.show', $record))->assertOk();
            $this->get(route('admin.'.$resource.'.edit', $record))->assertOk()
                ->assertSee(route('admin.'.$resource.'.update', $record), false);
            $this->patch(route('admin.'.$resource.'.status', $record), ['is_active' => false])
                ->assertRedirect(route('admin.'.$resource.'.show', $record));
            $this->assertFalse($record->fresh()->is_active);
            $this->patch(route('admin.'.$resource.'.status', $record->id), ['is_active' => true])
                ->assertRedirect(route('admin.'.$resource.'.show', $record));
            $this->assertTrue($record->fresh()->is_active);
        }
        $room = Room::firstOrFail();
        $this->get(route('admin.rooms.pics', $room))->assertRedirect(route('admin.rooms.show', $room).'#pics');
        $this->get(route('admin.rooms.access', $room))->assertRedirect(route('admin.rooms.show', $room).'#access');
        $this->actingAs(User::where('role', 'user')->firstOrFail())
            ->get(route('admin.rooms.edit', $room))->assertForbidden();
        $this->patch(route('admin.rooms.status', $room), ['is_active' => false])->assertForbidden();
        $this->assertTrue($room->fresh()->is_active);
    }

    public function test_admin_approval_urls_keep_navigation_decisions_and_authorization_in_admin_area(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $employee = User::where('role', 'user')->firstOrFail();
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $this->travelTo(now(config('booking.timezone'))->startOfDay());
        $booking = Booking::create([
            'room_id' => $room->id, 'user_id' => $employee->id,
            'date' => now(config('booking.timezone'))->addDays(2)->toDateString(),
            'start_time' => '12:00', 'end_time' => '13:00', 'agenda' => 'Private agenda',
            'status' => 'pending', 'requires_approval' => true,
            'submission_token' => (string) Str::uuid(), 'request_hash' => str_repeat('a', 64),
        ]);

        $this->get(route('admin.approvals.index'))->assertRedirect(route('login'));
        foreach (['user', 'room_pic'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            $this->get(route('admin.approvals.index'))->assertForbidden();
            $this->post(route('admin.approvals.decide', $booking->id), ['decision' => 'approved'])->assertForbidden();
        }

        $this->actingAs($admin)->get(route('admin.approvals.index'))->assertOk()
            ->assertSee(route('admin.approvals.history'), false)
            ->assertSee(route('admin.approvals.decide', $booking->id), false);
        $this->get(route('admin.approvals.show', $booking->id))->assertOk()
            ->assertSee(route('admin.approvals.index'), false);
        $this->from(route('admin.approvals.index'))->post(route('admin.approvals.decide', $booking->id), [
            'decision' => 'rejected', 'return_to' => 'list', 'page' => 1,
        ])->assertRedirect(route('admin.approvals.index'))->assertSessionHasErrors('rejection_reason');
        $this->post(route('admin.approvals.decide', $booking->id), [
            'decision' => 'approved', 'decision_notes' => 'Reviewed', 'return_to' => 'list',
        ])->assertRedirect(route('admin.approvals.index', ['page' => 1]));
        $this->assertSame('approved', $booking->fresh()->status);
        $this->get(route('admin.approvals.history'))->assertOk()->assertSee($booking->agenda);
        // Existing Admin bookmarks in the PIC area continue to work.
        $this->get(route('pic.approvals.show', $booking->id))->assertOk();
        $this->travelBack();
    }
}
