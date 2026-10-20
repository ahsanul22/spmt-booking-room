<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappRecipient;
use App\Models\WhatsappTemplate;
use App\Services\BookingService;
use App\Services\WhatsappDelivery;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\PostgresTestCase;

class WhatsappManagementTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-13 08:00', 'Asia/Jakarta'));
        Http::preventStrayRequests();
        config(['whatsapp.enabled' => true, 'whatsapp.token' => 'fake-token']);
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function account(string $role, ?string $number = null): User
    {
        $user = User::where('role', $role)->firstOrFail();
        if ($number) {
            $user->whatsapp_number = $number;
            $user->save();
        }

        return $user;
    }

    private function data(array $changes = []): array
    {
        return array_replace(['is_active' => '1', 'include_room_pics' => '0', 'body' => 'Booking #{booking_id} {room}', 'recipient_ids' => []], $changes);
    }

    private function configure(string $event, array $data): void
    {
        $this->actingAs($this->account(User::ROLE_SUPER_ADMIN));
        $this->put(route('admin.whatsapp.update', WhatsappTemplate::where('event', $event)->sole()), $this->data($data))->assertSessionHasNoErrors();
    }

    private function booking(string $date = '2026-10-14'): Booking
    {
        $room = Room::where('code', 'DEMO-SM')->sole();

        return app(BookingService::class)->submit($this->account(User::ROLE_USER), $room->id, [
            'date' => $date, 'start_time' => '10:00', 'end_time' => '11:00', 'agenda' => 'Rapat <script>alert(1)</script>',
            'meeting_preparation' => true, 'participant_count' => 8, 'consumption_snack' => true, 'consumption_meal' => true,
        ], (string) Str::uuid());
    }

    public function test_only_admin_can_manage_settings_preview_and_retry_and_csrf_is_required(): void
    {
        $template = WhatsappTemplate::firstOrFail();
        $booking = $this->booking();
        $message = WhatsappMessage::create(['booking_id' => $booking->id, 'event' => 'approval_request', 'number' => '+6281234567890', 'message' => 'Pengujian']);
        $this->get(route('admin.whatsapp.index'))->assertRedirect(route('login'));
        foreach ([User::ROLE_USER, User::ROLE_ROOM_PIC] as $role) {
            $this->actingAs($this->account($role));
            $this->get(route('admin.whatsapp.index'))->assertForbidden();
            $this->put(route('admin.whatsapp.update', $template), $this->data())->assertForbidden();
            $this->post(route('admin.whatsapp.preview', $template), $this->data())->assertForbidden();
            $this->post(route('admin.whatsapp.retry', $message))->assertForbidden();
        }
        $this->actingAs($this->account(User::ROLE_SUPER_ADMIN));
        $this->get(route('admin.whatsapp.index'))->assertOk()->assertSee('<title>Notifikasi</title>', false)->assertSee('Pengujian');
        $this->app->bind(VerifyCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends VerifyCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });
        $this->put(route('admin.whatsapp.update', $template), $this->data())->assertStatus(419);
        $this->post(route('admin.whatsapp.preview', $template), $this->data())->assertStatus(419);
        $this->post(route('admin.whatsapp.retry', $message))->assertStatus(419);
    }

    public function test_user_number_normalizes_validates_clears_and_remains_hidden(): void
    {
        $this->actingAs($this->account(User::ROLE_SUPER_ADMIN));
        $user = $this->account(User::ROLE_USER);
        $data = ['name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'is_active' => '1', 'whatsapp_number' => '0812 3456-7890'];
        $this->put(route('admin.users.update', $user), $data)->assertSessionHasNoErrors();
        $this->assertSame('+6281234567890', $user->fresh()->whatsapp_number);
        $this->assertArrayNotHasKey('whatsapp_number', $user->fresh()->toArray());
        $this->get(route('admin.users.show', $user))->assertSee('+6281234567890');
        $this->get(route('admin.users.edit', $user))->assertSee('name="whatsapp_number"', false);
        $this->put(route('admin.users.update', $user), array_replace($data, ['whatsapp_number' => ['bad']]))->assertSessionHasErrors('whatsapp_number');
        $this->get(route('admin.users.edit', $user))->assertOk()->assertSee('nomor WhatsApp');
        unset($data['whatsapp_number']);
        $this->put(route('admin.users.update', $user), $data)->assertSessionHasNoErrors();
        $this->assertSame('+6281234567890', $user->fresh()->whatsapp_number);
        $this->put(route('admin.users.update', $user), $data + ['whatsapp_number' => ''])->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->whatsapp_number);
    }

    public function test_settings_reject_unknown_variables_invalid_roles_missing_numbers_and_no_recipients(): void
    {
        $admin = $this->account(User::ROLE_SUPER_ADMIN, '+6281234567890');
        $employee = $this->account(User::ROLE_USER, '+6281234567891');
        $this->actingAs($admin);
        $template = WhatsappTemplate::where('event', 'approval_request')->sole();
        $this->put(route('admin.whatsapp.update', $template), $this->data(['body' => '{password}', 'recipient_ids' => [$admin->id]]))->assertSessionHasErrors('body');
        $this->put(route('admin.whatsapp.update', $template), $this->data(['recipient_ids' => [$employee->id]]))->assertSessionHasErrors('recipient_ids');
        $this->put(route('admin.whatsapp.update', $template), $this->data())->assertSessionHasErrors('recipient_ids');
        $employee->whatsapp_number = null;
        $employee->save();
        $consumption = WhatsappTemplate::where('event', 'consumption')->sole();
        $this->put(route('admin.whatsapp.update', $consumption), $this->data(['recipient_ids' => [$employee->id]]))->assertSessionHasErrors('recipient_ids');
        $this->put(route('admin.whatsapp.update', $consumption), $this->data(['include_room_pics' => '1']))->assertSessionHasErrors('include_room_pics');
        $this->put(route('admin.whatsapp.update', $template), $this->data(['body' => ['bad'], 'recipient_ids' => [['bad']]]))->assertSessionHasErrors(['body', 'recipient_ids.0']);
        $this->get(route('admin.whatsapp.index'))->assertOk()->assertSee('Data belum disimpan');
        $this->assertFalse($template->fresh()->is_active);
        $this->assertSame(0, WhatsappRecipient::count());
    }

    public function test_pic_and_admin_receive_approval_consumption_uses_selected_user_and_links_follow_role(): void
    {
        $admin = $this->account(User::ROLE_SUPER_ADMIN, '+6281234567890');
        $pic = $this->account(User::ROLE_ROOM_PIC, '+6281234567891');
        $employee = $this->account(User::ROLE_USER, '+6281234567892');
        $this->configure('approval_request', ['include_room_pics' => '1', 'recipient_ids' => [$admin->id], 'body' => '{applicant} {review_url}']);
        $this->configure('consumption', ['recipient_ids' => [$employee->id], 'body' => '{room} {participant_count}']);
        $booking = $this->booking();
        $this->assertSame(2, WhatsappMessage::count());
        $this->assertStringContainsString('/admin/approvals/', WhatsappMessage::where('recipient_user_id', $admin->id)->sole()->message);
        $this->assertStringContainsString('/pic/approvals/', WhatsappMessage::where('recipient_user_id', $pic->id)->sole()->message);
        app(BookingService::class)->decide($pic, $booking, 'approved', null, ['approved_consumption_snack' => true, 'approved_consumption_meal' => true]);
        $consumption = WhatsappMessage::where('event', 'consumption')->sole();
        $this->assertSame($employee->id, $consumption->recipient_user_id);
        $this->assertSame($employee->whatsapp_number, $consumption->number);
        $this->assertStringContainsString('8', $consumption->message);
        $this->assertNull(WhatsappRecipient::firstOrFail()->number);
        $pic->whatsapp_number = $admin->whatsapp_number;
        $pic->save();
        $nextBooking = $this->booking('2026-10-15');
        $this->assertSame(1, WhatsappMessage::where('booking_id', $nextBooking->id)->count());
    }

    public function test_number_change_skips_old_snapshot_and_new_booking_uses_current_number(): void
    {
        $pic = $this->account(User::ROLE_ROOM_PIC, '+6281234567891');
        $this->configure('approval_request', ['include_room_pics' => '1']);
        $this->booking();
        $message = WhatsappMessage::sole();
        $pic->whatsapp_number = '+6281234567899';
        $pic->save();
        Http::fake();
        app(WhatsappDelivery::class)->send($message->id);
        $this->assertSame('skipped', $message->fresh()->status);
        $this->assertSame('+6281234567891', $message->fresh()->number);
        Http::assertNothingSent();
        $this->booking('2026-10-15');
        $this->assertSame('+6281234567899', WhatsappMessage::latest('id')->firstOrFail()->number);
        $this->assertSame($pic->id, WhatsappMessage::latest('id')->firstOrFail()->recipient_user_id);
    }

    public function test_preview_is_escaped_and_does_not_save_or_send_and_history_filter_works(): void
    {
        $admin = $this->account(User::ROLE_SUPER_ADMIN, '+6281234567890');
        $this->configure('approval_request', ['recipient_ids' => [$admin->id]]);
        $this->booking();
        $template = WhatsappTemplate::where('event', 'approval_request')->sole();
        $original = $template->body;
        Http::fake();
        $this->post(route('admin.whatsapp.preview', $template), $this->data(['recipient_ids' => [$admin->id], 'body' => '{agenda}']))->assertSessionHas('whatsapp_preview');
        $this->get(route('admin.whatsapp.index'))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->assertSame($original, $template->fresh()->body);
        $this->assertSame(1, WhatsappMessage::count());
        Http::assertNothingSent();
        $this->get(route('admin.whatsapp.index', ['event' => 'consumption']))->assertSee('Belum ada pesan untuk filter ini');
    }

    public function test_admin_retry_only_requeues_failed_message_and_preserves_content_snapshot(): void
    {
        $admin = $this->account(User::ROLE_SUPER_ADMIN, '+6281234567890');
        $this->configure('approval_request', ['recipient_ids' => [$admin->id], 'body' => 'Pesan awal {booking_id}']);
        $this->booking();
        $message = WhatsappMessage::sole();
        $message->update(['status' => 'failed', 'attempts' => 1, 'error_code' => 'provider_rejected']);
        $this->configure('approval_request', ['recipient_ids' => [$admin->id], 'body' => 'Pesan baru {booking_id}']);
        Http::fake();
        $this->post(route('admin.whatsapp.retry', $message))->assertSessionHasNoErrors();
        $this->assertSame('pending', $message->fresh()->status);
        $this->assertStringContainsString('Pesan awal', $message->fresh()->message);
        Http::assertNothingSent();
        $message->refresh()->update(['status' => 'unknown']);
        $this->post(route('admin.whatsapp.retry', $message))->assertSessionHasErrors('retry');
        $this->assertSame('unknown', $message->fresh()->status);
    }

    public function test_inactive_user_removed_pic_and_disabled_event_are_rechecked_before_send(): void
    {
        $pic = $this->account(User::ROLE_ROOM_PIC, '+6281234567891');
        $this->configure('approval_request', ['include_room_pics' => '1']);
        $this->booking();
        $message = WhatsappMessage::sole();
        $pic->is_active = false;
        $pic->save();
        Http::fake();
        app(WhatsappDelivery::class)->send($message->id);
        $this->assertSame('skipped', $message->fresh()->status);
        $pic->is_active = true;
        $pic->save();
        $message->refresh()->update(['status' => 'pending']);
        Room::where('code', 'DEMO-SM')->sole()->pics()->detach($pic->id);
        app(WhatsappDelivery::class)->send($message->id);
        $this->assertSame('skipped', $message->fresh()->status);
        Room::where('code', 'DEMO-SM')->sole()->pics()->attach($pic->id);
        $message->refresh()->update(['status' => 'pending']);
        WhatsappTemplate::where('event', 'approval_request')->update(['is_active' => false]);
        app(WhatsappDelivery::class)->send($message->id);
        $this->assertSame('skipped', $message->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_migration_maps_only_unambiguous_eligible_users_and_preserves_old_messages(): void
    {
        $original = config('database.connections.pgsql.search_path');
        $schema = 'whatsapp_migration_test_'.bin2hex(random_bytes(6));
        DB::statement('CREATE SCHEMA '.$schema);
        config(['database.connections.pgsql.search_path' => $schema]);
        DB::purge('pgsql');
        try {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('role');
                $table->boolean('is_active');
                $table->string('whatsapp_number')->nullable();
            });
            Schema::create('bookings', fn (Blueprint $table) => $table->id());
            (require database_path('migrations/2026_10_13_000001_create_whatsapp_notifications.php'))->up();
            DB::table('users')->insert([
                ['id' => 1, 'role' => User::ROLE_SUPER_ADMIN, 'is_active' => true, 'whatsapp_number' => '+6281234567890'],
                ['id' => 2, 'role' => User::ROLE_USER, 'is_active' => true, 'whatsapp_number' => '+6281234567890'],
            ]);
            DB::table('bookings')->insert(['id' => 1]);
            foreach (['approval_request', 'consumption'] as $event) {
                DB::table('whatsapp_recipients')->insert(['purpose' => $event, 'number' => '+6281234567890', 'is_active' => true]);
                DB::table('whatsapp_messages')->insert(['booking_id' => 1, 'event' => $event, 'number' => '+6281234567890', 'message' => 'Riwayat asli', 'status' => 'accepted']);
            }
            (require database_path('migrations/2026_10_13_000002_manage_whatsapp_users_and_templates.php'))->up();
            $approval = DB::table('whatsapp_recipients')->where('purpose', 'approval_request')->first();
            $consumption = DB::table('whatsapp_recipients')->where('purpose', 'consumption')->first();
            $this->assertSame(1, $approval->user_id);
            $this->assertNull($approval->number);
            $this->assertNull($consumption->user_id);
            $this->assertFalse($consumption->is_active);
            $this->assertSame(2, DB::table('whatsapp_messages')->where('message', 'Riwayat asli')->where('status', 'accepted')->count());
            $this->assertSame(1, DB::table('whatsapp_messages')->where('event', 'approval_request')->first()->recipient_user_id);
            $this->assertSame(0, DB::table('whatsapp_templates')->where('is_active', true)->count());
            $this->assertSame(2, DB::table('users')->where('whatsapp_number', '+6281234567890')->count());
        } finally {
            config(['database.connections.pgsql.search_path' => $original]);
            DB::purge('pgsql');
            DB::statement('DROP SCHEMA '.$schema.' CASCADE');
        }
    }
}
