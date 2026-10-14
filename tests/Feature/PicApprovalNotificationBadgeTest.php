<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Tests\PostgresTestCase;

class PicApprovalNotificationBadgeTest extends PostgresTestCase
{
    public function test_sidebar_shows_no_badge_when_no_pending_approvals(): void
    {
        $pic = User::where('email', 'pic@example.test')->firstOrFail();

        $response = $this->actingAs($pic)->get(route('pic.dashboard'));

        $response->assertOk();
        $response->assertSee('Permintaan Approval');
        $response->assertDontSee('permintaan approval baru');
    }

    public function test_sidebar_shows_notification_badge_with_count_when_booking_pending(): void
    {
        $pic = User::where('email', 'pic@example.test')->firstOrFail();
        $room = $pic->managedRooms()->firstOrFail();
        $applicant = User::where('email', 'pegawai@example.test')->firstOrFail();

        $booking = Booking::create([
            'user_id' => $applicant->id,
            'room_id' => $room->id,
            'agenda' => 'Diskusi Proyek',
            'date' => CarbonImmutable::now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'pending',
            'requires_approval' => true,
            'submission_token' => (string) Str::uuid(),
            'request_hash' => 'hash1',
        ]);

        $response = $this->actingAs($pic)->get(route('pic.dashboard'));

        $response->assertOk();
        $response->assertSee('Permintaan Approval');
        $response->assertSee('1 permintaan approval baru');
        $response->assertSee('bg-danger');

        // When a second booking is pending
        $booking2 = Booking::create([
            'user_id' => $applicant->id,
            'room_id' => $room->id,
            'agenda' => 'Review Lanjutan',
            'date' => CarbonImmutable::now()->addDays(2)->toDateString(),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'status' => 'pending',
            'requires_approval' => true,
            'submission_token' => (string) Str::uuid(),
            'request_hash' => 'hash2',
        ]);

        $response2 = $this->actingAs($pic->fresh())->get(route('pic.dashboard'));
        $response2->assertSee('2 permintaan approval baru');

        // When approved, count decreases
        $booking->update(['status' => 'approved', 'decided_at' => now(), 'reviewed_by' => $pic->id]);
        $response3 = $this->actingAs($pic->fresh())->get(route('pic.dashboard'));
        $response3->assertSee('1 permintaan approval baru');

        // When all decided, badge disappears
        $booking2->update(['status' => 'rejected', 'decided_at' => now(), 'reviewed_by' => $pic->id, 'decision_notes' => 'Jadwal bentrok', 'rejection_reason' => 'Jadwal bentrok']);
        $response4 = $this->actingAs($pic->fresh())->get(route('pic.dashboard'));
        $response4->assertDontSee('permintaan approval baru');
    }

    public function test_pic_only_sees_pending_count_for_assigned_rooms(): void
    {
        $pic = User::where('email', 'pic@example.test')->firstOrFail();
        $applicant = User::where('email', 'pegawai@example.test')->firstOrFail();

        // Create an unassigned room
        $unassignedRoom = Room::create([
            'name' => 'Ruang Unassigned',
            'code' => 'UNASSIGNED-01',
            'floor_id' => $pic->managedRooms()->first()->floor_id,
            'capacity' => 10,
            'operational_status' => 'available',
            'access_type' => 'all',
            'requires_approval' => true,
        ]);

        Booking::create([
            'user_id' => $applicant->id,
            'room_id' => $unassignedRoom->id,
            'agenda' => 'Rapat Eksternal',
            'date' => CarbonImmutable::now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => 'pending',
            'requires_approval' => true,
            'submission_token' => (string) Str::uuid(),
            'request_hash' => 'hash3',
        ]);

        $response = $this->actingAs($pic)->get(route('pic.dashboard'));
        $response->assertDontSee('permintaan approval baru');
    }
}
