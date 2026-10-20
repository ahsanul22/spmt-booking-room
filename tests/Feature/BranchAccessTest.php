<?php

namespace Tests\Feature;

use App\Livewire\BookingPreparationForm;
use App\Livewire\RoomCatalog;
use App\Livewire\RoomSchedule;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Room;
use App\Models\User;
use App\Models\WhatsappTemplate;
use App\Services\BookingService;
use App\Services\BookingWhatsapp;
use App\Services\RoomAssignmentService;
use Database\Seeders\FoundationSeeder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\PostgresTestCase;

class BranchAccessTest extends PostgresTestCase
{
    private Room $hq;

    private Room $other;

    private Branch $branch;

    private User $employee;

    private User $pic;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hq = Room::where('code', 'DEMO-SM')->firstOrFail();
        $this->branch = Branch::create(['name' => 'LOKASI-BELAWAN-PRIVAT']);
        $this->other = $this->hq->replicate();
        $this->other->fill(['branch_id' => $this->branch->id, 'name' => 'RUANG-BELAWAN-PRIVAT', 'code' => 'PRIVATE-BLW'])->save();
        $this->employee = User::where('role', 'user')->firstOrFail();
        $this->pic = User::where('role', 'room_pic')->firstOrFail();
        $this->admin = User::where('role', 'super_admin')->firstOrFail();
        // Simulate an old/stale assignment. Membership must still be required.
        $this->other->pics()->attach($this->pic);
    }

    private function booking(Room $room, string $agenda): Booking
    {
        return Booking::create([
            'room_id' => $room->id, 'user_id' => $this->employee->id,
            'date' => now(config('booking.timezone'))->addDays(2)->toDateString(),
            'start_time' => '12:00', 'end_time' => '13:00', 'agenda' => $agenda,
            'status' => 'pending', 'requires_approval' => true,
            'submission_token' => (string) Str::uuid(), 'request_hash' => str_repeat('a', 64),
        ]);
    }

    public function test_employee_schedule_catalog_and_history_do_not_leak_other_locations(): void
    {
        $mine = $this->booking($this->hq, 'AGENDA-PUSAT');
        $hidden = $this->booking($this->other, 'AGENDA-BELAWAN-PRIVAT');
        $this->actingAs($this->employee);
        $this->get(route('schedule.index', ['branch_id' => $this->branch->id, 'room_id' => $this->other->id, 'date' => $mine->date]))
            ->assertOk()->assertDontSee($this->other->name)->assertDontSee($this->branch->name)->assertDontSee('name="branch_filter"', false);
        Livewire::test(RoomCatalog::class)->assertDontSee($this->other->name)->assertDontSee('name="branch_filter"', false)
            ->assertViewHas('rooms', fn ($rooms) => $rooms->every(fn ($room) => $room->branch_id === $this->hq->branch_id));
        Livewire::test(RoomSchedule::class)->call('selectDate', $mine->date)
            ->assertViewHas('daySlots', fn ($slots) => $slots->count() === 1 && $slots->first()['room_id'] === $this->hq->id);
        $this->get(route('my-bookings.index'))->assertOk()->assertSee('AGENDA-PUSAT')->assertDontSee('AGENDA-BELAWAN-PRIVAT')->assertDontSee('name="branch_id"', false);
        $this->get(route('my-bookings.show', $hidden))->assertNotFound();
        $this->get(route('my-bookings.index', ['branch_id' => $this->branch->id]))->assertForbidden();
        Livewire::test(RoomCatalog::class)->call('selectBranch', (string) $this->branch->id)->assertForbidden();
        Livewire::test(RoomSchedule::class)->call('selectRoom', (string) $this->other->id)->assertStatus(422);
    }

    public function test_direct_urls_components_and_booking_service_reject_unauthorized_rooms(): void
    {
        $this->actingAs($this->employee);
        $this->get(route('rooms.show', $this->other))->assertNotFound();
        $this->get(route('rooms.book', $this->other))->assertNotFound();
        $this->get(route('my-bookings.create', ['room_id' => $this->other->id]))->assertNotFound();
        Livewire::test(BookingPreparationForm::class, ['roomId' => $this->other->id])->assertNotFound();
        try {
            app(BookingService::class)->submit($this->employee, $this->other->id, [
                'date' => now(config('booking.timezone'))->addDays(2)->toDateString(),
                'start_time' => '12:00', 'end_time' => '13:00', 'agenda' => 'Tidak boleh tersimpan',
            ], (string) Str::uuid());
            $this->fail('Unauthorized booking must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('room', $exception->errors());
        }
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_permission_revocation_is_rechecked_on_livewire_refresh_and_submit(): void
    {
        $this->actingAs($this->employee);
        $date = now(config('booking.timezone'))->addDays(2)->toDateString();
        $this->booking($this->hq, 'AGENDA-HQ');
        $schedule = Livewire::test(RoomSchedule::class)->call('selectDate', $date);
        $catalog = Livewire::test(RoomCatalog::class);
        $form = Livewire::test(BookingPreparationForm::class, ['roomId' => $this->hq->id]);
        $this->employee->branches()->detach();
        $schedule->call('$refresh')->assertViewHas('daySlots', fn ($slots) => $slots->isEmpty())->assertViewHas('rooms', fn ($rooms) => $rooms->isEmpty());
        $catalog->call('$refresh')->assertViewHas('rooms', fn ($rooms) => $rooms->isEmpty());
        $form->call('$refresh')->assertNotFound();
        $this->get(route('rooms.show', $this->hq))->assertNotFound();
    }

    public function test_multiple_explicit_locations_work_without_employee_dropdown_and_admin_keeps_selector(): void
    {
        $this->employee->branches()->syncWithoutDetaching([$this->branch->id]);
        $this->actingAs($this->employee);
        Livewire::test(RoomCatalog::class)->assertSee($this->hq->name)->assertSee($this->other->name)->assertDontSee('name="branch_filter"', false);
        $this->get(route('rooms.show', $this->other))->assertOk();
        $this->actingAs($this->admin);
        Livewire::test(RoomCatalog::class)->assertSee('name="branch_filter"', false)->call('selectBranch', (string) $this->branch->id)
            ->assertViewHas('rooms', fn ($rooms) => $rooms->total() === 1 && $rooms->first()->id === $this->other->id);
        Livewire::test(RoomSchedule::class)->call('selectBranch', (string) $this->branch->id)->assertSee('name="branch_filter"', false);
    }

    public function test_pic_needs_membership_and_assignment_for_approvals_reports_and_dashboard(): void
    {
        $own = $this->booking($this->hq, 'PIC-HQ-VISIBLE');
        $hidden = $this->booking($this->other, 'PIC-BLW-PRIVATE');
        $this->actingAs($this->pic);
        $this->assertSame(1, $this->pic->pendingApprovalsCount());
        $this->get(route('pic.rooms.index'))->assertOk()->assertDontSee($this->other->name);
        $this->get(route('pic.approvals.index'))->assertOk()->assertSee('PIC-HQ-VISIBLE')->assertDontSee('PIC-BLW-PRIVATE')->assertDontSee('name="branch_id"', false);
        $this->get(route('pic.approvals.show', $hidden))->assertNotFound();
        $this->post(route('pic.approvals.decide', $hidden), ['decision' => 'approved'])->assertNotFound();
        $month = substr($own->date, 0, 7);
        foreach (['index', 'export', 'print'] as $action) {
            $response = $this->get(route('pic.reports.'.$action, ['month' => $month]))->assertOk();
            $body = $action === 'export' ? $response->streamedContent() : $response->getContent();
            $this->assertStringContainsString('PIC-HQ-VISIBLE', $body);
            $this->assertStringNotContainsString('PIC-BLW-PRIVATE', $body);
            $this->assertStringNotContainsString($this->branch->name, $body);
            $this->get(route('pic.reports.'.$action, ['month' => $month, 'room_id' => $this->other->id]))->assertNotFound();
        }
        try {
            app(BookingService::class)->decide($this->pic, $hidden, 'approved', null);
            $this->fail('Service must reject stale cross-location PIC assignments.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->pic->branches()->syncWithoutDetaching([$this->branch->id]);
        $this->assertSame(2, $this->pic->pendingApprovalsCount());
        $this->get(route('pic.approvals.show', $hidden))->assertOk();
        $this->pic->branches()->detach($this->branch->id);
        $this->assertSame(1, $this->pic->pendingApprovalsCount());
        $this->assertSame('pending', $hidden->fresh()->status);
    }

    public function test_only_admin_can_manage_user_locations_and_empty_selection_revokes_access(): void
    {
        $url = route('admin.users.update', $this->employee);
        $data = ['name' => $this->employee->name, 'email' => $this->employee->email,
            'role' => 'user', 'is_active' => 1, 'branch_ids' => [$this->branch->id], 'branch_selection_present' => 1];
        $this->actingAs($this->employee)->put($url, $data)->assertForbidden();
        $this->actingAs($this->admin)->put($url, $data)->assertSessionHasNoErrors();
        $this->assertFalse($this->employee->canAccessBranch($this->hq->branch_id));
        $this->assertTrue($this->employee->canAccessBranch($this->branch->id));
        $this->put($url, array_replace($data, ['branch_ids' => [999999], 'name' => 'INVALID-NAME']))->assertSessionHasErrors('branch_ids.0');
        $this->assertNotSame('INVALID-NAME', $this->employee->fresh()->name);
        $this->assertTrue($this->employee->canAccessBranch($this->branch->id));
        unset($data['branch_ids']);
        $this->put($url, $data)->assertSessionHasNoErrors();
        $this->assertSame(0, $this->employee->branches()->count());
        $this->get(route('admin.users.edit', $this->employee))->assertOk()->assertSee('name="branch_ids[]"', false);
    }

    public function test_migration_backfills_head_office_and_seeder_does_not_restore_revoked_permissions(): void
    {
        $migration = require database_path('migrations/2026_10_19_000003_add_user_branch_permissions.php');
        $migration->down();
        $migration->up();
        foreach ([$this->employee, $this->pic, $this->admin] as $user) {
            $this->assertSame([$this->hq->branch_id], $user->branches()->pluck('branches.id')->all());
        }
        $this->employee->branches()->detach();
        $this->seed(FoundationSeeder::class);
        $this->assertSame(0, $this->employee->branches()->count());
    }

    public function test_pic_assignment_and_notification_recipients_require_location_permission(): void
    {
        try {
            app(RoomAssignmentService::class)->syncPics($this->other, [$this->pic->id]);
            $this->fail('PIC without location permission cannot be assigned.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('pic_ids', $exception->errors());
        }
        $this->pic->whatsapp_number = '+6281234567890';
        $this->pic->save();
        $booking = $this->booking($this->other, 'Private notification');
        $template = new WhatsappTemplate(['include_room_pics' => true]);
        $this->assertFalse(app(BookingWhatsapp::class)->recipients($booking, 'approval_request', $template)->contains('id', $this->pic->id));
        $this->pic->branches()->syncWithoutDetaching([$this->branch->id]);
        app(RoomAssignmentService::class)->syncPics($this->other, [$this->pic->id]);
        $this->assertTrue(app(BookingWhatsapp::class)->recipients($booking, 'approval_request', $template)->contains('id', $this->pic->id));
    }

    public function test_revoked_user_cannot_replay_a_previously_successful_booking_token(): void
    {
        $service = app(BookingService::class);
        $token = (string) Str::uuid();
        $data = ['date' => now(config('booking.timezone'))->addDays(2)->toDateString(), 'start_time' => '14:00', 'end_time' => '15:00', 'agenda' => 'Retry after revocation'];
        $booking = $service->submit($this->employee, $this->hq->id, $data, $token);
        $this->employee->branches()->detach();
        try {
            $service->submit($this->employee, $this->hq->id, $data, $token);
            $this->fail('Even an idempotent retry must recheck location permission.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('room', $exception->errors());
        }
        $this->assertDatabaseCount('bookings', 1);
        $this->assertModelExists($booking);
    }
}
