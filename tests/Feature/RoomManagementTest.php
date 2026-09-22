<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Facility;
use App\Models\Floor;
use App\Models\Room;
use App\Models\User;
use App\Services\RoomService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\PostgresTestCase;

class RoomManagementTest extends PostgresTestCase
{
    private function admin(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
    }

    private function url(string $action, ?Room $room = null): string
    {
        return route('admin.rooms.'.$action, $room);
    }

    private function data(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Ruang Pengujian', 'code' => 'TEST-ROOM', 'floor_id' => Floor::firstOrFail()->id,
            'capacity' => 15, 'description' => 'Deskripsi pengujian', 'access_type' => 'restricted',
            'requires_approval' => '1', 'status' => 'available', 'is_active' => '1',
            'facility_ids' => Facility::orderBy('id')->limit(2)->pluck('id')->all(),
        ], $overrides);
    }

    public function test_all_management_endpoints_require_authentication_and_super_admin(): void
    {
        $room = Room::firstOrFail();
        $original = $room->getAttributes();
        $count = Room::count();
        $pivot = DB::table('facility_room')->orderBy('room_id')->orderBy('facility_id')->get()->toArray();
        $endpoints = [['GET', 'index', null], ['GET', 'create', null], ['POST', 'store', null],
            ['GET', 'show', $room], ['GET', 'edit', $room], ['PUT', 'update', $room],
            ['PATCH', 'update', $room], ['PATCH', 'status', $room]];
        foreach ($endpoints as [$method, $action, $record]) {
            $this->call($method, $this->url($action, $record), $this->data())->assertRedirect(route('login'));
        }
        foreach (['user', 'room_pic'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            foreach ($endpoints as [$method, $action, $record]) {
                $this->call($method, $this->url($action, $record), $this->data())->assertForbidden();
            }
        }
        $this->assertSame($original, $room->fresh()->getAttributes());
        $this->assertSame($count, Room::count());
        $this->assertEquals($pivot, DB::table('facility_room')->orderBy('room_id')->orderBy('facility_id')->get()->toArray());
    }

    public function test_create_persists_room_and_facilities_and_renders_database_fields(): void
    {
        $this->admin();
        $floor = Floor::create(['name' => 'Lantai dari Database', 'floor_number' => 50, 'is_active' => true]);
        $facility = Facility::create(['name' => 'Fasilitas dari Database', 'is_active' => true]);
        $this->get($this->url('create'))->assertOk()->assertSee($floor->name)->assertSee($facility->name)
            ->assertSee($this->url('store'), false)->assertSee('name="_token"', false)->assertDontSee('event.preventDefault()');
        $data = $this->data(['floor_id' => $floor->id, 'facility_ids' => [$facility->id]]);
        $this->post($this->url('store'), $data)->assertSessionHasNoErrors()->assertSessionHas('status');
        $room = Room::where('code', 'TEST-ROOM')->firstOrFail();
        unset($data['facility_ids']);
        $this->assertDatabaseHas('rooms', array_replace($data, ['id' => $room->id, 'is_active' => true, 'requires_approval' => true]));
        $this->assertDatabaseHas('facility_room', ['room_id' => $room->id, 'facility_id' => $facility->id]);
        $this->get($this->url('show', $room))->assertOk()->assertSee('Ruangan berhasil ditambahkan.')
            ->assertSee([$room->name, $room->code, $floor->name, '15', $room->description, 'restricted', 'Requires Approval: Ya', 'available', 'Status Aktif: Aktif', $facility->name]);
        $this->get($this->url('index'))->assertOk()->assertSee([$room->name, $room->code, $floor->name, '15', 'restricted', 'Ya', 'available', 'Aktif', $facility->name]);
    }

    public function test_edit_prefills_and_updates_fields_and_syncs_or_clears_facilities(): void
    {
        $this->admin();
        $room = Room::has('facilities')->firstOrFail();
        $existing = $room->facilities()->firstOrFail();
        $floor = Floor::whereKeyNot($room->floor_id)->firstOrFail();
        $replacement = Facility::whereNotIn('id', $room->facilities()->pluck('facilities.id'))->firstOrFail();
        $this->get($this->url('edit', $room))->assertOk()->assertSee('value="'.$room->name.'"', false)
            ->assertSee('value="'.$room->floor_id.'" selected', false)
            ->assertSee('value="'.$existing->id.'" checked', false)->assertSee($this->url('update', $room), false);
        $data = $this->data(['name' => 'Ruang Diperbarui', 'floor_id' => $floor->id, 'capacity' => 0,
            'access_type' => 'all', 'requires_approval' => 0, 'status' => 'maintenance', 'is_active' => 0,
            'facility_ids' => [$replacement->id]]);
        $this->put($this->url('update', $room), $data)->assertRedirect($this->url('show', $room))->assertSessionHasNoErrors();
        $this->assertSame([$replacement->id], $room->facilities()->pluck('facilities.id')->all());
        $this->assertDatabaseMissing('facility_room', ['room_id' => $room->id, 'facility_id' => $existing->id]);
        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'name' => 'Ruang Diperbarui', 'code' => 'TEST-ROOM',
            'floor_id' => $floor->id, 'capacity' => 0, 'access_type' => 'all', 'requires_approval' => false, 'status' => 'maintenance', 'is_active' => false]);
        $this->get($this->url('show', $room))->assertSee(['Ruang Diperbarui', $floor->name, $replacement->name, 'Requires Approval: Tidak', 'maintenance', 'Status Aktif: Nonaktif', 'berhasil diperbarui.']);
        $this->get($this->url('index'))->assertSee(['Ruang Diperbarui', $replacement->name, 'maintenance']);
        // No checked checkbox means the browser omits facility_ids entirely.
        unset($data['facility_ids']);
        $data['code'] = '';
        $data['description'] = '';
        $this->patch($this->url('update', $room), $data)->assertSessionHasNoErrors();
        $this->assertSame(0, $room->facilities()->count());
        $this->assertNull($room->fresh()->code);
        $this->assertNull($room->fresh()->description);
        $this->get($this->url('show', $room))->assertSee('Belum ada fasilitas.');
    }

    public function test_validation_rejects_invalid_fields_and_unique_codes_without_writes(): void
    {
        $this->admin();
        $room = Room::firstOrFail();
        $count = Room::count();
        $facility = Facility::firstOrFail();
        foreach ([['name', ''], ['name', str_repeat('a', 256)], ['code', str_repeat('a', 256)], ['code', $room->code],
            ['floor_id', null], ['floor_id', 999999999], ['capacity', -1], ['capacity', 1.5], ['capacity', 2147483648],
            ['description', str_repeat('a', 5001)], ['access_type', 'public'], ['status', 'broken'],
            ['requires_approval', 'yes'], ['is_active', 'yes'], ['facility_ids', 'bad'], ['facility_ids', null]] as [$field, $value]) {
            $this->from($this->url('create'))->post($this->url('store'), $this->data([$field => $value]))
                ->assertRedirect($this->url('create'))->assertSessionHasErrors($field);
        }
        foreach ([[999999999], ['bad'], [$facility->id, $facility->id]] as $ids) {
            $this->post($this->url('store'), $this->data(['facility_ids' => $ids]))->assertSessionHasErrors('facility_ids.0');
        }
        $this->assertSame($count, Room::count());
        $original = $room->getAttributes();
        $this->put($this->url('update', $room), $this->data(['capacity' => -1]))->assertSessionHasErrors('capacity');
        $other = Room::whereKeyNot($room->id)->whereNotNull('code')->firstOrFail();
        $this->put($this->url('update', $room), $this->data(['code' => $other->code]))->assertSessionHasErrors('code');
        $this->assertSame($original, $room->fresh()->getAttributes());
        $this->put($this->url('update', $room), $this->data(['code' => $room->code]))->assertSessionHasNoErrors();
    }

    public function test_validation_errors_preserve_empty_selection_and_handle_invalid_array_input(): void
    {
        $this->admin();
        $room = Room::has('facilities')->firstOrFail();
        $data = $this->data(['name' => '', 'facility_ids' => []]);
        unset($data['facility_ids']);
        $this->from($this->url('edit', $room))->put($this->url('update', $room), $data)->assertSessionHasErrors('name');
        $this->get($this->url('edit', $room))->assertOk()->assertSee('role="alert"', false)->assertDontSee(' checked', false);
        $this->from($this->url('edit', $room))->put($this->url('update', $room), $this->data(['facility_ids' => 'invalid']))
            ->assertSessionHasErrors('facility_ids');
        $this->get($this->url('edit', $room))->assertOk()->assertSee('role="alert"', false);
    }

    public function test_nullable_codes_and_empty_facilities_can_be_saved(): void
    {
        $this->admin();
        foreach (['Ruang Tanpa Kode A', 'Ruang Tanpa Kode B'] as $name) {
            $this->post($this->url('store'), $this->data(['name' => $name, 'code' => '', 'facility_ids' => []]))->assertSessionHasNoErrors();
            $room = Room::where('name', $name)->firstOrFail();
            $this->assertNull($room->code);
            $this->assertSame(0, $room->facilities()->count());
        }
    }

    public function test_operational_and_active_status_preserve_other_relationships(): void
    {
        $this->admin();
        $room = Room::has('pics')->has('allowedOrganizationalUnits')->firstOrFail();
        $pics = DB::table('room_pics')->orderBy('room_id')->orderBy('user_id')->get()->toArray();
        $units = DB::table('room_unit_access')->orderBy('room_id')->orderBy('organizational_unit_id')->get()->toArray();
        $others = Room::whereKeyNot($room->id)->orderBy('id')->get()->toArray();
        $facilities = $room->facilities()->pluck('facilities.id')->all();
        foreach (['maintenance', 'unavailable', 'available'] as $status) {
            $this->put($this->url('update', $room), $this->data(['status' => $status, 'access_type' => 'all',
                'facility_ids' => $facilities, 'pic_ids' => [], 'organizational_unit_ids' => []]))->assertSessionHasNoErrors();
            $this->assertSame($status, $room->fresh()->status);
            $this->get($this->url('show', $room))->assertSee($status);
            $this->get($this->url('index'))->assertSee($status);
        }
        foreach ([false, true] as $active) {
            $this->patch($this->url('status', $room), ['is_active' => (int) $active, 'status' => 'unavailable', 'facility_ids' => []])
                ->assertRedirect($this->url('show', $room))->assertSessionHasNoErrors();
            $this->assertDatabaseHas('rooms', ['id' => $room->id, 'is_active' => $active, 'status' => 'available']);
            $this->get($this->url('show', $room))->assertSee('Status Aktif: '.($active ? 'Aktif' : 'Nonaktif'));
            $this->get($this->url('index'))->assertSee($active ? 'Nonaktifkan' : 'Aktifkan');
        }
        $this->patch($this->url('status', $room), ['is_active' => 'bad'])->assertSessionHasErrors('is_active');
        $this->delete($this->url('show', $room))->assertStatus(405);
        $this->assertModelExists($room);
        $this->assertEquals($facilities, $room->facilities()->pluck('facilities.id')->all());
        $this->assertEquals($pics, DB::table('room_pics')->orderBy('room_id')->orderBy('user_id')->get()->toArray());
        $this->assertEquals($units, DB::table('room_unit_access')->orderBy('room_id')->orderBy('organizational_unit_id')->get()->toArray());
        $this->assertSame($others, Room::whereKeyNot($room->id)->orderBy('id')->get()->toArray());
    }

    public function test_transaction_rolls_back_room_and_pivot_when_sync_fails(): void
    {
        $room = Room::has('facilities')->firstOrFail();
        $original = $room->getAttributes();
        $ids = $room->facilities()->orderBy('facilities.id')->pluck('facilities.id')->all();
        $count = Room::count();
        foreach ([null, $room] as $record) {
            try {
                // Simulate a facility disappearing after HTTP validation; PostgreSQL rejects the FK.
                app(RoomService::class)->save($this->data(['facility_ids' => [999999999]]), $record);
                $this->fail('Expected a foreign key failure.');
            } catch (QueryException $exception) {
                $this->assertSame('23503', $exception->getCode());
            }
            $this->assertSame($count, Room::count());
            $this->assertSame($original, $room->fresh()->getAttributes());
            $this->assertSame($ids, $room->facilities()->orderBy('facilities.id')->pluck('facilities.id')->all());
        }
    }

    public function test_missing_records_escaping_and_csrf(): void
    {
        $this->admin();
        $this->get('/admin/rooms/preview')->assertNotFound();
        $this->get('/admin/rooms/999999999')->assertNotFound();
        $this->get('/admin/rooms/999999999/edit')->assertNotFound();
        $this->put('/admin/rooms/999999999', $this->data())->assertNotFound();
        $this->patch('/admin/rooms/999999999/status', ['is_active' => 0])->assertNotFound();
        $this->post($this->url('store'), $this->data(['name' => '<script>alert(1)</script>']))->assertSessionHasNoErrors();
        $room = Room::where('code', 'TEST-ROOM')->firstOrFail();
        foreach (['index', 'show', 'edit'] as $page) {
            $this->get($this->url($page, $page === 'index' ? null : $room))->assertOk()->assertSee($room->name)->assertDontSee($room->name, false);
        }
        $this->app->bind(VerifyCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends VerifyCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });
        $this->post($this->url('store'), $this->data())->assertStatus(419);
        $this->put($this->url('update', $room), $this->data())->assertStatus(419);
        $this->patch($this->url('status', $room), ['is_active' => 0])->assertStatus(419);
    }
}
