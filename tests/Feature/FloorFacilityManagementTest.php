<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Facility;
use App\Models\Floor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\PostgresTestCase;

class FloorFacilityManagementTest extends PostgresTestCase
{
    public static function modules(): array
    {
        return ['floor' => [Floor::class, 'floors'], 'facility' => [Facility::class, 'facilities']];
    }

    private function admin(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
    }

    private function data(string $module, array $overrides = []): array
    {
        $data = ['name' => 'Data Baru '.$module, 'description' => 'Deskripsi dari form', 'is_active' => '1'];
        if ($module === 'floors') {
            $data['floor_number'] = 90;
        }

        return array_replace($data, $overrides);
    }

    /** @dataProvider modules */
    public function test_all_endpoints_require_authentication_and_super_admin(string $model, string $module): void
    {
        $record = $model::firstOrFail();
        $original = $record->getAttributes();
        $count = $model::count();
        $endpoints = [
            ['GET', 'index', null], ['GET', 'create', null], ['POST', 'store', null],
            ['GET', 'show', $record], ['GET', 'edit', $record], ['PUT', 'update', $record],
            ['PATCH', 'update', $record], ['PATCH', 'status', $record],
        ];
        foreach ($endpoints as [$method, $action, $item]) {
            $this->call($method, route('admin.'.$module.'.'.$action, $item), $this->data($module))->assertRedirect(route('login'));
        }
        foreach (['user', 'room_pic'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            foreach ($endpoints as [$method, $action, $item]) {
                $this->call($method, route('admin.'.$module.'.'.$action, $item), $this->data($module))->assertForbidden();
            }
        }
        $this->assertSame($original, $record->fresh()->getAttributes());
        $this->assertSame($count, $model::count());
    }

    /** @dataProvider modules */
    public function test_create_persists_and_renders_postgresql_data(string $model, string $module): void
    {
        $this->admin();
        $this->get(route('admin.'.$module.'.create'))->assertOk()
            ->assertSee(route('admin.'.$module.'.store'), false)->assertSee('name="_token"', false)
            ->assertSee('type="submit"', false)->assertDontSee('event.preventDefault()');
        $data = $this->data($module);
        $this->post(route('admin.'.$module.'.store'), $data)->assertSessionHasNoErrors()->assertSessionHas('status');
        $record = $model::where('name', $data['name'])->firstOrFail();
        $this->assertDatabaseHas($module, array_replace($data, ['id' => $record->id, 'is_active' => true]));
        $this->get(route('admin.'.$module.'.show', $record))->assertOk()->assertSee($data['name'])
            ->assertSee($data['description'])->assertSee('Status Aktif: Aktif')->assertSee('berhasil ditambahkan.');
        $index = $this->get(route('admin.'.$module.'.index'))->assertOk()->assertSee($data['name'])->assertSee($data['description']);
        if ($module === 'floors') {
            $index->assertSee('90');
        }
    }

    /** @dataProvider modules */
    public function test_edit_prefills_and_updates_fields_and_nullable_description(string $model, string $module): void
    {
        $this->admin();
        $record = $model::firstOrFail();
        $this->get(route('admin.'.$module.'.edit', $record))->assertOk()->assertSee('value="'.$record->name.'"', false)
            ->assertSee(route('admin.'.$module.'.update', $record), false)->assertSee('value="PUT"', false);
        $data = $this->data($module, ['name' => 'Data Diperbarui', 'is_active' => 0]);
        $this->put(route('admin.'.$module.'.update', $record), $data)
            ->assertRedirect(route('admin.'.$module.'.show', $record))->assertSessionHasNoErrors();
        $this->assertDatabaseHas($module, array_replace($data, ['id' => $record->id, 'is_active' => false]));
        $this->get(route('admin.'.$module.'.show', $record))->assertSee('Data Diperbarui')->assertSee('Status Aktif: Nonaktif')
            ->assertSee('berhasil diperbarui.')->assertSee('Deskripsi dari form');
        $this->get(route('admin.'.$module.'.index'))->assertSee('Data Diperbarui');
        // Keeping the same unique value is valid; an empty description clears existing text.
        $this->patch(route('admin.'.$module.'.update', $record), array_replace($data, ['description' => '']))->assertSessionHasNoErrors();
        $this->assertNull($record->fresh()->description);
    }

    /** @dataProvider modules */
    public function test_validation_rejects_bad_fields_and_duplicates_without_changing_records(string $model, string $module): void
    {
        $this->admin();
        $record = $model::firstOrFail();
        $other = $model::whereKeyNot($record->id)->firstOrFail();
        $unique = $module === 'floors' ? 'floor_number' : 'name';
        $count = $model::count();
        $invalid = [['name', ''], ['name', str_repeat('a', 256)], ['description', ['not text']],
            ['description', str_repeat('a', 5001)], ['is_active', 'bad'], ['is_active', null], [$unique, $record->$unique]];
        if ($module === 'floors') {
            $invalid = array_merge($invalid, [['floor_number', null], ['floor_number', 'abc'],
                ['floor_number', 1.5], ['floor_number', 2147483648], ['floor_number', -2147483649]]);
        }
        foreach ($invalid as [$field, $value]) {
            $this->from(route('admin.'.$module.'.create'))->post(route('admin.'.$module.'.store'), $this->data($module, [$field => $value]))
                ->assertRedirect(route('admin.'.$module.'.create'))->assertSessionHasErrors($field);
        }
        $this->get(route('admin.'.$module.'.create'))->assertSee('role="alert"', false)->assertSee('Deskripsi dari form');
        $original = $record->getAttributes();
        $this->from(route('admin.'.$module.'.edit', $record))->put(route('admin.'.$module.'.update', $record), $this->data($module, [$unique => $other->$unique]))
            ->assertRedirect(route('admin.'.$module.'.edit', $record))->assertSessionHasErrors($unique);
        $this->get(route('admin.'.$module.'.edit', $record))->assertSee('role="alert"', false);
        $this->patch(route('admin.'.$module.'.status', $record), ['is_active' => 'bad'])->assertSessionHasErrors('is_active');
        $this->assertSame($original, $record->fresh()->getAttributes());
        $this->assertSame($count, $model::count());
    }

    /** @dataProvider modules */
    public function test_status_and_edit_preserve_rooms_relationships_and_other_existing_data(string $model, string $module): void
    {
        $this->admin();
        $record = $model::has('rooms')->firstOrFail();
        $roomIds = $record->rooms()->orderBy('rooms.id')->pluck('rooms.id')->all();
        $rooms = DB::table('rooms')->orderBy('id')->get()->toArray();
        $pivots = DB::table('facility_room')->orderBy('room_id')->orderBy('facility_id')->get()->toArray();
        $otherRecords = $model::whereKeyNot($record->id)->orderBy('id')->get()->toArray();
        $this->assertNotEmpty($roomIds);
        foreach ([false, true] as $active) {
            $this->patch(route('admin.'.$module.'.status', $record), ['is_active' => (int) $active, 'name' => 'Must be ignored', 'room_ids' => []])
                ->assertRedirect(route('admin.'.$module.'.show', $record))->assertSessionHasNoErrors()->assertSessionHas('status');
            $this->assertDatabaseHas($module, ['id' => $record->id, 'is_active' => $active, 'name' => $record->name]);
            $this->get(route('admin.'.$module.'.show', $record))->assertSee('Status Aktif: '.($active ? 'Aktif' : 'Nonaktif'));
            $this->get(route('admin.'.$module.'.index'))->assertSee($record->name)->assertSee($active ? 'Nonaktifkan' : 'Aktifkan');
            $this->assertSame($roomIds, $record->rooms()->orderBy('rooms.id')->pluck('rooms.id')->all());
        }
        $this->put(route('admin.'.$module.'.update', $record), $this->data($module))->assertSessionHasNoErrors();
        $this->delete(route('admin.'.$module.'.show', $record))->assertStatus(405);
        $this->assertModelExists($record);
        $this->assertSame($roomIds, $record->rooms()->orderBy('rooms.id')->pluck('rooms.id')->all());
        $this->assertEquals($rooms, DB::table('rooms')->orderBy('id')->get()->toArray());
        $this->assertEquals($pivots, DB::table('facility_room')->orderBy('room_id')->orderBy('facility_id')->get()->toArray());
        $this->assertSame($otherRecords, $model::whereKeyNot($record->id)->orderBy('id')->get()->toArray());
    }

    /** @dataProvider modules */
    public function test_missing_records_escaping_and_csrf_protection(string $model, string $module): void
    {
        $this->admin();
        $this->get('/admin/'.$module.'/preview')->assertNotFound();
        $this->get('/admin/'.$module.'/999999999')->assertNotFound();
        $this->get('/admin/'.$module.'/999999999/edit')->assertNotFound();
        $this->put('/admin/'.$module.'/999999999', $this->data($module))->assertNotFound();
        $this->patch('/admin/'.$module.'/999999999/status', ['is_active' => 0])->assertNotFound();
        $record = $model::create($this->data($module, ['name' => '<script>alert(1)</script>', 'description' => '<b>Untrusted</b>']));
        foreach (['index', 'show', 'edit'] as $page) {
            $this->get(route('admin.'.$module.'.'.$page, $page === 'index' ? [] : $record))->assertOk()
                ->assertSee($record->name)->assertDontSee($record->name, false)
                ->assertSee($record->description)->assertDontSee($record->description, false);
        }
        $this->app->bind(VerifyCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends VerifyCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });
        $this->post(route('admin.'.$module.'.store'), $this->data($module))->assertStatus(419);
        $this->put(route('admin.'.$module.'.update', $record), $this->data($module))->assertStatus(419);
        $this->patch(route('admin.'.$module.'.status', $record), ['is_active' => 0])->assertStatus(419);
        $this->assertTrue($record->fresh()->is_active);
    }
}
