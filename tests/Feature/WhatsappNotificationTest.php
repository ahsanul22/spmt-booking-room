<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappRecipient;
use App\Models\WhatsappTemplate;
use App\Services\BookingService;
use App\Services\WhatsappDelivery;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\PostgresTestCase;

class WhatsappNotificationTest extends PostgresTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-13 08:00', 'Asia/Jakarta'));
        Http::preventStrayRequests();
        $recipient = User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail();
        $recipient->whatsapp_number = '+6281234567890';
        $recipient->save();
        WhatsappTemplate::query()->update(['is_active' => true]);
        foreach (['approval_request', 'consumption'] as $purpose) {
            WhatsappRecipient::create(['purpose' => $purpose, 'user_id' => $recipient->id]);
        }
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function submit(string $token, bool $consumption = true): Booking
    {
        $room = Room::where('code', 'DEMO-SM')->firstOrFail();
        $room->update(['allows_meeting_preparation' => true]);

        return app(BookingService::class)->submit(User::where('role', User::ROLE_USER)->firstOrFail(), $room->id, [
            'date' => '2026-10-14', 'start_time' => '10:00', 'end_time' => '11:00',
            'agenda' => 'Rapat', 'meeting_preparation' => true, 'participant_count' => 10,
            'needs_consumption' => $consumption,
        ], $token);
    }

    public function test_booking_retry_and_approval_record_each_message_once_without_http(): void
    {
        Http::fake();
        $token = (string) Str::uuid();
        $booking = $this->submit($token);
        $this->submit($token);
        $this->assertSame(1, WhatsappMessage::count());
        $this->assertSame('approval_request', WhatsappMessage::sole()->event);
        app(BookingService::class)->decide(User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail(), $booking, 'approved', null);
        $this->assertSame(2, WhatsappMessage::count());
        $this->assertStringContainsString('Perkiraan peserta: 10', WhatsappMessage::where('event', 'consumption')->sole()->message);
        Http::assertNothingSent();
    }

    public function test_rejection_and_booking_without_consumption_do_not_notify_consumption(): void
    {
        $booking = $this->submit((string) Str::uuid(), false);
        app(BookingService::class)->decide(User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail(), $booking, 'rejected', 'Tidak tersedia');
        $this->assertSame(1, WhatsappMessage::count());
        $this->assertFalse(WhatsappMessage::where('event', 'consumption')->exists());
    }

    public function test_outer_rollback_discards_booking_and_message(): void
    {
        DB::beginTransaction();
        try {
            $this->submit((string) Str::uuid());
            $this->assertSame(1, WhatsappMessage::count());
        } finally {
            DB::rollBack();
        }
        $this->assertSame(0, WhatsappMessage::count());
        $this->assertSame(0, Booking::count());
    }

    public function test_auto_approval_creates_consumption_message_and_no_consumption_creates_none(): void
    {
        Room::where('code', 'DEMO-SM')->update(['requires_approval' => false]);
        $this->submit((string) Str::uuid());
        $this->assertSame('consumption', WhatsappMessage::sole()->event);
        WhatsappMessage::query()->delete();
        Booking::query()->delete();
        $this->submit((string) Str::uuid(), false);
        $this->assertSame(0, WhatsappMessage::count());
    }

    public function test_inactive_recipients_are_skipped_and_obsolete_approval_request_is_not_sent(): void
    {
        $booking = $this->submit((string) Str::uuid());
        $message = WhatsappMessage::sole();
        WhatsappRecipient::query()->update(['is_active' => false]);
        app(BookingService::class)->decide(User::where('role', User::ROLE_SUPER_ADMIN)->firstOrFail(), $booking, 'approved', null);
        $this->assertSame(1, WhatsappMessage::count());
        config(['whatsapp.enabled' => true, 'whatsapp.token' => 'test-token']);
        Http::fake();
        app(WhatsappDelivery::class)->send($message->id);
        $this->assertSame('skipped', $message->fresh()->status);
        $this->assertSame('recipient_changed', $message->fresh()->error_code);
        WhatsappRecipient::query()->update(['is_active' => true]);
        $message->refresh()->update(['status' => 'pending']);
        app(WhatsappDelivery::class)->send($message->id);
        $this->assertSame('skipped', $message->fresh()->status);
        $this->assertSame('booking_status_changed', $message->fresh()->error_code);
        Http::assertNothingSent();
    }

    public function test_delivery_is_disabled_by_default_and_provider_acceptance_is_not_delivery_receipt(): void
    {
        $this->submit((string) Str::uuid());
        $message = WhatsappMessage::sole();
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);
        config(['whatsapp.enabled' => false, 'whatsapp.token' => 'test-token']);
        app(WhatsappDelivery::class)->send($message->id);
        Http::assertNothingSent();
        config(['whatsapp.enabled' => true]);
        app(WhatsappDelivery::class)->send($message->id);
        app(WhatsappDelivery::class)->send($message->id);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['target'] === '6281234567890' && $request['countryCode'] === '0' && $request->hasHeader('Authorization', 'test-token'));
        $this->assertSame('accepted', $message->fresh()->status);
    }

    public function test_uncertain_response_is_not_retried_and_rejection_can_be_retried_explicitly(): void
    {
        config(['whatsapp.enabled' => true, 'whatsapp.token' => 'test-token']);
        $this->submit((string) Str::uuid());
        $message = WhatsappMessage::sole();
        Http::fake(['api.fonnte.com/*' => Http::sequence()->push([], 502)->push(['status' => false])->push(['status' => true])]);
        app(WhatsappDelivery::class)->send($message->id);
        app(WhatsappDelivery::class)->send($message->id);
        $this->assertSame('unknown', $message->fresh()->status);
        Http::assertSentCount(1);
        $this->artisan('whatsapp:send', ['--retry' => $message->id])->assertFailed();
        $message->refresh()->update(['status' => 'pending']);
        app(WhatsappDelivery::class)->send($message->id);
        $this->assertSame('failed', $message->fresh()->status);
        $this->artisan('whatsapp:send', ['--retry' => $message->id])->assertSuccessful();
        $this->assertSame('accepted', $message->fresh()->status);
    }
}
