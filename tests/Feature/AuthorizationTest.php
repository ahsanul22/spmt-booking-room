<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\PostgresTestCase;

class AuthorizationTest extends PostgresTestCase
{
    private const GENERAL = ['/dashboard', '/rooms', '/schedule'];

    private const EMPLOYEE = ['/my-bookings'];

    private const PIC = ['/pic/dashboard', '/pic/rooms', '/pic/approvals'];

    private const ADMIN = ['/admin/dashboard', '/admin/users', '/admin/organizational-units', '/admin/floors', '/admin/facilities', '/admin/rooms', '/admin/bookings'];

    public function test_guests_are_redirected_from_every_protected_page(): void
    {
        foreach (array_merge(self::GENERAL, self::EMPLOYEE, self::PIC, self::ADMIN) as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_user_can_only_access_general_and_employee_pages(): void
    {
        $this->login('pegawai@example.test', '/dashboard');
        $this->assertPagesAllowed(array_merge(self::GENERAL, self::EMPLOYEE));
        $this->assertPagesDenied(array_merge(self::PIC, self::ADMIN));
        $this->get('/dashboard')->assertSee('Dashboard Pegawai')->assertSee('My Booking')
            ->assertDontSee('Ruangan Saya')->assertDontSee('Permintaan Approval')->assertDontSee('Kelola User')
            ->assertSee('Pilihan ruang untuk setiap ide.')->assertDontSee('schedule-navigation');
        $this->get('/schedule')->assertOk()->assertDontSee('schedule-navigation')
            ->assertSee('aria-label="Navigasi utama"', false)->assertSee('data-schedule', false);
    }

    public function test_pic_inherits_employee_access_but_cannot_open_admin_urls(): void
    {
        $this->login('pic@example.test', '/pic/dashboard');
        $this->assertPagesAllowed(array_merge(self::GENERAL, self::EMPLOYEE, self::PIC));
        $this->assertPagesDenied(self::ADMIN);
        $this->get('/pic/dashboard')->assertSee('Dashboard PIC')->assertSee('My Booking')
            ->assertSee('Ruangan Saya')->assertSee('Permintaan Approval')->assertDontSee('Kelola User')
            ->assertSee('schedule-navigation')->assertSee('Antrean approval belum tersedia')
            ->assertDontSee('Pilihan ruang untuk setiap ide.');
    }

    public function test_admin_can_open_administration_and_pic_placeholders_without_changing_assignments(): void
    {
        $before = DB::table('room_pics')->orderBy('room_id')->orderBy('user_id')->get()->toArray();
        $this->login('admin@example.test', '/admin/dashboard');
        $this->assertPagesAllowed(array_merge(self::GENERAL, self::PIC, self::ADMIN));
        $this->assertPagesDenied(self::EMPLOYEE);
        $this->get('/admin/dashboard')->assertSee('Dashboard Super Admin')->assertSee('Kelola User')
            ->assertSee('Semua Booking')->assertDontSee('My Booking')
            ->assertViewIs('admin.dashboard')->assertSee('Pratinjau dashboard')
            ->assertSee('Aktivitas booking belum tersedia');
        $this->assertEquals($before, DB::table('room_pics')->orderBy('room_id')->orderBy('user_id')->get()->toArray());
    }

    public function test_navigation_links_are_authorized_and_dashboard_link_matches_role(): void
    {
        foreach (['pegawai@example.test' => '/dashboard', 'pic@example.test' => '/pic/dashboard', 'admin@example.test' => '/admin/dashboard'] as $email => $dashboard) {
            $this->actingAs(User::where('email', $email)->firstOrFail());
            $response = $this->get($dashboard)->assertOk();
            $response->assertSee('href="'.url($dashboard).'"', false);
            preg_match_all('/<a\b[^>]*href="([^"]+)"/', $response->getContent(), $matches);
            foreach ($matches[1] as $url) {
                if (str_starts_with($url, '#')) {
                    continue;
                }
                $this->get(html_entity_decode($url))->assertOk();
            }
            $this->get('/login')->assertRedirect($dashboard);
        }
    }

    public function test_all_dashboards_handle_missing_unit(): void
    {
        foreach (['user' => '/dashboard', 'room_pic' => '/pic/dashboard', 'super_admin' => '/admin/dashboard'] as $role => $dashboard) {
            $user = User::factory()->create(['role' => $role, 'organizational_unit_id' => null]);
            $this->actingAs($user)->get($dashboard)->assertOk()->assertSee('Belum ditentukan');
        }
    }

    public function test_unknown_role_is_denied_without_changing_database_constraint(): void
    {
        $user = User::where('email', 'pegawai@example.test')->firstOrFail();
        $user->role = 'unknown'; // Only in memory; PostgreSQL enum constraint is unchanged.
        $this->actingAs($user);
        $this->assertPagesDenied(array_merge(self::GENERAL, self::EMPLOYEE, self::PIC, self::ADMIN));
        $this->get('/login')->assertForbidden();
        $this->assertSame('user', $user->fresh()->role);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_inactive_accounts_of_all_roles_cannot_login(): void
    {
        foreach (User::all() as $user) {
            $user->is_active = false;
            $user->save();
            $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect('/login')->assertSessionHasErrors('email');
            $this->assertGuest();
        }
    }

    public function test_role_change_is_applied_to_next_request(): void
    {
        $this->login('pic@example.test', '/pic/dashboard');
        $this->get('/pic/approvals')->assertOk();
        User::where('email', 'pic@example.test')->update(['role' => 'user']);
        Auth::forgetGuards();
        $this->get('/pic/approvals')->assertForbidden();
        $this->get('/login')->assertRedirect('/dashboard');
    }

    private function login(string $email, string $dashboard): void
    {
        $this->post('/login', ['email' => $email, 'password' => 'password'])
            ->assertSessionHasNoErrors()->assertRedirect($dashboard);
        $this->assertAuthenticated();
    }

    private function assertPagesAllowed(array $urls): void
    {
        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    private function assertPagesDenied(array $urls): void
    {
        foreach ($urls as $url) {
            $this->get($url)->assertForbidden()->assertSee('Akses Ditolak');
        }
    }
}
