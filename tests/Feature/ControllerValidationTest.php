<?php

namespace Tests\Feature;

use App\Livewire\ListingFilters;
use App\Models\Branch;
use App\Models\GuestRegistration;
use App\Models\Room;
use App\Models\User;
use App\Models\WhatsappTemplate;
use App\Services\BookingService;
use App\Services\UserService;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\PostgresTestCase;

class ControllerValidationTest extends PostgresTestCase
{
    public function test_malformed_foreign_keys_return_validation_errors_instead_of_database_errors(): void
    {
        $this->actingAs(User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail());
        $room = Room::where('access_type', 'restricted')->firstOrFail();
        $template = WhatsappTemplate::firstOrFail();
        $cases = [
            ['post', route('admin.users.store'), 'organizational_unit_id', 'not-an-id'],
            ['post', route('admin.users.store'), 'branch_ids', ['not-an-id']],
            ['post', route('admin.rooms.store'), 'branch_id', '9223372036854775808'],
            ['post', route('admin.rooms.store'), 'floor_id', ['invalid']],
            ['post', route('admin.rooms.store'), 'facility_ids', [['nested']]],
            ['post', route('admin.organizational-units.store'), 'parent_id', 'not-an-id'],
            ['post', route('admin.floors.store'), 'floor_number', '9223372036854775808'],
            ['put', route('admin.rooms.pics.update', $room), 'pic_ids', ['not-an-id']],
            ['put', route('admin.rooms.access.update', $room), 'organizational_unit_ids', ['not-an-id']],
            ['put', route('admin.whatsapp.update', $template), 'recipient_ids', ['not-an-id']],
        ];
        foreach ($cases as [$method, $url, $field, $value]) {
            $key = in_array($field, ['branch_ids', 'facility_ids', 'pic_ids', 'organizational_unit_ids', 'recipient_ids'], true) ? $field.'.0' : $field;
            $this->json($method, $url, [$field => $value])->assertUnprocessable()->assertJsonValidationErrors($key);
        }
        $this->assertDatabaseMissing('rooms', ['name' => 'not-an-id']);
    }

    public function test_invalid_filters_and_extreme_route_ids_are_rejected_before_queries(): void
    {
        $this->actingAs(User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail());
        foreach (['/admin/users', '/admin/rooms', '/admin/bookings', '/admin/approvals', '/admin/branches', '/admin/floors', '/admin/facilities', '/admin/organizational-units', '/admin/whatsapp', '/admin/reports/monthly'] as $url) {
            foreach ([['bad'], 'not-a-page', '9223372036854775808', 0, -1] as $page) {
                $this->getJson($url.'?'.http_build_query(['page' => $page]))->assertUnprocessable()->assertJsonValidationErrors('page');
            }
        }
        foreach (['/admin/users', '/admin/rooms', '/admin/bookings', '/admin/approvals', '/admin/reports/monthly'] as $url) {
            $this->getJson($url.'?branch_id=not-an-id')->assertUnprocessable()->assertJsonValidationErrors('branch_id');
            $this->getJson($url.'?branch_id=9223372036854775808')->assertUnprocessable()->assertJsonValidationErrors('branch_id');
        }
        $huge = '9223372036854775808';
        foreach (['/admin/users/', '/admin/bookings/', '/admin/approvals/', '/pic/approvals/'] as $prefix) {
            $this->getJson($prefix.$huge)->assertNotFound();
        }
        $this->putJson('/admin/branches/'.$huge, ['name' => 'Invalid'])->assertNotFound();
        $this->putJson('/admin/whatsapp/'.$huge, [])->assertNotFound();
        $this->postJson('/admin/whatsapp/messages/'.$huge.'/retry')->assertNotFound();
        $this->getJson('/admin/users?branch_id='.Branch::firstOrFail()->id)->assertOk();
        Livewire::test(ListingFilters::class, ['destination' => 'admin.reports.index', 'kind' => 'report'])
            ->set('branch', 'not-an-id')->assertSee('Semua ruangan dalam cakupan Anda')->call('applyFilters')->assertHasErrors(['branch']);
    }

