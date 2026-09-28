<?php

namespace Tests\Feature;

use App\Livewire\BookingPreparationForm;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\Process\Process;
use Tests\PostgresTestCase;

class BookingWorkflowTest extends PostgresTestCase
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
        return Room::where('code', 'DEMO-SM')->firstOrFail();
    }

    private function data(array $changes = []): array
    {
        return array_replace(['date' => '2026-09-27', 'start_time' => '12:00', 'end_time' => '13:00', 'agenda' => 'Rapat tim', 'notes' => ''], $changes);
    }

    private function submit(array $changes = [], ?string $token = null): Booking
    {
        return app(BookingService::class)->submit(auth()->user(), $this->room()->id, $this->data($changes), $token ?? (string) Str::uuid());
    }

    private function assertInvalid(callable $action, string $field): void
    {
        try {
            $action();
            $this->fail('Expected validation error: '.$field);
        } catch (ValidationException $error) {
            $this->assertArrayHasKey($field, $error->errors());
        }
    }

    public function test_livewire_submission_persists_approved_booking_and_redirects_to_real_detail(): void
    {
        $component = Livewire::test(BookingPreparationForm::class, ['roomId' => $this->room()->id]);
        foreach ($this->data(['agenda' => '<script>agenda</script>']) as $field => $value) {
            $component->set($field, $value);
        }
        $component->call('submitBooking')->assertHasNoErrors();
        $booking = Booking::sole();
        $this->assertSame('approved', $booking->status);
        $this->assertSame(auth()->id(), $booking->user_id);
        $this->assertSame(auth()->user()->organizationalUnit->name, $booking->unit_name);
        $component->assertRedirect(route('my-bookings.show', $booking->id));
        $this->get(route('my-bookings.show', $booking->id))->assertOk()->assertSee('Booking dikonfirmasi')
            ->assertSee('<script>agenda</script>')->assertDontSee('<script>agenda</script>', false);
        $this->get('/my-bookings')->assertOk()->assertSee('Approved')->assertSee($this->room()->name);
    }

    public function test_submission_rechecks_notice_window_even_after_successful_check(): void
    {
        $component = Livewire::test(BookingPreparationForm::class, ['roomId' => $this->room()->id]);
        foreach ($this->data() as $field => $value) { $component->set($field, $value); }
        $component->call('checkPlan')->assertHasNoErrors();
        $this->travel(1)->minutes();
        $component->call('submitBooking')->assertHasErrors('start_time');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_retry_is_idempotent_and_reusing_token_for_different_data_is_rejected(): void
    {
        $token = (string) Str::uuid();
        $first = $this->submit(token: $token);
        $this->travel(3)->hours();
        $second = $this->submit(token: $token);
        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('bookings', 1);
        $this->assertInvalid(fn () => $this->submit(['agenda' => 'Berubah'], $token), 'agenda');
    }

    public function test_overlaps_are_blocked_and_adjacent_times_are_allowed(): void
    {
        $this->submit();
        foreach ([['12:00', '13:00'], ['12:30', '13:30'], ['12:15', '12:45'], ['12:00', '14:00']] as [$start, $end]) {
            $this->assertInvalid(fn () => $this->submit(['start_time' => $start, 'end_time' => $end]), 'start_time');
        }
        $this->submit(['start_time' => '13:00', 'end_time' => '14:00']);
        $this->submit(['date' => '2026-09-28']);
        $this->assertDatabaseCount('bookings', 3);
    }

    public function test_required_approval_is_pending_blocks_time_and_rejection_releases_it(): void
    {
        $this->room()->update(['requires_approval' => true]);
        $booking = $this->submit();
        $this->assertSame('pending', $booking->status);
        $this->assertInvalid(fn () => $this->submit(), 'start_time');
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $this->actingAs($pic)->get('/pic/approvals')->assertOk()->assertSee($booking->agenda);
        $this->post(route('pic.approvals.decide', $booking->id), ['decision' => 'rejected'])
            ->assertSessionHasErrors('rejection_reason');
        $this->post(route('pic.approvals.decide', $booking->id), ['decision' => 'rejected', 'rejection_reason' => 'Kebutuhan ruangan tidak sesuai.'])->assertRedirect();
        $this->assertSame('rejected', $booking->fresh()->status);
        $this->actingAs(User::where('role', 'user')->firstOrFail());
        $this->submit();
        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_only_assigned_pic_can_decide_and_a_second_decision_is_rejected(): void
    {
        $this->room()->update(['requires_approval' => true]);
        $booking = $this->submit();
        $url = route('pic.approvals.decide', $booking->id);
        $otherPic = User::factory()->create(['role' => 'room_pic']);
        $this->actingAs($otherPic)->post($url, ['decision' => 'approved'])->assertNotFound();
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $this->actingAs($admin)->get(route('pic.approvals.show', $booking->id))->assertOk()->assertDontSee('Setujui Pengajuan');
        $this->post($url, ['decision' => 'approved'])->assertForbidden();
        $pic = User::where('email', 'pic@example.test')->firstOrFail();
        $this->actingAs($pic)->post($url, ['decision' => 'approved'])->assertRedirect();
        $this->assertSame('approved', $booking->fresh()->status);
        $this->post($url, ['decision' => 'rejected', 'rejection_reason' => 'Ubah'])->assertSessionHasErrors('decision');
        $this->get('/pic/approvals/history')->assertOk()->assertSee($booking->agenda);
    }

    public function test_private_booking_details_cannot_be_read_by_another_employee(): void
    {
        $booking = $this->submit();
        $other = User::factory()->create(['role' => 'user']);
        $this->actingAs($other)->get(route('my-bookings.show', $booking->id))->assertForbidden();
        $this->get('/my-bookings')->assertDontSee($booking->agenda);
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail())
            ->get(route('admin.bookings.show', $booking->id))->assertOk()->assertSee($booking->agenda);
        $this->get('/admin/bookings/preview')->assertNotFound();
    }

    public function test_room_status_access_and_pic_are_rechecked_before_save(): void
    {
        foreach ([['status' => 'maintenance'], ['is_active' => false], ['access_type' => 'restricted']] as $change) {
            $this->room()->update($change);
            $this->assertInvalid(fn () => $this->submit(), 'room');
            $this->room()->update(['status' => 'available', 'is_active' => true, 'access_type' => 'all']);
        }
        $this->room()->update(['requires_approval' => true]);
        $this->room()->pics()->detach();
        $this->assertInvalid(fn () => $this->submit(), 'room');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_requested_room_seed_is_repeatable_and_matches_floor_and_approval_rules(): void
    {
        $this->seed();
        $this->seed();
        $active = Room::with('floor')->where('is_active', true)->get();
        $this->assertCount(10, $active);
        $this->assertSame([2 => 1, 3 => 2, 4 => 2, 6 => 2, 7 => 3], $active->groupBy('floor.floor_number')->map->count()->sortKeys()->all());
        $this->assertCount(7, $active->where('requires_approval', false));
        $this->assertCount(3, $active->where('requires_approval', true));
        $this->assertTrue($active->every(fn ($room) => $room->access_type === 'all' && $room->status === 'available'));
        $this->assertDatabaseCount('rooms', 14);
        Livewire::test(\App\Livewire\RoomCatalog::class)->call('selectFilter', 'featured')->assertViewHas('rooms', fn ($rooms) => $rooms->total() === 3);
        Livewire::test(\App\Livewire\RoomCatalog::class)->call('selectFilter', 'other')->assertViewHas('rooms', fn ($rooms) => $rooms->total() === 7);
        foreach ($active as $room) {
            $result = app(BookingService::class)->submit(auth()->user(), $room->id, $this->data(), (string) Str::uuid());
            $this->assertSame($room->requires_approval ? 'pending' : 'approved', $result->status);
        }
    }

    public function test_approval_rechecks_room_condition_and_does_not_approve_a_past_meeting(): void
    {
        $this->room()->update(['requires_approval' => true]);
        $booking = $this->submit();
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $this->room()->update(['status' => 'maintenance']);
        $this->assertInvalid(fn () => app(BookingService::class)->decide($pic, $booking, 'approved', null), 'room');
        $this->room()->update(['status' => 'available']);
        $this->travel(3)->hours();
        $this->assertInvalid(fn () => app(BookingService::class)->decide($pic, $booking, 'approved', null), 'decision');
        $this->assertSame('pending', $booking->fresh()->status);
        app(BookingService::class)->decide($pic, $booking, 'rejected', 'Waktu sudah lewat.');
        $this->assertSame('rejected', $booking->fresh()->status);
    }

    public function test_booking_preserves_unit_name_after_the_applicants_unit_changes(): void
    {
        $booking = $this->submit();
        $unit = $booking->unit_name;
        auth()->user()->forceFill(['organizational_unit_id' => null])->save();
        $this->assertSame($unit, $booking->fresh()->unit_name);
        $this->get(route('my-bookings.show', $booking->id))->assertOk()->assertSee($unit);
    }

    public function test_active_mutations_require_csrf(): void
    {
        $this->room()->update(['requires_approval' => true]);
        $booking = $this->submit();
        $this->actingAs(User::where('role', 'room_pic')->firstOrFail());
        $this->app->bind(\App\Http\Middleware\VerifyCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends \App\Http\Middleware\VerifyCsrfToken
        {
            protected function runningUnitTests(): bool { return false; }
        });
        $this->post(route('pic.approvals.decide', $booking->id), ['decision' => 'approved'])->assertStatus(419);
        $this->post('/livewire/update', [])->assertStatus(419);
        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_concurrent_submissions_serialize_on_room_and_only_one_gets_the_slot(): void
    {
        $room = $this->room();
        $worker = new Process([PHP_BINARY, base_path('tests/Support/booking-worker.php')], base_path(), timeout: 15);
        $worker->setInput(json_encode([
            'connection' => config('database.connections.pgsql'), 'user_id' => auth()->id(), 'room_id' => $room->id,
            'data' => $this->data(), 'token' => (string) Str::uuid(),
        ], JSON_THROW_ON_ERROR));
        DB::beginTransaction();
        try {
            Room::lockForUpdate()->findOrFail($room->id);
            $worker->start();
            $deadline = microtime(true) + 8;
            while (! str_contains($worker->getOutput(), 'ready') && $worker->isRunning() && microtime(true) < $deadline) { usleep(10000); }
            $this->assertStringContainsString('ready', $worker->getOutput(), $worker->getErrorOutput());
            $this->assertTrue($worker->isRunning());
            $this->submit();
            DB::commit();
            $worker->wait();
            $this->assertSame(2, $worker->getExitCode(), $worker->getOutput().$worker->getErrorOutput());
            $this->assertStringContainsString('conflict', $worker->getOutput());
            $this->assertDatabaseCount('bookings', 1);
        } finally {
            if (DB::transactionLevel()) { DB::rollBack(); }
            if ($worker->isRunning()) { $worker->stop(); }
        }
    }
}
