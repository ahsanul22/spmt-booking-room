<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PicDashboardBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pic_dashboard_renders_modern_banner_matching_mockup(): void
    {
        $this->seed();

        $pic = User::where('email', 'pic@example.test')->firstOrFail();

        $response = $this->actingAs($pic)->get(route('pic.dashboard'));

        $response->assertOk();

        // 1. Pill badge
        $response->assertSee('DASHBOARD PIC RUANGAN');

        // 2. Title & Subtitle
        $response->assertSee('Selamat Datang! 👋');
        $response->assertSee('Mari kelola pengajuan dengan mudah.');

        // 3. Description
        $response->assertSee('Di sini Anda dapat dengan mudah memeriksa pengajuan pemesanan ruangan yang menjadi tanggung jawab Anda.');
        $response->assertSee('Prioritaskan pengajuan yang perlu segera ditinjau');

        // 4. Action button
        $response->assertSee('Cek Daftar Pengajuan');
        $response->assertSee(route('pic.approvals.index'));

        // 5. Banner image asset
        $response->assertSee('images/dashboard-pic-banner.jpg');

        // 6. Must not display personal booking actions for PIC role
        $response->assertDontSee('My Booking');
        $response->assertDontSee('Booking Ruangan');
    }
}
