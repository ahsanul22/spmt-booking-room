<?php

namespace Tests\Feature;

use App\Livewire\RoomCatalog;
use App\Models\Room;
use App\Models\User;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\PostgresTestCase;

class RoomCatalogTest extends PostgresTestCase
{
    public function test_dashboard_renders_database_rooms_on_initial_load(): void
    {
        $this->actingAs(User::where('role', 'user')->firstOrFail());
        $room = Room::where('name', 'Selat Malaka')->firstOrFail();
        $room->update(['description' => '<script>alert("room")</script>']);
        $inactive = Room::where('code', 'DEMO-03')->firstOrFail();
        $inactive->update(['is_active' => false]);

        $this->get('/dashboard')->assertOk()->assertSeeLivewire(RoomCatalog::class)
            ->assertSee($room->name)->assertSee($room->description)
            ->assertDontSee($room->description, false)->assertDontSee($inactive->name)
            ->assertSee('Projector')->assertSee('Kapasitas 20 peserta')
            ->assertSee('Dalam perawatan')->assertSee('Booking belum tersedia');
    }

    public function test_filters_partition_active_rooms_and_can_return_to_all(): void
    {
        $user = User::where('role', 'user')->firstOrFail();
        $component = Livewire::actingAs($user)->test(RoomCatalog::class)
            ->assertViewHas('rooms', fn ($rooms) => $rooms->total() === 4)
            ->call('selectFilter', 'featured')
            ->assertViewHas('rooms', fn ($rooms) => $rooms->count() === 1 && $rooms->first()->name === 'Selat Malaka')
            ->assertDontSee('Ruang Demo Terbatas')
            ->call('selectFilter', 'other')
            ->assertViewHas('rooms', fn ($rooms) => $rooms->total() === 3 && ! $rooms->contains('name', 'Selat Malaka'))
            ->call('selectFilter', 'all')->assertViewHas('rooms', fn ($rooms) => $rooms->total() === 4);

        Room::query()->update(['is_active' => false]);
        $component->call('selectFilter', 'featured')->assertSee('Belum ada ruangan pada pilihan ini');
    }

    public function test_pagination_resets_when_switching_filter(): void
    {
        $template = Room::firstOrFail();
        foreach (range(1, 10) as $number) {
            $room = $template->replicate();
            $room->fill(['name' => 'Test Room '.$number, 'code' => 'TEST-'.$number])->save();
        }

        Livewire::actingAs(User::where('role', 'user')->firstOrFail())->test(RoomCatalog::class)
            ->assertViewHas('rooms', fn ($rooms) => $rooms->count() === 9)
            ->call('nextPage', 'roomsPage')
            ->assertViewHas('rooms', fn ($rooms) => $rooms->currentPage() === 2)
            ->call('selectFilter', 'featured')
            ->assertViewHas('rooms', fn ($rooms) => $rooms->currentPage() === 1 && $rooms->total() === 1);
    }

    public function test_invalid_filter_is_rejected(): void
    {
        Livewire::actingAs(User::where('role', 'user')->firstOrFail())->test(RoomCatalog::class)
            ->call('selectFilter', 'invalid')->assertStatus(422);
    }

    public function test_filter_cannot_be_overwritten_directly(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::actingAs(User::where('role', 'user')->firstOrFail())->test(RoomCatalog::class)
            ->set('filter', 'invalid');
    }

    public function test_authorization_applies_on_initial_and_subsequent_requests(): void
    {
        Livewire::test(RoomCatalog::class)->assertForbidden();
        Livewire::actingAs(User::where('role', 'super_admin')->firstOrFail())->test(RoomCatalog::class)->assertForbidden();

        $user = User::where('role', 'user')->firstOrFail();
        $component = Livewire::actingAs($user)->test(RoomCatalog::class);
        $user->is_active = false;
        $user->save();
        $component->call('selectFilter', 'other')->assertForbidden();
    }
}
