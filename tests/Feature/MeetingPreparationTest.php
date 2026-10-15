<?php

namespace Tests\Feature;

use App\Livewire\BookingPreparationForm;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Database\Seeders\BookingRoomsSeeder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\PostgresTestCase;

class MeetingPreparationTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-08 10:00', 'Asia/Jakarta'));
        $this->actingAs(User::where('role', 'user')->firstOrFail());
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    public function test_conditional_fields_validate_persist_and_are_visible_to_reviewer(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $form = Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->assertSee('Tambahkan kebutuhan persiapan rapat')->assertDontSee('Perkiraan Jumlah Peserta')
            ->set('date', '2026-10-08')->set('start_time', '13:00')->set('agenda', 'Rapat persiapan')
            ->set('meeting_preparation', true)->assertSee('Perkiraan Jumlah Peserta')
            ->call('checkPlan')->assertHasErrors('participant_count')
            ->set('participant_count', 0)->call('submitBooking')->assertHasErrors('participant_count')
            ->set('participant_count', 25)->set('consumption_snack', true)->set('consumption_meal', true)
            ->call('checkPlan')->assertHasNoErrors()->call('submitBooking')->assertHasNoErrors();
        $booking = Booking::sole();
        $this->assertSame('pending', $booking->status);
        $this->assertSame(25, $booking->participant_count);
        $this->assertTrue($booking->consumption_snack);
        $this->assertTrue($booking->consumption_meal);
        $form->assertRedirect(route('my-bookings.show', $booking->id));
        $this->get(route('my-bookings.show', $booking->id))->assertOk()->assertSee('25 orang')->assertSee('Snack')->assertSee('Makan');
        $this->actingAs(User::where('role', 'room_pic')->firstOrFail())
            ->get(route('pic.approvals.index'))->assertOk()->assertSee('25 orang')->assertSee('Snack');
    }

    public function test_disabled_room_rejects_preparation_and_unchecking_discards_details(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $form = Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->set('date', '2026-10-08')->set('start_time', '13:00')->set('agenda', 'Rapat')
            ->set('meeting_preparation', true)->set('participant_count', 10)->set('consumption_snack', true);
        $room->update(['allows_meeting_preparation' => false]);
        $form->call('submitBooking')->assertHasErrors('meeting_preparation');
        $this->assertDatabaseCount('bookings', 0);
        $form->set('meeting_preparation', false)->assertSet('participant_count', null)->assertSet('consumption_snack', false)->assertSet('consumption_meal', false)
            ->call('submitBooking')->assertHasNoErrors();
        $booking = Booking::sole();
        $this->assertNull($booking->participant_count);
        $this->assertFalse($booking->consumption_snack);
        $this->assertFalse($booking->consumption_meal);
    }

    public function test_preparation_is_part_of_idempotency_and_room_setting_is_editable(): void
    {
        $room = Room::where('code', 'DEMO-01')->firstOrFail();
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $employee = auth()->user();
        $this->actingAs($admin)->put(route('admin.rooms.update', $room), [
            'name' => $room->name, 'code' => $room->code, 'floor_id' => $room->floor_id,
            'access_type' => 'all', 'requires_approval' => 1, 'allows_meeting_preparation' => 1,
            'status' => 'available', 'is_active' => 1, 'facility_ids' => [],
        ])->assertSessionHasNoErrors();
        $this->assertTrue($room->fresh()->allows_meeting_preparation);
        $this->get(route('admin.rooms.show', $room))->assertOk()->assertSee('Opsi Lanjutan Booking')
            ->assertSee('Tampilkan jumlah peserta dan permintaan konsumsi')
            ->assertSee('id="allows-meeting-preparation"', false);
        $this->actingAs($employee);
        Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])->assertSee('Tambahkan kebutuhan persiapan rapat');

        $this->actingAs($employee);
        $input = ['date' => '2026-10-08', 'start_time' => '13:00', 'end_time' => '14:00', 'agenda' => 'Rapat', 'notes' => '',
            'meeting_preparation' => true, 'participant_count' => 10, 'consumption_snack' => false, 'consumption_meal' => false];
        $token = (string) Str::uuid();
        $service = app(BookingService::class);
        $first = $service->submit($employee, $room->id, $input, $token);
        $this->assertSame($first->id, $service->submit($employee, $room->id, $input, $token)->id);
        $input['consumption_snack'] = true;
        try {
            $service->submit($employee, $room->id, $input, $token);
            $this->fail('Changed preparation must not reuse the token.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('agenda', $exception->errors());
        }
        $this->assertDatabaseCount('bookings', 1);
        $this->actingAs($admin)->put(route('admin.rooms.update', $room), [
            'name' => $room->name, 'code' => $room->code, 'floor_id' => $room->floor_id,
            'access_type' => 'all', 'requires_approval' => 1, 'allows_meeting_preparation' => 0,
            'status' => 'available', 'is_active' => 1, 'facility_ids' => [],
        ])->assertSessionHasNoErrors();
        $this->assertFalse($room->fresh()->allows_meeting_preparation);
        $this->actingAs($employee);
        Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->assertDontSee('Tambahkan kebutuhan persiapan rapat')
            ->set('date', '2026-10-09')->set('start_time', '13:00')->set('agenda', 'Rapat')
            ->set('meeting_preparation', true)->set('participant_count', 10)
            ->call('submitBooking')->assertHasErrors('meeting_preparation');

    }

    public function test_livewire_additional_dates_validate_and_submit_each_date(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $form = Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->set('date', '2026-10-09')->set('start_time', '13:00')->set('agenda', 'Rapat beberapa hari')
            ->call('addDate')->assertSet('additionalDates', [''])
            ->call('submitBooking')->assertHasErrors('additionalDates.0')
            ->set('additionalDates.0', '2026-10-09')->call('checkPlan')->assertHasErrors('additionalDates')
            ->set('additionalDates.0', '2026-10-07')->call('submitBooking')->assertHasErrors('start_time')
            ->set('additionalDates.0', '2026-10-10')->call('addDate')->set('additionalDates.1', '2026-10-11')
            ->call('removeDate', 1)->assertSet('additionalDates', ['2026-10-10'])
            ->call('checkPlan')->assertHasNoErrors()->call('submitBooking')->assertHasNoErrors();
        $this->assertSame(['2026-10-09', '2026-10-10'], Booking::orderBy('date')->pluck('date')->all());
        $this->assertSame(1, Booking::select('start_time')->distinct()->count('start_time'));
        $form->assertRedirect(route('my-bookings.show', Booking::orderBy('date')->first()->id));
    }

    public function test_multi_date_submission_is_atomic_and_idempotent(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $service = app(BookingService::class);
        $input = ['date' => '2026-10-09', 'start_time' => '13:00', 'end_time' => '14:00', 'agenda' => 'Seri rapat'];
        $token = (string) Str::uuid();
        $first = $service->submitMany(auth()->user(), $room->id, $input, $token, ['2026-10-10', '2026-10-11']);
        $this->assertSame($first->id, $service->submitMany(auth()->user(), $room->id, $input, $token, ['2026-10-10', '2026-10-11'])->id);
        $this->assertDatabaseCount('bookings', 3);
        try {
            $service->submitMany(auth()->user(), $room->id, array_replace($input, ['date' => '2026-10-12']), (string) Str::uuid(), ['2026-10-10']);
            $this->fail('A conflict must roll back all dates.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('2026-10-10', $exception->errors()['start_time'][0]);
        }
        $this->assertDatabaseCount('bookings', 3);
        try {
            $service->submitMany(auth()->user(), $room->id, $input, $token, ['2026-10-13']);
            $this->fail('Changed series must not reuse the token.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('agenda', $exception->errors());
        }
    }

    public function test_pic_and_admin_can_approve_a_subset_or_no_consumption(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $employee = auth()->user();
        foreach (['room_pic', 'super_admin'] as $index => $role) {
            $booking = app(BookingService::class)->submit($employee, $room->id, [
                'date' => '2026-10-'.(10 + $index), 'start_time' => '13:00', 'end_time' => '14:00', 'agenda' => 'Review',
                'meeting_preparation' => true, 'participant_count' => 10, 'consumption_snack' => true, 'consumption_meal' => true,
            ], (string) Str::uuid());
            $reviewer = User::where('role', $role)->firstOrFail();
            $prefix = $role === 'room_pic' ? 'pic.approvals' : 'admin.approvals';
            $this->actingAs($reviewer)->get(route($prefix.'.show', $booking))->assertOk()->assertSee('Konsumsi yang disetujui');
            $this->post(route($prefix.'.decide', $booking), ['decision' => 'approved', 'approved_consumption_snack' => $index === 0 ? '1' : '0'])->assertSessionHasNoErrors();
            $booking->refresh();
            $this->assertTrue($booking->consumption_meal);
            $this->assertSame($index === 0, $booking->approved_consumption_snack);
            $this->assertFalse($booking->approved_consumption_meal);
        }
    }

    public function test_reviewer_cannot_approve_unrequested_consumption(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $booking = app(BookingService::class)->submit(auth()->user(), $room->id, [
            'date' => '2026-10-10', 'start_time' => '13:00', 'end_time' => '14:00', 'agenda' => 'Review',
            'meeting_preparation' => true, 'participant_count' => 10, 'consumption_snack' => true,
        ], (string) Str::uuid());
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail())
            ->post(route('admin.approvals.decide', $booking), ['decision' => 'approved', 'approved_consumption_meal' => '1'])
            ->assertSessionHasErrors('approved_consumption_meal');
        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_room_seeder_requires_approval_without_assigning_unrelated_pics(): void
    {
        $this->seed(BookingRoomsSeeder::class);
        $rooms = Room::where('code', 'like', 'SPMT-%')->get();
        $this->assertCount(10, $rooms);
        $this->assertTrue($rooms->every(fn ($room) => $room->requires_approval));
        $this->assertCount(3, $rooms->where('allows_meeting_preparation', true));
        $ordinary = $rooms->firstWhere('code', 'SPMT-L2-01');
        $this->assertCount(0, $ordinary->pics);
        $ordinary->update(['requires_approval' => false, 'allows_meeting_preparation' => true]);
        $this->seed(BookingRoomsSeeder::class);
        $this->assertTrue($ordinary->fresh()->requires_approval);
        $this->assertTrue($ordinary->fresh()->allows_meeting_preparation);
        $this->expectException(ValidationException::class);
        app(BookingService::class)->assertPicAvailable($ordinary->fresh());
    }
}
