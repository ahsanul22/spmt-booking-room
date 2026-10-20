<?php

namespace Tests\Feature;

use App\Livewire\BookingPreparationForm;
use App\Livewire\RoomCatalog;
use App\Livewire\RoomSchedule;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\PostgresTestCase;

class BranchAndItSupportTest extends PostgresTestCase
{
    public function test_branch_management_filters_and_room_history_are_protected(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $user = User::where('role', 'user')->firstOrFail();
        $this->get(route('admin.branches.index'))->assertRedirect(route('login'));
        $this->actingAs($user)->post(route('admin.branches.store'), ['name' => 'Belawan'])->assertForbidden();
        $this->actingAs($admin)->post(route('admin.branches.store'), ['name' => 'Belawan'])->assertSessionHasNoErrors();
        $branch = Branch::where('name', 'Belawan')->firstOrFail();
        $this->post(route('admin.branches.store'), ['name' => 'Belawan'])->assertSessionHasErrors('name');
        $this->put(route('admin.branches.update', $branch), ['name' => 'Branch Belawan'])->assertSessionHasNoErrors();
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $originalBranch = $room->branch_id;
        $room->update(['branch_id' => $branch->id]);
        $this->get(route('admin.rooms.index', ['branch_id' => $branch->id]))->assertOk()
            ->assertViewHas('rooms', fn ($rooms) => $rooms->total() === 1 && $rooms->first()->id === $room->id);
        $user->branches()->sync([$branch->id]);
        User::where('role', 'room_pic')->firstOrFail()->branches()->syncWithoutDetaching([$branch->id]);
        $this->actingAs($user);
        Livewire::test(RoomCatalog::class)
            ->assertViewHas('rooms', fn ($rooms) => $rooms->total() === 1 && $rooms->first()->id === $room->id)
            ->assertDontSee('name="branch_filter"', false);
        Livewire::actingAs($admin)->test(RoomCatalog::class)->call('selectBranch', (string) $branch->id)
            ->assertViewHas('rooms', fn ($rooms) => $rooms->total() === 1)
            ->call('selectBranch', '')->assertViewHas('rooms', fn ($rooms) => $rooms->total() > 1);
        $booking = $this->submit($room);
        $this->actingAs($admin)->put(route('admin.rooms.update', $room), [
            'name' => $room->name, 'code' => $room->code, 'floor_id' => $room->floor_id,
            'branch_id' => $originalBranch, 'access_type' => 'all', 'requires_approval' => 1,
            'status' => 'available', 'is_active' => 1, 'facility_ids' => [],
        ])->assertSessionHasErrors('branch_id');
        $this->assertSame($branch->id, $room->fresh()->branch_id);
        $this->assertModelExists($booking);
    }

    private function submit(Room $room, array $changes = []): Booking
    {
        return app(BookingService::class)->submit(User::where('role', 'user')->firstOrFail(), $room->id, array_replace([
            'date' => now(config('booking.timezone'))->addDays(2)->toDateString(),
            'start_time' => '12:00', 'end_time' => '13:00', 'agenda' => 'Rapat pengujian',
        ], $changes), (string) Str::uuid());
    }

    public function test_it_checkbox_creates_pending_and_admin_can_approve_it_without_consumption(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $room->update(['requires_approval' => false, 'allows_meeting_preparation' => false]);
        $user = User::where('role', 'user')->firstOrFail();
        Livewire::actingAs($user)->test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->set('date', now(config('booking.timezone'))->addDays(2)->toDateString())
            ->set('additionalDates', [now(config('booking.timezone'))->addDays(3)->toDateString()])
            ->set('start_time', '12:00')->set('agenda', 'Bantuan presentasi')->set('it_support', true)
            ->call('checkPlan')->assertHasNoErrors()->call('submitBooking')->assertHasNoErrors();
        $this->assertDatabaseCount('bookings', 2);
        $this->assertTrue(Booking::get()->every(fn ($record) => $record->it_support && $record->status === 'pending'));
        $booking = Booking::latest('id')->firstOrFail();
        $this->assertTrue($booking->it_support);
        $this->assertSame('pending', $booking->status);
        $this->assertFalse($booking->meeting_preparation);
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.approvals.index'))->assertOk()->assertSee('approved_it_support');
        $this->post(route('admin.approvals.decide', $booking), ['decision' => 'approved', 'approved_it_support' => 1])->assertSessionHasNoErrors();
        $this->assertTrue($booking->fresh()->approved_it_support);
        $this->assertSame(1, Booking::where('status', 'pending')->count());
        $this->actingAs($user)->get(route('my-bookings.show', $booking))->assertOk()->assertSee('Teknisi IT disetujui');
    }

