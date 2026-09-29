<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PicDashboardBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pic_dashboard_renders_layout_matching_admin_dashboard(): void
    {
        $this->seed();

        $pic = User::where('email', 'pic@example.test')->firstOrFail();

        $response = $this->actingAs($pic)->get(route('pic.dashboard'));

        $response->assertOk();

        // 1. Header with title, subtitle, and primary action button
        $response->assertSee('Workspace PIC Ruangan');
        $response->assertSee('Dashboard PIC');
        $response->assertSee('Periksa pengajuan dan pastikan ketersediaan ruangan tanggung jawab Anda.');
        $response->assertSee('Permintaan Approval');
        $response->assertSee(route('pic.approvals.index'));

        // 2. Preview note banner
        $response->assertSee('Pratinjau dashboard PIC');

        // 3. 3 Stat cards with numeric counts
        $response->assertSee('Menunggu approval');
        $response->assertSee('Ruangan tanggung jawab');
        $response->assertSee('Jadwal hari ini');
        $response->assertSee('Pengajuan perlu ditinjau');
        $response->assertSee('Ruangan ditugaskan');
        $response->assertSee('Penggunaan hari ini');
        $response->assertSee((string) $pic->managedRooms()->count());

        // 4. Main shortcuts card
        $response->assertSee('Kelola Pengajuan dan Ruangan');
        $response->assertSee('Ruangan Saya');
        $response->assertSee(route('pic.rooms.index'));
        $response->assertSee('Riwayat Approval');
        $response->assertSee(route('pic.approvals.history'));
        $response->assertSee('Jadwal Ruangan');
        $response->assertSee(route('schedule.index'));

        // 5. Activity review card
        $response->assertSee('Menunggu Tinjauan Anda');
        $response->assertSee('Tinjau permintaan approval');
        $response->assertSee('Buka permintaan');

        // 6. Aside feature card & review guide
        $response->assertSee('Tinjau cepat.');
        $response->assertSee('Persetujuan tepat.');
        $response->assertSee('Saat meninjau pengajuan');

        // 7. Account info
        $response->assertSee('Akun Anda');
        $response->assertSee($pic->name);
        $response->assertSee($pic->roleLabel());

        // 8. Must not display employee or admin actions
        $response->assertDontSee('My Booking');
        $response->assertDontSee('Booking Ruangan');
        $response->assertDontSee('Kelola User');
    }
}
