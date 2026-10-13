<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\PostgresTestCase;

class UniqueUserContactsTest extends PostgresTestCase
{
    private function data(array $changes = []): array
    {
        return array_replace(['name' => 'Kontak Baru', 'email' => 'contact@example.test', 'role' => 'user', 'is_active' => '1',
            'password' => 'New-password-123', 'password_confirmation' => 'New-password-123'], $changes);
    }

    public function test_email_is_unique_and_shared_whatsapp_is_allowed_on_create_update_and_login_accepts_email_case(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $this->actingAs($admin);
        $this->post(route('admin.users.store'), $this->data(['email' => ' CONTACT@EXAMPLE.TEST ', 'whatsapp_number' => '0812 3456-7890']))->assertSessionHasNoErrors();
        $record = User::where('email', 'contact@example.test')->sole();
        $this->assertSame('+6281234567890', $record->whatsapp_number);
        $this->post(route('admin.users.store'), $this->data(['email' => 'other@example.test', 'whatsapp_number' => '6281234567890']))->assertSessionHasNoErrors();
        $this->post(route('admin.users.store'), $this->data(['email' => 'CONTACT@EXAMPLE.TEST']))->assertSessionHasErrors('email');
        $this->put(route('admin.users.update', $record), $this->data(['email' => 'CONTACT@EXAMPLE.TEST', 'whatsapp_number' => '081234567890']))->assertSessionHasNoErrors();
        $other = User::where('role', 'room_pic')->firstOrFail();
        $this->put(route('admin.users.update', $other), $this->data(['email' => $other->email, 'whatsapp_number' => '+6281234567890']))->assertSessionHasNoErrors();
        $this->assertSame(3, User::where('whatsapp_number', '+6281234567890')->count());
        $this->post('/logout');
        $this->post('/login', ['email' => ' CONTACT@EXAMPLE.TEST ', 'password' => 'New-password-123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($record);
    }

    public function test_database_rejects_duplicate_email_and_invalid_number_but_allows_shared_whatsapp(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $employee = User::where('role', 'user')->firstOrFail();
        $admin->whatsapp_number = '+6281234567890';
        $admin->save();
        foreach ([['email' => strtoupper($admin->email)], ['whatsapp_number' => '081234567890']] as $data) {
            try {
                DB::transaction(fn () => DB::table('users')->where('id', $employee->id)->update($data));
                $this->fail('Database must reject invalid or duplicate contact.');
            } catch (QueryException $error) {
                $this->assertContains((string) $error->getCode(), ['23505', '23514']);
            }
        }
        $this->assertNull($employee->fresh()->whatsapp_number);
        $this->assertNotSame($admin->email, $employee->fresh()->email);
        $this->assertGreaterThanOrEqual(2, User::whereNull('whatsapp_number')->count());
        DB::table('users')->where('id', $employee->id)->update(['whatsapp_number' => '+6281234567890']);
        $this->assertSame(2, User::where('whatsapp_number', '+6281234567890')->count());
    }
}