    public function test_status_password_and_approval_only_accept_valid_authorized_changes(): void
    {
        $admin = User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail();
        $employee = User::where('role', User::ROLE_USER)->firstOrFail();
        $pic = User::where('role', User::ROLE_ROOM_PIC)->firstOrFail();
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $booking = app(BookingService::class)->submit($employee, $room->id, [
            'date' => now(config('booking.timezone'))->addDay()->toDateString(),
            'start_time' => '12:00', 'end_time' => '13:00', 'agenda' => 'Audit controller',
        ], (string) Str::uuid());
        $this->actingAs($employee)->patchJson(route('admin.users.status', $employee), ['is_active' => false])->assertForbidden();
        $this->postJson(route('pic.approvals.decide', $booking->id), ['decision' => 'approved'])->assertForbidden();
        $this->actingAs($admin);
        $this->patchJson(route('admin.users.status', $employee), ['is_active' => ['false']])->assertUnprocessable()->assertJsonValidationErrors('is_active');
        $originalPassword = $employee->password;
        $this->patchJson(route('admin.users.reset-password', $employee), ['password' => 'short', 'password_confirmation' => 'different'])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertSame($originalPassword, $employee->fresh()->password);
        $this->postJson(route('admin.approvals.decide', $booking->id), ['decision' => 'approved', 'approved_it_support' => ['1']])->assertUnprocessable()->assertJsonValidationErrors('approved_it_support');
        $this->postJson(route('admin.approvals.decide', $booking->id), ['decision' => 'rejected', 'decision_notes' => '   '])->assertUnprocessable()->assertJsonValidationErrors('rejection_reason');
        $this->assertSame('pending', $booking->fresh()->status);
        $this->actingAs($pic)->postJson(route('pic.approvals.decide', $booking->id), ['decision' => 'approved', 'branch_id' => $room->branch_id])->assertForbidden();
        $this->assertSame('pending', $booking->fresh()->status);
        $this->actingAs($admin)->patchJson(route('admin.users.status', $employee), ['is_active' => true, 'role' => User::ROLE_SUPER_ADMIN])->assertRedirect();
        $this->assertSame(User::ROLE_USER, $employee->fresh()->role);
        $this->postJson(route('admin.approvals.decide', $booking->id), ['decision' => 'approved', 'approved_it_support' => false, 'return_to' => 'list', 'page' => 2])->assertRedirect(route('admin.approvals.index', ['page' => 1]));
        $this->assertSame('approved', $booking->fresh()->status);
    }

    public function test_guest_whitelist_and_locked_user_snapshot_preserve_server_owned_data(): void
    {
        $this->postJson('/tamu', ['name' => ['invalid'], 'organization' => 'Instansi', 'contact' => '0812345', 'purpose' => 'Audit'])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertSame(0, GuestRegistration::count());
        $this->postJson('/tamu', ['name' => 'Tamu', 'organization' => 'Instansi', 'contact' => '0812345', 'purpose' => 'Audit', 'reference' => 'ATTACKER', 'expires_at' => '2099-01-01'])->assertRedirect(route('guests.schedule'));
        $guest = GuestRegistration::sole();
        $this->assertNotSame('ATTACKER', $guest->reference);
        $this->assertTrue($guest->expires_at->lessThan(now()->addWeek()));
        $this->getJson('/tamu/jadwal?date=0000-01-01')->assertUnprocessable()->assertJsonValidationErrors('date');
        $user = User::where('role', User::ROLE_USER)->firstOrFail();
        User::whereKey($user->id)->update(['is_receptionist' => true]);
        $updated = app(UserService::class)->save($user, [
            'name' => $user->name, 'email' => $user->email, 'role' => $user->role,
            'is_active' => true, 'organizational_unit_id' => $user->organizational_unit_id,
        ]);
        $this->assertTrue($updated->is_receptionist);
        $this->assertTrue($updated->branches()->exists());
        $this->assertSame($user->password, $updated->password);
    }
}
