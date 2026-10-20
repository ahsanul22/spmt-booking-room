<?php

namespace Tests\Feature;

use App\Livewire\BookingPreparationForm;
use App\Models\Branch;
use App\Models\GuestRegistration;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\PostgresTestCase;

class ReceptionistPermissionTest extends PostgresTestCase
{
    private function guest(Room $room, string $name): GuestRegistration
    {
        return GuestRegistration::create(['name' => $name, 'organization' => 'Instansi', 'contact' => '0812345',
            'branch_id' => $room->branch_id, 'reference' => strtoupper(Str::random(12)), 'expires_at' => now()->addDay()]);
    }

    public function test_admin_can_assign_remove_and_preserve_permission_with_one_checkbox(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $user = User::where('role', 'user')->firstOrFail();
        $data = ['name' => $user->name, 'email' => $user->email, 'role' => 'user', 'is_active' => 1,
            'receptionist_selection_present' => 1, 'is_receptionist' => 1];
        $this->actingAs($admin)->get('/admin/users/'.$user->id.'/edit')->assertOk()->assertSee('Petugas resepsionis');
        $this->put('/admin/users/'.$user->id, $data)->assertSessionHasNoErrors();
        $this->assertTrue($user->fresh()->canHandleGuests());
        $this->get('/admin/users')->assertOk()->assertSee('Petugas resepsionis');
        $legacy = $data;
        unset($legacy['receptionist_selection_present'], $legacy['is_receptionist']);
        $this->put('/admin/users/'.$user->id, $legacy)->assertSessionHasNoErrors();
        $this->assertTrue($user->fresh()->is_receptionist);
        unset($data['is_receptionist']);
        $this->put('/admin/users/'.$user->id, $data)->assertSessionHasNoErrors();
        $this->assertFalse($user->fresh()->is_receptionist);
        $this->actingAs($user)->put('/admin/users/'.$user->id, $data + ['is_receptionist' => 1])->assertForbidden();
        $this->assertFalse($user->fresh()->is_receptionist);
        $this->actingAs($admin)->put('/admin/users/'.$user->id, array_replace($data, ['role' => 'super_admin', 'is_receptionist' => 1]))->assertSessionHasNoErrors();
        $this->assertFalse($user->fresh()->canHandleGuests());
        $this->assertFalse($user->fresh()->is_receptionist);
    }

    public function test_only_receptionists_see_active_guests_for_room_office_and_revocation_is_immediate(): void
    {
        $user = User::where('role', 'user')->firstOrFail();
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $guest = $this->guest($room, 'Tamu Aktif Pilihan');
        $expired = $this->guest($room, 'Tamu Kedaluwarsa Pilihan');
        $expired->update(['expires_at' => now()->subMinute()]);
        $other = $this->guest($room, 'Tamu Kantor Lain Pilihan');
        $other->update(['branch_id' => Branch::create(['name' => 'Kantor Lain'])->id]);
        $this->actingAs($user);
        Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->assertDontSee('Booking untuk')->assertDontSee($guest->name)->assertDontSee($guest->reference);
        $user->forceFill(['is_receptionist' => true])->save();
        $component = Livewire::test(BookingPreparationForm::class, ['roomId' => $room->id])
            ->assertSee('Booking untuk')->assertSee($guest->name)->assertDontSee($expired->name)->assertDontSee($other->name);
        $component->set('guest_reference', $guest->reference)->set('date', now(config('booking.timezone'))->addDay()->toDateString())
            ->set('start_time', '12:00')->set('agenda', 'Permintaan tamu');
        $user->forceFill(['is_receptionist' => false])->save();
        $component->call('submitBooking')->assertForbidden();
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_direct_guest_submission_is_forbidden_but_personal_booking_still_works(): void
    {
        $user = User::where('role', 'user')->firstOrFail();
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $room->update(['requires_approval' => false]);
        $guest = $this->guest($room, 'Tamu Tidak Boleh');
        $input = ['date' => now(config('booking.timezone'))->addDay()->toDateString(),
            'start_time' => '12:00', 'end_time' => '13:00', 'agenda' => 'Rapat'];
        try {
            app(BookingService::class)->submit($user, $room->id, $input + ['guest_reference' => $guest->reference], (string) Str::uuid());
            $this->fail('Guest submission must be forbidden.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $booking = app(BookingService::class)->submit($user, $room->id, $input, (string) Str::uuid());
        $this->assertNull($booking->guest_name);
        $this->assertSame('approved', $booking->status);
        $this->assertFalse((new User(['is_receptionist' => true]))->is_receptionist ?? false);
    }
}