    public function test_pic_assignment_and_it_subset_decisions_are_enforced(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $booking = $this->submit($room, ['it_support' => true]);
        $room->pics()->detach($pic);
        $this->actingAs($pic)->post(route('pic.approvals.decide', $booking), ['decision' => 'approved', 'approved_it_support' => 1])->assertNotFound();
        $room->pics()->attach($pic);
        $this->post(route('pic.approvals.decide', $booking), ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->assertSame('approved', $booking->fresh()->status);
        $this->assertFalse($booking->fresh()->approved_it_support);
        $notRequested = $this->submit($room, ['start_time' => '14:00', 'end_time' => '15:00']);
        $this->post(route('pic.approvals.decide', $notRequested), ['decision' => 'approved', 'approved_it_support' => 1])->assertSessionHasErrors('approved_it_support');
        $this->assertSame('pending', $notRequested->fresh()->status);
        $this->post(route('pic.approvals.decide', $notRequested), ['decision' => 'rejected', 'rejection_reason' => 'Tidak tersedia', 'approved_it_support' => 1])->assertSessionHasNoErrors();
        $this->assertFalse($notRequested->fresh()->approved_it_support);
    }

    public function test_branch_schedule_and_report_include_all_requirements_without_leaking_other_branches(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $room->update(['allows_meeting_preparation' => true]);
        $branch = Branch::create(['name' => 'Belawan']);
        $other = Room::where('code', '!=', 'DEMO-SM')->where('access_type', 'all')->firstOrFail();
        $other->update(['branch_id' => $branch->id, 'status' => 'available']);
        User::whereIn('role', ['user', 'room_pic'])->get()->each(fn ($account) => $account->branches()->syncWithoutDetaching([$branch->id]));
        $booking = $this->submit($room, ['agenda' => 'PUSAT-ONLY', 'meeting_preparation' => true, 'participant_count' => 27, 'consumption_snack' => true, 'consumption_meal' => true, 'it_support' => true, 'notes' => '=PRIVATE-NOTE']);
        $this->submit($other, ['agenda' => 'BELAWAN-ONLY']);
        $admin = User::where('role', 'super_admin')->firstOrFail();
        app(BookingService::class)->decide($admin, $booking, 'approved', 'IT siap', ['approved_consumption_snack' => 1, 'approved_it_support' => 1]);
        Livewire::actingAs($admin)->test(RoomSchedule::class)->call('selectDate', $booking->date)
            ->call('selectRoom', (string) $room->id)->call('selectBranch', (string) $branch->id)->assertSet('roomId', null)
            ->assertViewHas('daySlots', fn ($slots) => $slots->count() === 1 && $slots->first()['room_id'] === $other->id);
        $filter = ['month' => substr($booking->date, 0, 7), 'branch_id' => $room->branch_id];
        $this->actingAs($admin);
        foreach (['index', 'print', 'export'] as $action) {
            $response = $this->get(route('admin.reports.'.$action, $filter))->assertOk();
            $body = $action === 'export' ? $response->streamedContent() : $response->getContent();
            $this->assertStringContainsString('PUSAT-ONLY', $body);
            $this->assertStringNotContainsString('BELAWAN-ONLY', $body);
            $this->assertStringContainsString('27', $body);
            $this->assertStringContainsString('IT siap', $body);
            $this->assertStringContainsString('Teknisi IT', $body);
            if ($action === 'export') {
                $rows = array_values(array_filter(explode("\n", trim($body))));
                $columns = str_getcsv($rows[1], ',', '"', '');
                $this->assertSame(['27', 'Ya', 'Ya', 'Ya', 'Tidak', 'Ya', 'Ya'], array_slice($columns, 10, 7));
                $this->assertStringContainsString("'=PRIVATE-NOTE", $body);
            }
        }
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $pic->managedRooms()->sync([$room->id]);
        $this->actingAs($pic)->getJson(route('pic.reports.export', ['month' => $filter['month'], 'branch_id' => $branch->id]))->assertUnprocessable()->assertJsonValidationErrors('branch_id');
    }

    public function test_it_requirement_participates_in_idempotency_and_requires_an_active_pic(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $room->update(['requires_approval' => false]);
        $service = app(BookingService::class);
        $user = User::where('role', 'user')->firstOrFail();
        $data = ['date' => now(config('booking.timezone'))->addDays(2)->toDateString(), 'start_time' => '12:00', 'end_time' => '13:00', 'agenda' => 'IT retry', 'it_support' => true];
        $token = (string) Str::uuid();
        $booking = $service->submit($user, $room->id, $data, $token);
        $this->assertSame($booking->id, $service->submit($user, $room->id, $data, $token)->id);
        try {
            $service->submit($user, $room->id, array_replace($data, ['it_support' => false]), $token);
            $this->fail('Changed IT request must not reuse the same token.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('agenda', $exception->errors());
        }
        $room->pics()->detach();
        $this->expectException(ValidationException::class);
        $service->submit($user, $room->id, array_replace($data, ['start_time' => '14:00', 'end_time' => '15:00']), (string) Str::uuid());
    }
}
