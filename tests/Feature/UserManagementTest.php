<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\OrganizationalUnit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\PostgresTestCase;

class UserManagementTest extends PostgresTestCase
{
    private function admin(): User
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $this->actingAs($admin);

        return $admin;
    }

    private function url(string $action, ?User $user = null): string
    {
        return route('admin.users.'.$action, $user);
    }

    private function data(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Pegawai Pengujian', 'email' => 'baru@example.test', 'role' => 'user',
            'organizational_unit_id' => OrganizationalUnit::firstOrFail()->id, 'is_active' => '1',
            'password' => 'New-password-123', 'password_confirmation' => 'New-password-123',
        ], $overrides);
    }

    private function guest(): void
    {
        $this->post('/logout');
        Auth::forgetGuards();
    }

    public function test_every_endpoint_requires_authentication_and_super_admin(): void
    {
        $user = User::where('role', 'user')->firstOrFail();
        $original = $user->getAttributes();
        $endpoints = [
            ['GET', 'index', null], ['GET', 'create', null], ['POST', 'store', null],
            ['GET', 'show', $user], ['GET', 'edit', $user], ['PUT', 'update', $user],
            ['PATCH', 'update', $user], ['PATCH', 'status', $user], ['PATCH', 'reset-password', $user],
        ];
        foreach ($endpoints as [$method, $action, $record]) {
            $this->call($method, $this->url($action, $record), $this->data())->assertRedirect(route('login'));
        }
        foreach (['user', 'room_pic'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            foreach ($endpoints as [$method, $action, $record]) {
                $this->call($method, $this->url($action, $record), $this->data())->assertForbidden();
            }
        }
        $this->assertSame($original, $user->fresh()->getAttributes());
        $this->assertDatabaseMissing('users', ['email' => 'baru@example.test']);
    }

    public function test_create_each_role_hashes_password_and_displays_saved_database_data(): void
    {
        $this->admin();
        $unit = OrganizationalUnit::create(['name' => 'Unit Baru dari Database', 'type' => 'directorate', 'is_active' => true]);
        $this->get($this->url('create'))->assertOk()->assertSee($unit->name)
            ->assertSee('value="'.$unit->id.'"', false)->assertDontSee('Belum ada pilihan tersedia.')
            ->assertSee($this->url('store'), false)->assertSee('name="_token"', false)->assertDontSee('event.preventDefault()');
        foreach (['user', 'room_pic', 'super_admin'] as $role) {
            $data = $this->data(['email' => $role.'-new@example.test', 'role' => $role, 'organizational_unit_id' => $unit->id]);
            $this->post($this->url('store'), $data)->assertSessionHasNoErrors()->assertSessionHas('status');
            $record = User::where('email', $data['email'])->firstOrFail();
            $this->assertDatabaseHas('users', ['id' => $record->id, 'role' => $role, 'organizational_unit_id' => $unit->id, 'is_active' => true]);
            $this->assertTrue(Hash::check($data['password'], $record->password));
            $this->assertNotSame($data['password'], $record->password);
            $this->get($this->url('show', $record))->assertOk()->assertSee($data['email'])->assertSee($role)->assertSee($unit->name)
                ->assertDontSee($record->password, false)->assertDontSee($data['password']);
            $this->get($this->url('index'))->assertOk()->assertSee($data['email'])->assertSee($unit->name);
        }
        $this->guest();
        $this->post('/login', ['email' => 'user-new@example.test', 'password' => 'New-password-123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'user-new@example.test')->firstOrFail());
    }

    public function test_duplicate_email_is_rejected_on_create_and_update(): void
    {
        $admin = $this->admin();
        $record = User::where('role', 'user')->firstOrFail();
        $count = User::count();
        $this->post($this->url('store'), $this->data(['email' => $admin->email]))->assertSessionHasErrors('email');
        $this->put($this->url('update', $record), $this->data(['email' => $admin->email]))->assertSessionHasErrors('email');
        $this->assertSame($count, User::count());
        $this->assertNotSame($admin->email, $record->fresh()->email);
        $this->put($this->url('update', $record), $this->data(['email' => $record->email]))->assertSessionHasNoErrors();
    }

    public function test_validation_displays_errors_keeps_old_input_and_never_flashes_passwords(): void
    {
        $this->admin();
        foreach ([['name', ''], ['email', 'invalid'], ['role', 'owner'], ['organizational_unit_id', 999999999],
            ['is_active', 'invalid'], ['password', ''], ['password', 'short'], ['password_confirmation', 'mismatch']] as [$field, $value]) {
            $this->from($this->url('create'))->post($this->url('store'), $this->data([$field => $value]))
                ->assertRedirect($this->url('create'))->assertSessionHasErrors($field === 'password_confirmation' ? 'password' : $field)
                ->assertSessionMissing('_old_input.password')->assertSessionMissing('_old_input.password_confirmation');
        }
        $this->get($this->url('create'))->assertSee('role="alert"', false)->assertSee('value="Pegawai Pengujian"', false);
        $this->assertDatabaseMissing('users', ['email' => 'baru@example.test']);
    }

    public function test_update_changes_name_email_role_and_unit_but_keeps_blank_password(): void
    {
        $this->admin();
        $record = User::where('role', 'user')->firstOrFail();
        $oldHash = $record->password;
        $unit = OrganizationalUnit::where('id', '!=', $record->organizational_unit_id)->firstOrFail();
        $this->get($this->url('edit', $record))->assertOk()->assertSee('value="'.$record->email.'"', false)
            ->assertSee('value="'.$record->organizational_unit_id.'" selected', false)->assertDontSee($oldHash, false);
        foreach (['room_pic', 'super_admin', 'user'] as $role) {
            $data = $this->data(['role' => $role, 'organizational_unit_id' => $unit->id, 'password' => '', 'password_confirmation' => '']);
            $this->put($this->url('update', $record), $data)->assertRedirect($this->url('show', $record))->assertSessionHasNoErrors();
            $this->assertDatabaseHas('users', ['id' => $record->id, 'name' => $data['name'], 'email' => $data['email'], 'role' => $role, 'organizational_unit_id' => $unit->id]);
            $this->assertSame($oldHash, $record->fresh()->password);
            $this->get($this->url('show', $record))->assertSee($data['name'])->assertSee($data['email'])->assertSee($role)->assertSee($unit->name)->assertSee('User berhasil diperbarui.');
            $this->get($this->url('index'))->assertSee($data['email'])->assertSee($unit->name);
        }
        $data = $this->data(['organizational_unit_id' => null]);
        unset($data['password'], $data['password_confirmation']);
        $this->patch($this->url('update', $record), $data)->assertSessionHasNoErrors();
        $this->assertNull($record->fresh()->organizational_unit_id);
        $this->assertSame($oldHash, $record->fresh()->password);
    }

    public function test_edit_password_requires_confirmation_and_changes_login_password(): void
    {
        $this->admin();
        $record = User::where('role', 'user')->firstOrFail();
        $oldHash = $record->password;
        $this->put($this->url('update', $record), $this->data(['password_confirmation' => 'wrong']))->assertSessionHasErrors('password');
        $this->assertSame($oldHash, $record->fresh()->password);
        $this->put($this->url('update', $record), $this->data())->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('New-password-123', $record->fresh()->password));
        $this->guest();
        $this->post('/login', ['email' => 'baru@example.test', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => 'baru@example.test', 'password' => 'New-password-123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($record);
    }

    public function test_status_persists_and_inactive_user_cannot_login_until_reactivated(): void
    {
        $this->admin();
        $record = User::where('role', 'user')->firstOrFail();
        $originalUnit = $record->organizational_unit_id;
        foreach ([false, true] as $active) {
            $this->admin();
            $this->patch($this->url('status', $record), ['is_active' => (int) $active, 'role' => 'super_admin'])
                ->assertRedirect($this->url('show', $record))->assertSessionHas('status');
            $this->assertDatabaseHas('users', ['id' => $record->id, 'is_active' => $active, 'role' => 'user', 'organizational_unit_id' => $originalUnit]);
            $this->get($this->url('show', $record))->assertSee('Status Aktif: '.($active ? 'Aktif' : 'Nonaktif'));
            $this->get($this->url('index'))->assertSee($record->email)->assertSee($active ? 'Nonaktifkan' : 'Aktifkan');
            $this->guest();
            $response = $this->post('/login', ['email' => $record->email, 'password' => 'password']);
            if ($active) {
                $response->assertRedirect(route('dashboard'));
                $this->assertAuthenticatedAs($record);
            } else {
                $response->assertSessionHasErrors(['email' => 'Akun tidak aktif. Silakan hubungi administrator.']);
                $this->assertGuest();
            }
        }
        $this->admin();
        $this->patch($this->url('status', $record), ['is_active' => 'invalid'])->assertSessionHasErrors('is_active');
        $this->delete($this->url('show', $record))->assertStatus(405);
        $this->assertModelExists($record);
    }

    public function test_reset_password_replaces_login_password_and_rotates_remember_token(): void
    {
        $this->admin();
        $record = User::where('role', 'user')->firstOrFail();
        $record->setRememberToken('old-remember-token');
        $record->save();
        $oldHash = $record->password;
        foreach ([[], ['password' => 'short', 'password_confirmation' => 'short'],
            ['password' => 'Reset-password-123', 'password_confirmation' => 'wrong']] as $invalid) {
            $this->patch($this->url('reset-password', $record), $invalid)->assertSessionHasErrors('password');
            $this->assertSame($oldHash, $record->fresh()->password);
        }
        $this->patch($this->url('reset-password', $record), [
            'password' => 'Reset-password-123', 'password_confirmation' => 'Reset-password-123', 'role' => 'super_admin',
        ])->assertRedirect($this->url('show', $record))->assertSessionHasNoErrors()->assertSessionHas('status');
        $record->refresh();
        $this->assertTrue(Hash::check('Reset-password-123', $record->password));
        $this->assertFalse(Hash::check('password', $record->password));
        $this->assertNotSame('old-remember-token', $record->getRememberToken());
        $this->assertSame('user', $record->role);
        $this->get($this->url('show', $record))->assertSee('Password berhasil direset.')
            ->assertDontSee($record->password, false)->assertDontSee('Reset-password-123');
        $this->guest();
        $this->post('/login', ['email' => $record->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $record->email, 'password' => 'Reset-password-123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($record);
    }

    public function test_role_changes_are_enforced_on_subsequent_requests_and_self_changes_redirect_safely(): void
    {
        $admin = $this->admin();
        $this->put($this->url('update', $admin), $this->data(['email' => $admin->email, 'role' => 'room_pic']))
            ->assertRedirect(route('pic.dashboard'));
        $this->actingAs($admin->fresh())->get(route('pic.dashboard'))->assertOk()->assertSee('User berhasil diperbarui.');
        $this->actingAs($admin->fresh())->get($this->url('index'))->assertForbidden();
        $this->assertSame('room_pic', $admin->fresh()->role);
        $another = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $this->actingAs($another)->patch($this->url('status', $another), ['is_active' => 0])->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertFalse($another->fresh()->is_active);
    }

    public function test_missing_records_and_csrf_protection_and_escaped_output(): void
    {
        $this->admin();
        foreach (['GET' => '', 'PUT' => '', 'PATCH' => '/password'] as $method => $suffix) {
            $this->call($method, '/admin/users/999999999'.$suffix, $this->data())->assertNotFound();
        }
        $this->get('/admin/users/999999999/edit')->assertNotFound();
        $this->patch('/admin/users/999999999/status', ['is_active' => 0])->assertNotFound();
        $this->get('/admin/users/preview')->assertNotFound();
        $record = User::factory()->create(['name' => '<script>alert(1)</script>', 'role' => 'user', 'is_active' => true]);
        $this->get($this->url('show', $record))->assertSee($record->name)->assertDontSee($record->name, false);
        $this->get($this->url('index'))->assertSee($record->name)->assertDontSee($record->name, false);
        $this->app->bind(VerifyCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends VerifyCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });
        $this->post($this->url('store'), $this->data())->assertStatus(419);
        $this->put($this->url('update', $record), $this->data())->assertStatus(419);
        $this->patch($this->url('status', $record), ['is_active' => 0])->assertStatus(419);
        $this->patch($this->url('reset-password', $record), $this->data())->assertStatus(419);
    }
}
