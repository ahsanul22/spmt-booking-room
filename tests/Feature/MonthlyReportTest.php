<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\PostgresTestCase;

class MonthlyReportTest extends PostgresTestCase
{
    private function booking(Room $room, array $changes = []): Booking
    {
        return Booking::create(array_replace([
            'room_id' => $room->id, 'user_id' => User::where('role', 'user')->firstOrFail()->id,
            'date' => '2026-10-05', 'start_time' => '10:00', 'end_time' => '11:30',
            'agenda' => 'Rapat laporan', 'unit_name' => 'Unit saat booking', 'status' => 'approved',
            'requires_approval' => false, 'submission_token' => (string) Str::uuid(), 'request_hash' => str_repeat('a', 64),
        ], $changes));
    }

    public function test_report_routes_require_active_pic_or_admin(): void
    {
        foreach (['admin', 'pic'] as $prefix) {
            foreach (['index', 'export', 'print'] as $action) {
                $url = route($prefix.'.reports.'.$action);
                auth()->forgetGuards();
                $this->get($url)->assertRedirect(route('login'));
                $this->actingAs(User::where('role', 'user')->firstOrFail())->get($url)->assertForbidden();
                $pic = User::where('role', 'room_pic')->firstOrFail();
                $this->actingAs($pic)->get($url)->assertStatus($prefix === 'pic' ? 200 : 403);
                $this->actingAs(User::where('role', 'super_admin')->firstOrFail())->get($url)->assertOk();
            }
        }
        $pic->is_active = false;
        $pic->save();
        $this->actingAs($pic)->get(route('pic.reports.index'))->assertRedirect(route('login'));
    }

    public function test_summary_uses_meeting_month_all_statuses_and_scheduled_hours_only(): void
    {
        $room = Room::firstOrFail();
        foreach (['approved', 'pending', 'rejected', 'cancelled', 'completed'] as $status) {
            $this->booking($room, ['status' => $status]);
        }
        $this->booking($room, ['date' => '2026-09-30', 'agenda' => 'Outside month']);
        $this->booking($room, ['date' => '2026-11-01', 'agenda' => 'Outside month']);
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail())
            ->get(route('admin.reports.index', ['month' => '2026-10']))->assertOk()
            ->assertViewHas('summary', fn ($summary) => $summary['total'] === 5 && $summary['hours'] === 3.0
                && $summary['counts'] === ['pending' => 1, 'approved' => 1, 'rejected' => 1, 'cancelled' => 1, 'completed' => 1])
            ->assertSee('Unit saat booking')->assertDontSee('Outside month');
    }

    public function test_pic_scope_filter_export_and_print_cannot_leak_other_rooms(): void
    {
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $assigned = Room::firstOrFail();
        $pic->managedRooms()->sync([$assigned->id]);
        $other = Room::whereNotIn('id', $pic->managedRooms()->select('rooms.id'))->firstOrFail();
        $assigned->update(['is_active' => false]);
        $this->booking($assigned, ['agenda' => 'VISIBLE-BOOKING']);
        $this->booking($other, ['agenda' => 'PRIVATE-OTHER-ROOM']);
        $this->actingAs($pic);
        foreach (['index', 'print', 'export'] as $action) {
            $response = $this->get(route('pic.reports.'.$action, ['month' => '2026-10']))->assertOk();
            $body = $action === 'export' ? $response->streamedContent() : $response->getContent();
            $this->assertStringContainsString('VISIBLE-BOOKING', $body);
            $this->assertStringNotContainsString('PRIVATE-OTHER-ROOM', $body);
            $this->get(route('pic.reports.'.$action, ['month' => '2026-10', 'room_id' => $other->id]))->assertNotFound();
        }
        $assigned->pics()->detach($pic);
        $this->get(route('pic.reports.index', ['month' => '2026-10']))->assertOk()->assertSee('Belum ada ruangan dalam cakupan Anda.')->assertDontSee('VISIBLE-BOOKING');
    }

    public function test_filters_validate_month_and_room_and_empty_month_is_zero(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        foreach (['2026-13', 'bad', '2026-1', '0000-01', ['2026-10']] as $month) {
            $this->getJson(route('admin.reports.index', ['month' => $month]))->assertUnprocessable()->assertJsonValidationErrors('month');
        }
        $this->getJson(route('admin.reports.export', ['room_id' => 'bad']))->assertUnprocessable()->assertJsonValidationErrors('room_id');
        $this->get(route('admin.reports.index', ['room_id' => 999999]))->assertNotFound();
        $this->get(route('admin.reports.index', ['month' => '2024-02']))->assertOk()->assertSee('Belum ada booking pada periode ini.')
            ->assertViewHas('summary', fn ($summary) => $summary['total'] === 0 && $summary['hours'] === 0.0);
    }

    public function test_export_and_print_include_every_page_escape_content_and_preserve_filter(): void
    {
        $room = Room::firstOrFail();
        for ($i = 0; $i < 21; $i++) {
            $this->booking($room, ['agenda' => $i === 20 ? '=SUM(1,2)' : '<script>agenda-'.$i.'</script>']);
        }
        $this->booking(Room::whereKeyNot($room->id)->firstOrFail(), ['agenda' => 'OTHER-ROOM']);
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $filter = ['month' => '2026-10', 'room_id' => $room->id];
        $this->get(route('admin.reports.index', $filter))->assertOk()->assertDontSee('=SUM(1,2)')
            ->assertViewHas('bookings', fn ($bookings) => $bookings->count() === 20 && $bookings->total() === 21)
            ->assertSee('<script>agenda-0</script>')->assertDontSee('<script>agenda-0</script>', false);
        $csv = $this->get(route('admin.reports.export', $filter))->assertOk()->assertDownload('laporan-booking-2026-10-ruangan-'.$room->id.'.csv')->streamedContent();
        $this->assertStringContainsString("'=SUM(1,2)", $csv);
        $this->assertStringNotContainsString('OTHER-ROOM', $csv);
        $this->assertCount(22, array_filter(explode("\n", $csv)));
        $this->get(route('admin.reports.print', $filter))->assertOk()->assertSee('=SUM(1,2)')
            ->assertDontSee('<script>agenda-0</script>', false)->assertViewHas('bookings', fn ($bookings) => $bookings->count() === 21);
    }
}
