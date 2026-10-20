<?php

namespace Tests\Feature;

use App\Livewire\BookingPreparationForm;
use App\Models\Branch;
use App\Models\GuestRegistration;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\PostgresTestCase;

class GuestRegistrationTest extends PostgresTestCase
{
    public function test_form_has_no_office_selector_and_uses_server_configured_office(): void
    {
        $branch = Branch::create(['name' => 'Lokasi Resepsionis']);
        config(['guests.branch_id' => $branch->id]);
        $this->get('/tamu')->assertOk()->assertSee('Tujuan kunjungan')->assertSee('NIP (opsional)')
            ->assertDontSee('name="branch_id"', false);
        $this->post('/tamu', ['name' => 'Tamu tanpa NIP', 'organization' => 'Instansi', 'contact' => '0812345',
            'purpose' => 'Koordinasi', 'branch_id' => Branch::firstOrFail()->id])->assertRedirect('/tamu/jadwal');
        $guest = GuestRegistration::sole();
        $this->assertSame($branch->id, $guest->branch_id);
        $this->assertNull($guest->nip);
        $this->assertSame('Koordinasi', $guest->purpose);
    }

    private function receptionist(): User
    {
        $user = User::where('role', 'user')->firstOrFail();
        $user->forceFill(['is_receptionist' => true])->save();

        return $user;
    }

    private function registerGuest(): GuestRegistration
    {
        $this->post('/tamu', ['name' => 'Tamu Uji', 'organization' => 'Instansi Uji', 'contact' => '08123456789', 'nip' => '0012345678', 'purpose' => 'Pertemuan koordinasi'])->assertRedirect('/tamu/jadwal');

        return GuestRegistration::latest('id')->firstOrFail();
    }

    public function test_registration_gates_schedule_and_expires_without_employee_access(): void
    {
        $this->get('/tamu/jadwal')->assertRedirect('/tamu');
        $this->post('/tamu', [])->assertSessionHasErrors(['name', 'organization', 'contact', 'purpose']);
        $guest = $this->registerGuest();
        $this->assertEqualsWithDelta(now()->addDays(3)->timestamp, $guest->expires_at->timestamp, 5);
        $this->assertSame('0012345678', $guest->nip);
        $this->assertSame('Pertemuan koordinasi', $guest->purpose);
        $this->get('/tamu/jadwal')->assertOk()->assertDontSee($guest->reference)->assertDontSee('08123456789');
        $this->get('/tamu/jadwal?date=invalid')->assertSessionHasErrors('date');
        $this->get('/my-bookings')->assertRedirect('/login');
        $this->travel(4)->days();
        $this->get('/tamu/jadwal')->assertRedirect('/tamu');
        $this->artisan('guests:prune')->assertSuccessful();
        $this->assertDatabaseMissing('guest_registrations', ['id' => $guest->id]);
    }

    public function test_guest_snapshot_survives_pruning_and_is_in_report_and_csv(): void
    {
        $guest = $this->registerGuest();
        $user = $this->receptionist();
        $this->actingAs($user);
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $input = ['date' => now(config('booking.timezone'))->addDay()->toDateString(), 'start_time' => '12:00', 'end_time' => '13:00', 'agenda' => 'Agenda rahasia', 'guest_reference' => $guest->reference];
        $token = (string) Str::uuid();
        $booking = app(BookingService::class)->submit($user, $room->id, $input, $token);
        $this->assertSame('pending', $booking->status);
        $this->assertSame($user->id, $booking->user_id);
        $this->assertSame($guest->name, $booking->guest_name);
        $this->get('/tamu/jadwal?date='.$input['date'])->assertOk()->assertSee('12:00')->assertDontSee('Agenda rahasia')->assertDontSee($user->name);
        $this->get('/my-bookings/'.$booking->id)->assertOk()->assertSee('Tamu Uji')->assertSee($user->name);
        $this->travel(4)->days();
        $this->artisan('guests:prune')->assertSuccessful();
        $this->assertSame($booking->id, app(BookingService::class)->submit($user, $room->id, $input, $token)->id);
        $this->assertSame('Tamu Uji', $booking->fresh()->guest_name);
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $month = substr($input['date'], 0, 7);
        $this->get('/admin/reports/monthly?month='.$month)->assertOk()->assertSee('Tamu Uji')->assertSee($user->name);
        $this->get('/admin/reports/monthly/print?month='.$month)->assertOk()->assertSee('Tamu Uji');
        $csv = $this->get('/admin/reports/monthly/export?month='.$month)->assertOk()->streamedContent();
        $this->assertStringContainsString('Tamu Uji', $csv);
        $this->assertStringContainsString($user->name, $csv);
    }

    public function test_guest_reference_is_revalidated_on_submit_and_wrong_branch_is_rejected(): void
    {
        $guest = $this->registerGuest();
        $this->actingAs($this->receptionist());
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $guest->update(['branch_id' => Branch::create(['name' => 'Kantor Lain'])->id]);
        Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->set('date', now(config('booking.timezone'))->addDay()->toDateString())
            ->set('start_time', '12:00')->set('agenda', 'Pengajuan tamu')->set('guest_reference', $guest->reference)
            ->call('submitBooking')->assertHasErrors('guest_reference');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_end_of_day_expiry_and_expired_guest_submission(): void
    {
        config(['guests.expiry_mode' => 'end_of_day']);
        $this->travelTo(CarbonImmutable::parse('2026-10-20 16:00', 'Asia/Jakarta'));
        $guest = $this->registerGuest();
        $this->assertSame('2026-10-20 17:00', $guest->expires_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i'));
        $this->travelTo(CarbonImmutable::parse('2026-10-20 17:00', 'Asia/Jakarta'));
        $this->get('/tamu/jadwal')->assertRedirect('/tamu');
        $this->actingAs($this->receptionist());
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->set('date', '2026-10-21')->set('start_time', '12:00')->set('agenda', 'Tamu kedaluwarsa')
            ->set('guest_reference', $guest->reference)->call('submitBooking')->assertHasErrors('guest_reference');
        $this->assertDatabaseCount('bookings', 0);
        $next = $this->registerGuest();
        $this->assertSame('2026-10-21 17:00', $next->expires_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i'));
        $this->travelBack();
    }

    public function test_guest_schedule_excludes_restricted_rooms_and_other_branches(): void
    {
        $this->registerGuest();
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $room->update(['access_type' => 'restricted']);
        $this->get('/tamu/jadwal')->assertOk()->assertDontSee($room->name);
        $room->update(['access_type' => 'all', 'branch_id' => Branch::create(['name' => 'Cabang Lain'])->id]);
        $this->get('/tamu/jadwal')->assertOk()->assertDontSee($room->name);
    }
}
