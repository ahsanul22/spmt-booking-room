<?php

namespace Tests\Feature;

use App\Livewire\BookingPreparationForm;
use App\Livewire\RoomCatalog;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\PostgresTestCase;

class BookingPreparationTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-09-27 10:00:00', 'Asia/Jakarta'));
        $this->actingAs(User::where('role', 'user')->firstOrFail());
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function room(): Room
    {
        return Room::where('is_active', true)->where('status', 'available')->where('access_type', 'all')->firstOrFail();
    }

    private function form()
    {
        return Livewire::test(BookingPreparationForm::class, ['roomId' => $this->room()->id])
            ->set('date', '2026-09-27')->set('start_time', '12:00')
            ->set('agenda', 'Koordinasi tim');
    }

    public function test_duration_picker_updates_end_time_and_preserves_custom_time(): void
    {
        Livewire::test(BookingPreparationForm::class, ['roomId' => $this->room()->id])
            ->assertViewHas('startOptions', fn ($times) => in_array('08:00', $times) && in_array('16:45', $times) && ! in_array('18:00', $times))
            ->set('start_time', '13:15')->assertSet('end_time', '14:15')
            ->set('duration', '30')->assertSet('end_time', '13:45')
            ->set('duration', '120')->assertSet('end_time', '15:15')
            ->set('duration', 'custom')->set('end_time', '16:45')
            ->set('start_time', '14:00')->assertSet('end_time', '16:45')
            ->set('agenda', 'Rapat custom')->call('checkPlan')->assertHasNoErrors()
            ->set('duration', '60')->assertSet('end_time', '15:00')->assertSet('checked', false);
    }

    public function test_outside_work_hours_remain_bookable_and_cross_midnight_is_rejected(): void
    {
        $form = $this->form()->set('outsideWorkHours', true)
            ->assertViewHas('startOptions', fn ($times) => in_array('07:00', $times) && in_array('18:00', $times))
            ->set('start_time', '23:30')->assertSet('end_time', '')
            ->call('checkPlan')->assertHasErrors('end_time')
            ->set('start_time', '16:30')->assertSet('end_time', '17:30')
            ->assertSee('Waktu pilihan berada di luar jam kerja')
            ->call('checkPlan')->assertHasNoErrors()
            ->set('start_time', '18:00')->assertSet('end_time', '19:00')
            ->set('outsideWorkHours', false)->assertSet('start_time', '18:00')
            ->assertViewHas('startOptions', fn ($times) => in_array('18:00', $times));
        $form->call('submitBooking')->assertHasNoErrors();
        $this->assertDatabaseHas('bookings', ['room_id' => $this->room()->id, 'start_time' => '18:00:00', 'end_time' => '19:00:00']);
    }

    public function test_catalog_selection_opens_the_selected_room_without_dropdown(): void
    {
        $room = $this->room();
        $url = route('rooms.book', $room);
        $this->get('/rooms')->assertOk()->assertSee($url, false);
        $this->get($url)->assertOk()->assertSee($room->name)->assertSee('Ruangan pilihan')
            ->assertDontSee('<select id="room_id"', false)->assertDontSee('participant_count')->assertSee('WIB');
        $this->get('/my-bookings/create')->assertRedirect(route('rooms.index'));
        $this->get(route('my-bookings.create', ['room_id' => $room->id]))->assertRedirect($url);
        $this->get(route('rooms.book', $room->id))->assertOk();
        foreach (['bad', '99999999999999999999999999999', '-1', '9999999'] as $id) {
            $this->get('/my-bookings/create?room_id='.$id)->assertNotFound();
        }
        $this->get('/my-bookings/create?room_id[]=1')->assertNotFound();
        $this->get('/rooms/99999999999999999999999999999-room/book')->assertNotFound();
    }

    public function test_automatic_end_is_a_summary_and_manual_end_accepts_any_minute(): void
    {
        $form = Livewire::test(BookingPreparationForm::class, ['roomId' => $this->room()->id])
            ->assertSee('Jam selesai otomatis (WIB)')->assertDontSee('name="end_time"', false)
            ->set('start_time', '13:00')->assertSet('end_time', '14:00')
            ->set('duration', 'custom')->assertSee('name="end_time"', false)
            ->assertSee('step="60"', false)->assertDontSee('readonly', false)
            ->set('end_time', '14:07')->set('agenda', 'Rapat menit khusus');
        $form->call('checkPlan')->assertHasNoErrors()->call('submitBooking')->assertHasNoErrors();
        $this->assertDatabaseHas('bookings', ['room_id' => $this->room()->id, 'end_time' => '14:07:00']);
    }

    public function test_invalid_duration_is_rejected_by_both_actions(): void
    {
        $form = $this->form()->set('duration', '999');
        $form->call('checkPlan')->assertHasErrors('duration')->assertSet('checked', false);
        $form->call('submitBooking')->assertHasErrors('duration');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_precheck_and_submit_reject_a_room_without_an_active_pic(): void
    {
        $room = $this->room();
        $room->update(['requires_approval' => true]);
        $room->pics()->detach();
        $form = $this->form();
        $form->call('checkPlan')->assertHasErrors('room')->assertSet('checked', false)
            ->assertSee('PIC aktif belum ditetapkan');
        $form->call('submitBooking')->assertHasErrors('room');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_date_change_clears_success_and_rechecks_the_selected_day(): void
    {
        $this->form()->call('checkPlan')->assertSet('checked', true)
            ->set('date', '2026-09-26')->assertSet('checked', false)
            ->assertDontSee('Waktu dan isian lolos pemeriksaan saat ini.')
            ->call('checkPlan')->assertHasErrors('start_time');
    }

    public function test_dashboard_is_a_small_catalog_preview_and_my_bookings_is_not_a_catalog(): void
    {
        Livewire::test(RoomCatalog::class, ['summary' => true])
            ->assertViewHas('rooms', fn ($rooms) => $rooms->count() <= 3)
            ->assertSee('Lihat semua ruangan')->assertDontSee('aria-label="Filter ruangan"', false);
        $this->get('/my-bookings')->assertOk()->assertSee('Belum ada booking.')
            ->assertDontSeeLivewire(RoomCatalog::class)->assertSee(route('rooms.index'), false);
        $response = $this->get('/rooms')->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'aria-current="page"'));
        $this->assertStringNotContainsString('Daftar Ruangan', $response->getContent());
    }

    public function test_exact_two_hours_is_valid_but_does_not_claim_a_reservation(): void
    {
        $this->form()->call('checkPlan')->assertHasNoErrors()->assertSet('checked', true)
            ->assertSee('Ruangan belum dipesan.')->assertSee('ketersediaan akan diperiksa ulang');
    }

    public function test_less_than_two_hours_and_past_dates_are_rejected(): void
    {
        $this->form()->set('start_time', '11:59')->call('checkPlan')->assertHasErrors('start_time');
        $this->form()->set('date', '2026-09-26')->call('checkPlan')->assertHasErrors('start_time');
        $this->travelTo(CarbonImmutable::parse('2026-09-27 10:00:30', 'Asia/Jakarta'));
        $this->form()->call('checkPlan')->assertHasErrors('start_time');
        $this->form()->set('start_time', '12:01')->call('checkPlan')->assertHasNoErrors();
    }

    public function test_time_is_rechecked_after_the_form_has_been_left_open(): void
    {
        $form = $this->form()->call('checkPlan')->assertSet('checked', true);
        $this->travel(1)->minutes();
        $form->call('checkPlan')->assertHasErrors('start_time')->assertSet('checked', false);
    }

    public function test_end_must_follow_start_on_same_day_and_invalid_dates_are_rejected(): void
    {
        foreach (['12:00', '11:00', '00:30'] as $end) {
            $this->form()->set('end_time', $end)->call('checkPlan')->assertHasErrors('end_time');
        }
        $this->form()->set('date', '2026-02-30')->call('checkPlan')->assertHasErrors('date');
        $this->form()->set('start_time', '25:00')->call('checkPlan')->assertHasErrors('start_time');
        $this->form()->set('agenda', '   ')->call('checkPlan')->assertHasErrors('agenda');
        $this->form()->set('notes', str_repeat('x', 2001))->call('checkPlan')->assertHasErrors('notes');
    }

    public function test_two_hour_window_can_cross_midnight_in_wib(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-27 23:30:00', 'Asia/Jakarta'));
        $this->form()->set('date', '2026-09-28')->set('start_time', '01:30')->set('end_time', '02:30')
            ->call('checkPlan')->assertHasNoErrors();
        $this->form()->set('date', '2026-09-28')->set('start_time', '01:29')->set('end_time', '02:30')
            ->call('checkPlan')->assertHasErrors('start_time');
    }

    public function test_changed_room_state_is_checked_again_on_the_server(): void
    {
        foreach ([['status' => 'maintenance'], ['is_active' => false], ['access_type' => 'restricted']] as $change) {
            $room = $this->room();
            $form = $this->form();
            $room->update($change);
            $form->call('checkPlan')->assertHasErrors('room')->assertSet('checked', false);
            $room->update(['status' => 'available', 'is_active' => true, 'access_type' => 'all']);
        }
    }

    public function test_explicit_unit_access_is_required_and_revocation_is_rechecked(): void
    {
        $room = $this->room();
        $form = $this->form();
        $room->update(['access_type' => 'restricted']);
        $room->allowedOrganizationalUnits()->sync([auth()->user()->organizational_unit_id]);
        $form->call('checkPlan')->assertHasNoErrors();
        $room->allowedOrganizationalUnits()->detach();
        $form->call('checkPlan')->assertHasErrors('room')->assertSet('checked', false);
    }

    public function test_room_identity_cannot_be_changed_in_livewire_state(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $this->form()->set('roomId', $this->room()->id + 1);
    }

    public function test_form_enforces_role_authorization_and_has_no_write_route(): void
    {
        $room = $this->room();
        $this->post('/my-bookings/create', ['room_id' => $room->id])->assertStatus(405);
        $this->post(route('rooms.book', $room))->assertStatus(405);
        $this->post(route('logout'));
        $this->get(route('rooms.book', $room))->assertRedirect(route('login'));
        $this->actingAs(User::where('role', 'room_pic')->firstOrFail())
            ->get(route('rooms.book', $room))->assertOk();
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $this->get(route('my-bookings.create', ['room_id' => $room->id]))->assertForbidden();
        $this->get(route('rooms.book', $room))->assertForbidden();
        Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])->assertForbidden();
    }
}
