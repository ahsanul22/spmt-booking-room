<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Tests\PostgresTestCase;

class AuthenticationTest extends PostgresTestCase
{
    public function test_guest_can_open_login(): void
    {
        $this->get('/login')->assertOk()->assertSee('Remember Me')->assertSee('name="_token"', false)
            ->assertDontSee('Register');
    }

    public function test_all_three_development_accounts_can_login_and_logout(): void
    {
        foreach (['admin@example.test' => '/admin/dashboard', 'pic@example.test' => '/pic/dashboard', 'pegawai@example.test' => '/dashboard'] as $email => $dashboard) {
            $user = User::where('email', $email)->firstOrFail();
            $this->post('/login', ['email' => $email, 'password' => 'password'])
                ->assertSessionHasNoErrors()->assertRedirect($dashboard);
            $this->assertAuthenticatedAs($user);
            $this->get($dashboard)->assertOk()->assertSee($user->name)->assertSee($user->role)
                ->assertSee($user->organizationalUnit->name)->assertDontSee($user->password);
            $this->post('/logout')->assertRedirect('/login');
            $this->assertGuest();
        }
    }

    public function test_login_rotates_session_and_always_redirects_to_dashboard(): void
    {
        $this->get('/login');
        $oldSession = session()->getId();
        $this->withSession(['url.intended' => '/somewhere-else'])->post('/login', [
            'email' => 'pegawai@example.test', 'password' => 'password',
        ])->assertRedirect('/dashboard');
        $this->assertNotSame($oldSession, session()->getId());
        Auth::forgetGuards();
        $this->get('/dashboard')->assertOk();
    }

    public function test_wrong_password_is_rejected_without_flashing_password(): void
    {
        $this->from('/login')->post('/login', ['email' => 'pegawai@example.test', 'password' => 'wrong-secret'])
            ->assertRedirect('/login')->assertSessionHasErrors(['email' => 'Email atau password tidak sesuai.'])
            ->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_invalid_inputs_are_rejected(): void
    {
        $this->from('/login')->post('/login', ['email' => 'invalid', 'remember' => 'invalid'])
            ->assertRedirect('/login')->assertSessionHasErrors(['email', 'password', 'remember']);
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_create_session_or_remember_cookie(): void
    {
        $user = User::where('email', 'pegawai@example.test')->firstOrFail();
        $user->is_active = false;
        $user->save();
        $this->from('/login')->post('/login', [
            'email' => $user->email, 'password' => 'password', 'remember' => 1,
        ])->assertRedirect('/login')->assertSessionHasErrors(['email' => 'Akun tidak aktif. Silakan hubungi administrator.'])
            ->assertCookieMissing(Auth::guard('web')->getRecallerName());
        $this->assertGuest();
        $this->assertNull($user->fresh()->remember_token);
        $this->assertFalse(session()->has(Auth::guard('web')->getName()));
    }

    public function test_wrong_password_does_not_reveal_inactive_status(): void
    {
        $user = User::where('email', 'pegawai@example.test')->firstOrFail();
        $user->is_active = false;
        $user->save();
        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => 'Email atau password tidak sesuai.']);
        $this->assertGuest();
    }

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::where('email', 'pegawai@example.test')->firstOrFail())->get('/login')->assertRedirect('/dashboard');
    }

    public function test_dashboard_handles_missing_unit(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Belum ditentukan');
    }

    public function test_logout_invalidates_session_and_rotates_csrf_token(): void
    {
        $this->post('/login', ['email' => 'pegawai@example.test', 'password' => 'password', 'remember' => 1]);
        $sessionId = session()->getId();
        $token = session()->token();
        $this->withSession(['private_marker' => 'private'])->post('/logout')->assertRedirect('/login')
            ->assertSessionMissing('private_marker');
        $this->assertGuest();
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertNotSame($token, session()->token());
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/logout')->assertStatus(405);
    }

    public function test_registration_and_other_out_of_scope_routes_are_absent(): void
    {
        foreach (['/register', '/forgot-password', '/verify-email', '/profile'] as $url) {
            $this->get($url)->assertNotFound();
            $this->post($url)->assertNotFound();
        }
        $this->assertDatabaseCount('users', 3);
    }

    public function test_failed_logins_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => 'pegawai@example.test', 'password' => 'wrong']);
        }
        $this->post('/login', ['email' => 'pegawai@example.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertTrue(RateLimiter::tooManyAttempts('pegawai@example.test|127.0.0.1', 5));
        $this->travel(61)->seconds();
        $this->post('/login', ['email' => 'pegawai@example.test', 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_login_and_logout_require_csrf_tokens(): void
    {
        // Enable the real CSRF check that Laravel normally bypasses during tests.
        $this->app->bind(VerifyCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends VerifyCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });
        $this->post('/login', ['email' => 'pegawai@example.test', 'password' => 'password'])->assertStatus(419);
        $this->assertGuest();
        $this->actingAs(User::firstOrFail())->post('/logout')->assertStatus(419);
        $this->assertAuthenticated();
    }

    public function test_disabled_account_loses_existing_session(): void
    {
        $this->post('/login', ['email' => 'pegawai@example.test', 'password' => 'password']);
        User::where('email', 'pegawai@example.test')->update(['is_active' => false]);
        Auth::forgetGuards();
        $this->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_remember_cookie_restores_active_account_but_rejects_disabled_account(): void
    {
        $response = $this->post('/login', ['email' => 'pegawai@example.test', 'password' => 'password', 'remember' => 1]);
        $cookieName = Auth::guard('web')->getRecallerName();
        $response->assertCookie($cookieName);
        $cookie = $response->getCookie($cookieName, false)->getValue();
        session()->flush();
        Auth::forgetGuards();
        $this->withUnencryptedCookies([$cookieName => $cookie])->get('/dashboard')->assertOk();
        $this->assertTrue(Auth::guard('web')->viaRemember());

        User::where('email', 'pegawai@example.test')->update(['is_active' => false]);
        session()->flush();
        Auth::forgetGuards();
        $this->withUnencryptedCookies([$cookieName => $cookie])->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }
}
