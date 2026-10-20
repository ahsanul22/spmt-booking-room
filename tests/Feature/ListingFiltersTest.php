<?php

namespace Tests\Feature;

use App\Livewire\ListingFilters;
use App\Livewire\RoomCatalog;
use App\Livewire\RoomSchedule;
use App\Models\Branch;
use App\Models\Room;
use App\Models\User;
use Livewire\Livewire;
use Tests\PostgresTestCase;

class ListingFiltersTest extends PostgresTestCase
{
    public function test_user_directory_filters_effective_location_access_and_has_only_manage_actions(): void
    {
        $admin = User::where('role', 'super_admin')->firstOrFail();
        $employee = User::where('role', 'user')->firstOrFail();
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $branch = Branch::create(['name' => 'Cabang Pengujian', 'code' => 'FILTER-TEST']);
        $employee->branches()->sync([$branch->id]);
        $this->actingAs($admin);
        $response = $this->get(route('admin.users.index', ['branch_id' => $branch->id]))
            ->assertOk()->assertSee($employee->email)->assertSee($admin->email)->assertDontSee($pic->email)
            ->assertSeeLivewire(ListingFilters::class)->assertDontSee('Nonaktifkan')->assertDontSee('>Detail<', false);
        $response->assertSee(route('admin.users.edit', $employee), false);
        $this->get(route('admin.users.show', $employee))->assertRedirect(route('admin.users.edit', $employee));
        $this->get(route('admin.users.edit', $employee))->assertOk()->assertSee('Status Aktif')
            ->assertSee('name="is_active"', false)->assertSee('Reset Password');
        $this->get(route('admin.users.index', ['branch_id' => 'invalid']))->assertSessionHasErrors('branch_id');
    }

    public function test_list_filters_wait_for_apply_and_use_livewire_navigation_without_old_page(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $branch = Branch::firstOrFail();
        $component = Livewire::test(ListingFilters::class, ['destination' => 'admin.users.index'])
            ->set('branch', (string) $branch->id)->assertNoRedirect()
            ->call('applyFilters')->assertHasNoErrors()
            ->assertRedirect(route('admin.users.index', ['branch_id' => $branch->id]));
        $this->assertTrue($component->effects['redirectUsingNavigate']);
        Livewire::test(ListingFilters::class, ['destination' => 'admin.rooms.index'])
            ->set('branch', '999999999')->call('applyFilters')->assertHasErrors('branch')->assertNoRedirect();
        Livewire::test(ListingFilters::class, ['destination' => 'admin.whatsapp.index', 'kind' => 'whatsapp'])
            ->set('event', 'consumption')->call('applyFilters')->assertRedirect(route('admin.whatsapp.index', ['event' => 'consumption']));
        foreach (['admin.approvals.index', 'admin.approvals.history', 'pic.approvals.index', 'pic.approvals.history', 'pic.rooms.index'] as $destination) {
            $this->get(route($destination))->assertOk();
            Livewire::test(ListingFilters::class, ['destination' => $destination])->call('applyFilters')->assertRedirect(route($destination));
        }
    }

    public function test_report_filters_keep_export_scope_and_reject_rooms_outside_authorized_scope(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $room = Room::firstOrFail();
        Livewire::test(ListingFilters::class, ['destination' => 'admin.reports.index', 'kind' => 'report'])
            ->set('branch', (string) $room->branch_id)->set('room', (string) $room->id)->set('month', '2026-10')
            ->call('applyFilters')->assertHasNoErrors()->assertRedirect(route('admin.reports.index', ['branch_id' => $room->branch_id, 'room_id' => $room->id, 'month' => '2026-10']));
        $this->actingAs(User::where('role', 'room_pic')->firstOrFail());
        Livewire::test(ListingFilters::class, ['destination' => 'pic.reports.index', 'kind' => 'report'])
            ->set('room', '999999999')->call('applyFilters')->assertNotFound();
        Livewire::test(ListingFilters::class, ['destination' => 'pic.reports.index', 'kind' => 'report'])
            ->set('branch', (string) $room->branch_id)->call('applyFilters')->assertHasErrors('branch')->assertNoRedirect();
        Livewire::test(ListingFilters::class, ['destination' => 'admin.users.index'])->assertForbidden();
        Livewire::test(ListingFilters::class, ['destination' => 'logout'])->assertForbidden();
    }

    public function test_schedule_and_catalog_dropdowns_apply_only_after_display_and_preserve_access_checks(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
        $room = Room::firstOrFail();
        Livewire::test(RoomSchedule::class)->set('draftBranch', (string) $room->branch_id)
            ->assertSet('branchId', null)->call('applyBranch')->assertSet('branchId', $room->branch_id)
            ->set('draftRoom', (string) $room->id)->assertSet('roomId', null)
            ->call('applyRoom')->assertSet('roomId', $room->id);
        Livewire::test(RoomCatalog::class)->set('draftBranch', (string) $room->branch_id)
            ->assertSet('branchId', null)->call('applyBranch')->assertSet('branchId', $room->branch_id);
        $this->actingAs(User::where('role', 'user')->firstOrFail());
        Livewire::test(RoomSchedule::class)->set('draftBranch', (string) $room->branch_id)->call('applyBranch')->assertForbidden();
        Livewire::test(RoomCatalog::class)->set('draftBranch', (string) $room->branch_id)->call('applyBranch')->assertForbidden();
    }
}
