<?php

namespace Tests\Feature;

use App\Livewire\BookingPreparationForm;
use App\Livewire\RoomSchedule;
use App\Livewire\PublicRoomSchedule;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\PostgresTestCase;

class HomeAndScheduleTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-09-27 10:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function room(): Room
    {
        return Room::where('code', 'DEMO-SM')->firstOrFail();
    }

    private function employee(): User
    {
        return User::where('role', 'user')->firstOrFail();
    }

    private function booking(array $changes = []): Booking
    {
        return Booking::create(array_replace([
            'room_id' => $this->room()->id, 'user_id' => $this->employee()->id,
            'date' => '2026-09-27', 'start_time' => '12:00', 'end_time' => '13:00',
            'agenda' => 'RAHASIA-AGENDA', 'notes' => 'RAHASIA-CATATAN', 'unit_name' => 'RAHASIA-UNIT',
            'status' => 'approved', 'requires_approval' => false,
            'submission_token' => (string) Str::uuid(), 'request_hash' => str_repeat('a', 64),
        ], $changes));
    }

    public function test_home_shows_public_schedule_and_login_without_private_meeting_fields(): void
    {
        $booking = $this->booking();
        $this->room()->update(['description' => 'DESKRIPSI-INTERNAL']);
        $this->get('/')->assertOk()->assertSeeLivewire(PublicRoomSchedule::class)->assertSee($this->room()->name)
            ->assertSee('12:00')->assertSee('13:00')->assertSee(route('login'), false)
            ->assertDontSee('Kenali pilihan ruangan')
            ->assertDontSee('RAHASIA-AGENDA')->assertDontSee('RAHASIA-CATATAN')->assertDontSee('RAHASIA-UNIT')
            ->assertDontSee($booking->submission_token)->assertDontSee('DESKRIPSI-INTERNAL')
            ->assertDontSee('Ruang Demo Terbatas')->assertDontSee($this->employee()->email)
            ->assertDontSee('wire:submit', false);
        $this->get('/schedule')->assertRedirect('/login');
        $this->get('/admin/schedule')->assertRedirect('/login');
        Livewire::test(RoomSchedule::class)->assertForbidden();
    }

    public function test_public_home_handles_empty_schedule_and_escapes_names(): void
    {
        $this->room()->update(['name' => '<script>room-name</script>']);
        $this->get('/')->assertSee('<script>room-name</script>')->assertDontSee('<script>room-name</script>', false);
        Room::query()->update(['is_active' => false]);
        $this->get('/')->assertOk()->assertSee('Belum ada jadwal ruangan.')->assertSee('Login Pegawai');
    }

    public function test_public_schedule_scopes_filters_and_refresh_to_public_active_rooms(): void
    {
        $this->booking();
        $restricted = Room::where('access_type', 'restricted')->firstOrFail();
        $this->booking(['room_id' => $restricted->id]);
        $component = Livewire::test(PublicRoomSchedule::class)
            ->assertDontSee($restricted->name)
            ->assertViewHas('daySlots', fn ($slots) => $slots->count() === 1)
            ->call('selectRoom', (string) $this->room()->id)
            ->call('selectDate', '2026-09-28')->assertSee('Belum ada jadwal ruangan.')
            ->call('today')->assertSee('12:00')
            ->call('changeMonth', 1)->assertSet('month', '2026-10-01')
            ->call('today');
        $this->room()->update(['access_type' => 'restricted']);
        $component->call('$refresh')->assertViewHas('daySlots', fn ($slots) => $slots->isEmpty())
            ->assertDontSee($this->room()->name);
        Livewire::test(PublicRoomSchedule::class)->call('selectRoom', (string) $restricted->id)->assertStatus(422);
        $this->get('/?room_id='.$restricted->id)->assertOk()->assertDontSee($restricted->name);
        $this->room()->update(['access_type' => 'all', 'is_active' => false]);
        Livewire::test(PublicRoomSchedule::class)->assertViewHas('daySlots', fn ($slots) => $slots->isEmpty());
    }

    public function test_selected_public_room_is_preserved_through_failed_then_successful_login(): void
    {
        $this->get(route('login', ['room' => $this->room()->id]))->assertOk();
        $this->post('/login', ['email' => $this->employee()->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->get('/login')->assertOk();
        $this->post('/login', ['email' => $this->employee()->email, 'password' => 'password'])
            ->assertRedirect(route('my-bookings.create', ['room_id' => $this->room()->id]))->assertSessionMissing('booking.login_room');
    }

    public function test_invalid_room_parameter_cannot_create_an_external_login_redirect(): void
    {
        foreach (['https://example.com', '9999999999999999999999999', 'bad'] as $room) {
            $this->get(route('login', ['room' => $room]))->assertOk()->assertSessionMissing('booking.login_room');
        }
        $this->get('/login?room[]=1')->assertOk()->assertSessionMissing('booking.login_room');
        $this->post('/login', ['email' => $this->employee()->email, 'password' => 'password'])->assertRedirect('/dashboard');
    }

    public function test_admin_does_not_get_employee_booking_redirect_and_home_respects_roles(): void
    {
        $this->get(route('login', ['room' => $this->room()->id]));
        $this->post('/login', ['email' => 'admin@example.test', 'password' => 'password'])->assertRedirect('/admin/dashboard');
        foreach (['user', 'room_pic', 'super_admin'] as $role) {
            $user = User::where('role', $role)->firstOrFail();
            $this->actingAs($user)->get('/')->assertRedirect(route($user->dashboardRouteName()));
        }
    }

    public function test_room_deactivated_during_login_returns_to_catalog(): void
    {
        $this->get(route('login', ['room' => $this->room()->id]));
        $this->room()->update(['is_active' => false]);
        $this->post('/login', ['email' => $this->employee()->email, 'password' => 'password'])->assertRedirect('/rooms');
    }

    public function test_schedule_reads_bookings_but_never_exposes_private_fields(): void
    {
        $booking = $this->booking();
        $this->actingAs($this->employee());
        $this->get('/schedule')->assertOk()->assertSeeLivewire(RoomSchedule::class)
            ->assertSee('12:00')->assertSee('13:00')->assertSee($this->room()->name)
            ->assertDontSee('RAHASIA-AGENDA')->assertDontSee('RAHASIA-CATATAN')->assertDontSee('RAHASIA-UNIT')
            ->assertDontSee($booking->submission_token)->assertDontSee('Pratinjau desain');
        $this->get('/admin/schedule')->assertForbidden();
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail())->get('/admin/schedule')->assertOk()->assertSee('12:00');
    }

    public function test_occupancy_states_follow_wib_and_do_not_mark_pending_as_in_use(): void
    {
        $this->booking(['start_time' => '09:00', 'end_time' => '10:00']);
        $this->booking(['start_time' => '10:00', 'end_time' => '11:00']);
        $this->booking(['start_time' => '09:00', 'end_time' => '11:00', 'status' => 'pending']);
        $this->booking(['start_time' => '12:00', 'end_time' => '13:00']);
        $this->booking(['status' => 'rejected']);
        $this->booking(['status' => 'cancelled']);
        $slots = app(BookingSchedule::class)->slots('2026-09-27', '2026-09-27');
        $this->assertCount(4, $slots);
        $this->assertSame(['elapsed' => 1, 'ongoing' => 1, 'pending' => 1, 'scheduled' => 1], $slots->countBy('state')->sortKeys()->all());
    }

    public function test_calendar_counts_and_filters_use_the_same_booking_data(): void
    {
        $room = $this->room();
        $other = Room::where('id', '!=', $room->id)->firstOrFail();
        $this->booking();
        $this->booking(['room_id' => $other->id, 'status' => 'pending']);
        Livewire::actingAs($this->employee())->test(RoomSchedule::class)
            ->assertViewHas('days', fn ($days) => $days->firstWhere('date', '2026-09-27')['total'] === 2)
            ->call('selectRoom', (string) $room->id)->assertViewHas('daySlots', fn ($slots) => $slots->count() === 1 && $slots->first()['room_id'] === $room->id)
            ->call('selectRoom', '')->assertViewHas('daySlots', fn ($slots) => $slots->count() === 2)
            ->call('selectDate', '2026-09-28')->assertSee('Belum ada jadwal ruangan.');
    }

    public function test_calendar_navigation_handles_year_boundary_and_leap_day(): void
    {
        Livewire::actingAs($this->employee())->test(RoomSchedule::class)
            ->call('selectDate', '2026-12-31')->call('changeMonth', 1)->assertSet('month', '2027-01-01')
            ->call('changeMonth', -1)->assertSet('month', '2026-12-01')
            ->call('selectDate', '2028-02-29')->assertSet('selectedDate', '2028-02-29')
            ->assertViewHas('days', fn ($days) => $days->contains('date', '2028-02-29'))
            ->call('selectDate', '2026-02-30')->assertHasErrors('date')->assertSet('selectedDate', '2028-02-29')
            ->call('today')->assertSet('selectedDate', '2026-09-27');
    }

    public function test_room_calendar_query_parameters_are_validated(): void
    {
        $this->actingAs($this->employee());
        $this->get('/schedule?date[]=bad&room_id[]=bad')->assertOk();
        $this->get('/schedule?date=2026-02-30&room_id=999999999999999999999999')->assertOk();
        $this->get(route('schedule.index', ['date' => '2026-09-28', 'room_id' => $this->room()->id]))
            ->assertOk()->assertSee('2026-09-28', false);
        Livewire::test(RoomSchedule::class)->call('selectRoom', '999999999999999999999999')->assertStatus(422);
    }

    public function test_schedule_refresh_reflects_cancellation_and_room_state_changes(): void
    {
        $booking = $this->booking();
        $component = Livewire::actingAs($this->employee())->test(RoomSchedule::class)->assertViewHas('daySlots', fn ($slots) => $slots->count() === 1);
        $this->room()->update(['is_active' => false]);
        $component->call('$refresh')->assertSee('Ruangan saat ini tidak operasional');
        $booking->update(['status' => 'cancelled']);
        $component->call('$refresh')->assertViewHas('daySlots', fn ($slots) => $slots->isEmpty());
    }

    public function test_calendar_rechecks_authorization_on_refresh(): void
    {
        $user = $this->employee();
        $component = Livewire::actingAs($user)->test(RoomSchedule::class);
        $user->is_active = false;
        $user->save();
        $component->call('$refresh')->assertForbidden();
    }

    public function test_refresh_updates_ongoing_label_at_exact_start_and_end(): void
    {
        $this->booking(['start_time' => '10:01', 'end_time' => '11:00']);
        $component = Livewire::actingAs($this->employee())->test(RoomSchedule::class)
            ->assertViewHas('daySlots', fn ($slots) => $slots->first()['state'] === 'scheduled');
        $this->travel(1)->minutes();
        $component->call('$refresh')->assertViewHas('daySlots', fn ($slots) => $slots->first()['state'] === 'ongoing');
        $this->travel(59)->minutes();
        $component->call('$refresh')->assertViewHas('daySlots', fn ($slots) => $slots->first()['state'] === 'elapsed');
    }

    public function test_booking_form_shows_selected_rooms_times_above_fields_and_changes_with_date(): void
    {
        $room = $this->room();
        $this->booking();
        $this->booking(['date' => '2026-09-28', 'start_time' => '15:00', 'end_time' => '16:00']);
        $this->booking(['room_id' => Room::where('id', '!=', $room->id)->firstOrFail()->id, 'start_time' => '17:00', 'end_time' => '18:00']);
        $component = Livewire::actingAs($this->employee())->test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->assertSeeInOrder(['Jadwal Ruangan Ini', '12:00', 'Detail Pertemuan'])
            ->assertDontSee('17:00')->assertDontSee('RAHASIA-AGENDA')->assertDontSee('RAHASIA-CATATAN')
            ->set('date', '2026-09-28')->assertSee('15:00')
            ->assertViewHas('daySlots', fn ($slots) => $slots->count() === 1 && $slots->first()['start'] === '15:00')
            ->set('date', '2026-02-30')->assertSee('Pilih tanggal yang valid');
    }
}
