<?php

namespace Tests\Feature;

use App\Http\Controllers\FrontendSkeletonController;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\PostgresTestCase;

class FrontendSkeletonTest extends PostgresTestCase
{
    public function test_guests_are_redirected_from_every_skeleton_page(): void
    {
        foreach ($this->pages() as $page) {
            $this->get($page['url'])->assertRedirect(route('login'));
        }
    }

    public function test_each_role_can_render_its_empty_pages_and_is_denied_other_areas(): void
    {
        $roles = [
            'user' => ['access-general', 'access-employee'],
            'room_pic' => ['access-general', 'access-employee', 'access-pic'],
            'super_admin' => ['access-general', 'access-pic', 'access-admin'],
        ];

        foreach ($roles as $role => $abilities) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            foreach ($this->pages() as $page) {
                $response = $this->get($page['url']);
                if (in_array($page['ability'], $abilities, true)) {
                    $response->assertOk()->assertSee(str_contains($page['url'], 'schedule') ? 'Pratinjau desain' : 'Pratinjau Tahap 3');
                    // Every page link, including preview detail/edit and back, is usable.
                    preg_match_all('/<a\b[^>]*href="([^"]+)"/', $response->getContent(), $links);
                    foreach (array_unique($links[1]) as $url) {
                        if (str_starts_with($url, '#')) {
                            continue;
                        }
                        $this->get(html_entity_decode($url))->assertOk();
                    }
                } else {
                    $response->assertForbidden();
                }
            }
        }
    }

    public function test_skeleton_has_no_write_endpoints_even_if_submit_prevention_is_bypassed(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        foreach ($this->pages() as $page) {
            foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
                $this->call($method, $page['url'])->assertStatus(405);
            }
        }
    }

    public function test_empty_tables_and_forms_expose_expected_structure(): void
    {
        $this->actingAs(User::where('role', 'room_pic')->firstOrFail());
        $this->get(route('rooms.index'))->assertSee('Belum ada data ruangan.')->assertDontSee('Kelola PIC');
        $this->get(route('rooms.search'))->assertSee('Belum ada hasil pencarian.');
        $this->get(route('my-bookings.index'))->assertSee('Belum ada booking.')
            ->assertSee(['Semua', 'Pending', 'Approved', 'Completed', 'Cancelled', 'Rejected']);
        $this->get(route('pic.rooms.index'))->assertSee('Belum ada ruangan yang ditugaskan.');
        $this->get(route('pic.approvals.index'))->assertSee('Belum ada permintaan approval.');
        $this->get(route('pic.approvals.history'))->assertSee('Belum ada riwayat approval.');
        $this->get(route('schedule.index'))->assertSee('Belum ada jadwal ruangan.');
        $this->get(route('my-bookings.create'))->assertSee([
            'name="room_id"', 'name="date"', 'name="start_time"', 'name="end_time"',
            'name="participant_count"', 'name="agenda"', 'name="notes"',
            'onsubmit="event.preventDefault()"', 'type="button" disabled',
        ], false);
        $this->get(route('pic.approvals.show', 'preview'))
            ->assertSee('name="rejection_reason"', false)->assertSee(['Approve', 'Reject']);

        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $this->get(route('admin.users.create'))->assertSee([
            'name="name"', 'name="email"', 'name="password"', 'name="password_confirmation"',
            'name="organizational_unit_id"', 'name="role"', 'name="is_active"',
        ], false);
        $this->get(route('admin.rooms.create'))->assertSee([
            'name="name"', 'name="code"', 'name="floor_id"', 'name="capacity"',
            'name="description"', 'name="access_type"', 'name="requires_approval"',
            'name="status"', 'name="is_active"',
        ], false)->assertSee('name="facility_ids[]"', false);
        $this->get(route('admin.rooms.pics', Room::firstOrFail()))->assertSee('name="pic_ids[]"', false);
        $this->get(route('admin.rooms.access', Room::where('access_type', 'restricted')->firstOrFail()))->assertSee('name="organizational_unit_ids[]"', false);
        $this->get(route('admin.bookings.index'))->assertSee('Belum ada booking.');
        $this->get(route('admin.dashboard'))->assertDontSee('Ruangan Saya')->assertDontSee('My Booking');
    }

    public function test_views_can_receive_real_room_data_without_hardcoded_rows(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $room = Room::with(['floor', 'facilities', 'pics', 'allowedOrganizationalUnits'])->firstOrFail();
        $room->name = '<script>alert("room")</script>';
        $room->save();
        $this->get(route('admin.rooms.show', $room))->assertOk()
            ->assertSee($room->name)->assertDontSee($room->name, false)
            ->assertSee($room->floor->name)
            ->assertSee(route('admin.rooms.pics', $room->id), false);
        $this->view('user.rooms.index', ['rooms' => collect([$room])])
            ->assertSee($room->name)->assertDontSee('Belum ada data ruangan.');
    }

    public function test_user_form_requires_password_only_on_create_and_never_prefills_it(): void
    {
        $user = User::where('role', 'super_admin')->firstOrFail();
        $this->actingAs($user);
        foreach (['create' => true, 'edit' => false] as $view => $required) {
            $response = $this->get(route('admin.users.'.$view, $view === 'edit' ? $user : []))->assertOk();
            $html = $response->getContent();
            foreach (['password', 'password_confirmation'] as $name) {
                preg_match('/<input[^>]+name="'.$name.'"[^>]*>/', $html, $matches);
                $this->assertNotEmpty($matches);
                $this->assertSame($required, str_contains($matches[0], 'required'));
                $this->assertStringNotContainsString('value=', $matches[0]);
            }
        }
    }

    private function pages(): array
    {
        $pages = [];
        foreach (Route::getRoutes() as $route) {
            // Admin preview pages now have module controllers, but remain placeholders.
            if (! str_starts_with($route->getActionName(), FrontendSkeletonController::class)
                && ! in_array($route->getName(), ['admin.bookings.index', 'admin.bookings.show', 'admin.schedule.index'], true)) {
                continue;
            }
            $abilities = array_values(array_filter($route->middleware(), fn ($middleware) => str_starts_with($middleware, 'can:')));
            $this->assertContains('auth', $route->middleware());
            $this->assertCount(1, $abilities);
            $this->assertSame(['GET', 'HEAD'], $route->methods());
            $pages[] = [
                'url' => '/'.preg_replace('/\{[^}]+\}/', 'preview', $route->uri()),
                'ability' => substr($abilities[0], 4),
            ];
        }
        $this->assertCount(14, $pages);

        return $pages;
    }
}
