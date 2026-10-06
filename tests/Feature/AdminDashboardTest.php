<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Tests\PostgresTestCase;

class AdminDashboardTest extends PostgresTestCase
{
    public function test_dashboard_counts_current_bookings_without_treating_pending_as_ongoing(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-05 10:30', 'Asia/Jakarta'));
        try {
            foreach (['pending', 'approved', 'completed', 'rejected', 'cancelled'] as $status) {
                Booking::create([
                    'room_id' => Room::firstOrFail()->id, 'user_id' => User::where('role', 'user')->firstOrFail()->id,
                    'date' => '2026-10-05', 'start_time' => '10:00', 'end_time' => '11:00', 'agenda' => 'Agenda '.$status,
                    'status' => $status, 'requires_approval' => true, 'submission_token' => (string) Str::uuid(), 'request_hash' => str_repeat('a', 64),
                ]);
            }
            $this->actingAs(User::where('role', 'super_admin')->firstOrFail())->get('/admin/dashboard')->assertOk()
                ->assertViewHas('stats', fn ($stats) => array_column($stats, 'value') === [3, 1, 1])
                ->assertSee('Agenda cancelled')->assertSee('Laporan Bulanan')->assertDontSee('Pratinjau dashboard');
            $this->get('/dashboard')->assertOk()->assertViewIs('admin.dashboard')->assertSee('Dashboard Admin');
            $this->get('/pic/dashboard')->assertOk()->assertViewIs('admin.dashboard')->assertSee('Dashboard Admin');
        } finally {
            $this->travelBack();
        }
    }
}
