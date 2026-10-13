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
            ->set('participant_count', 25)->set('needs_consumption', true)
            ->call('checkPlan')->assertHasNoErrors()->call('submitBooking')->assertHasNoErrors();
        $booking = Booking::sole();
        $this->assertSame('pending', $booking->status);
        $this->assertSame(25, $booking->participant_count);
        $this->assertTrue($booking->needs_consumption);
        $form->assertRedirect(route('my-bookings.show', $booking->id));
        $this->get(route('my-bookings.show', $booking->id))->assertOk()->assertSee('25 orang')->assertSee('Diperlukan');
        $this->actingAs(User::where('role', 'room_pic')->firstOrFail())
            ->get(route('pic.approvals.index'))->assertOk()->assertSee('25 orang')->assertSee('Diperlukan');
    }

    public function test_disabled_room_rejects_preparation_and_unchecking_discards_details(): void
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $form = Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->set('date', '2026-10-08')->set('start_time', '13:00')->set('agenda', 'Rapat')
            ->set('meeting_preparation', true)->set('participant_count', 10)->set('needs_consumption', true);
        $room->update(['allows_meeting_preparation' => false]);
        $form->call('submitBooking')->assertHasErrors('meeting_preparation');
        $this->assertDatabaseCount('bookings', 0);
        $form->set('meeting_preparation', false)->assertSet('participant_count', null)->assertSet('needs_consumption', false)
            ->call('submitBooking')->assertHasNoErrors();
        $booking = Booking::sole();
        $this->assertNull($booking->participant_count);
        $this->assertFalse($booking->needs_consumption);
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
        $this->actingAs($employee);
        $input = ['date' => '2026-10-08', 'start_time' => '13:00', 'end_time' => '14:00', 'agenda' => 'Rapat', 'notes' => '',
            'meeting_preparation' => true, 'participant_count' => 10, 'needs_consumption' => false];
        $token = (string) Str::uuid();
        $service = app(BookingService::class);
        $first = $service->submit($employee, $room->id, $input, $token);
        $this->assertSame($first->id, $service->submit($employee, $room->id, $input, $token)->id);
        $input['needs_consumption'] = true;
        try {
            $service->submit($employee, $room->id, $input, $token);
            $this->fail('Changed preparation must not reuse the token.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('agenda', $exception->errors());
        }
        $this->assertDatabaseCount('bookings', 1);
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
