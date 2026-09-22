<?php

namespace Tests\Feature;

use App\Models\OrganizationalUnit;
use App\Models\User;
use Tests\PostgresTestCase;

class OrganizationalUnitTest extends PostgresTestCase
{
    private function admin(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
    }

    private function url(string $action, ?OrganizationalUnit $unit = null): string
    {
        return route('admin.organizational-units.'.$action, $unit);
    }

    private function data(string $type = 'directorate', ?int $parent = null): array
    {
        return ['name' => 'Unit Pengujian', 'type' => $type, 'parent_id' => $parent, 'is_active' => '1'];
    }

    public function test_all_endpoints_require_authentication_and_super_admin(): void
    {
        $unit = OrganizationalUnit::firstOrFail();
        $endpoints = [
            ['GET', 'index', null], ['GET', 'create', null], ['POST', 'store', null],
            ['GET', 'show', $unit], ['GET', 'edit', $unit], ['PUT', 'update', $unit],
            ['PATCH', 'update', $unit], ['PATCH', 'status', $unit],
        ];
        foreach ($endpoints as [$method, $action, $record]) {
            $this->call($method, $this->url($action, $record), $this->data())->assertRedirect(route('login'));
        }
        foreach (['user', 'room_pic'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            foreach ($endpoints as [$method, $action, $record]) {
                $this->call($method, $this->url($action, $record), $this->data())->assertForbidden();
            }
        }
        $this->assertDatabaseMissing('organizational_units', ['name' => 'Unit Pengujian']);
    }

    public function test_create_each_level_persists_and_renders_database_data(): void
    {
        $this->admin();
        $parent = null;
        foreach (['directorate', 'division', 'department'] as $type) {
            $data = array_replace($this->data($type, $parent?->id), ['name' => 'Baru '.$type]);
            $this->get($this->url('create'))->assertOk()->assertSee('name="_token"', false)
                ->assertSee('type="submit"', false)->assertDontSee('event.preventDefault()');
            $this->post($this->url('store'), $data)->assertSessionHasNoErrors()->assertSessionHas('status');
            $unit = OrganizationalUnit::where('name', $data['name'])->firstOrFail();
            $this->assertDatabaseHas('organizational_units', array_replace($data, ['id' => $unit->id, 'is_active' => true]));
            $this->get($this->url('show', $unit))->assertOk()->assertSee($unit->name)->assertSee('Aktif');
            $this->get($this->url('index'))->assertOk()->assertSee($unit->name);
            if ($parent) {
                $this->get($this->url('show', $unit))->assertSee($parent->name);
                $this->get($this->url('show', $parent))->assertSee($unit->name);
            }
            if ($type !== 'department') {
                $this->get($this->url('create'))->assertSee('value="'.$unit->id.'"', false)->assertSee($unit->name);
            }
            $parent = $unit;
        }
    }

    public function test_invalid_parents_and_basic_fields_are_rejected_without_writes(): void
    {
        $this->admin();
        $directorate = OrganizationalUnit::where('type', 'directorate')->firstOrFail();
        $division = OrganizationalUnit::where('type', 'division')->firstOrFail();
        $department = OrganizationalUnit::where('type', 'department')->firstOrFail();
        $count = OrganizationalUnit::count();
        foreach ([['directorate', $directorate->id], ['division', null], ['department', null],
            ['division', $division->id], ['department', $directorate->id], ['division', $department->id],
            ['department', $department->id], ['division', 999999999]] as [$type, $parent]) {
            $this->from($this->url('create'))->post($this->url('store'), $this->data($type, $parent))
                ->assertRedirect($this->url('create'))->assertSessionHasErrors('parent_id');
        }
        foreach ([['name', ''], ['name', str_repeat('a', 256)], ['type', 'invalid'], ['is_active', 'invalid'], ['parent_id', 'abc']] as [$field, $value]) {
            $this->post($this->url('store'), array_replace($this->data(), [$field => $value]))->assertSessionHasErrors($field);
        }
        $this->assertSame($count, OrganizationalUnit::count());
    }

    public function test_self_parent_and_descendant_cycle_are_rejected(): void
    {
        $this->admin();
        $directorate = OrganizationalUnit::where('type', 'directorate')->firstOrFail();
        $division = $directorate->children()->firstOrFail();
        // Changing the proposed type makes the parent type valid, so cycle validation must reject it.
        $this->put($this->url('update', $directorate), $this->data('division', $directorate->id))
            ->assertSessionHasErrors('parent_id');
        $this->put($this->url('update', $directorate), $this->data('department', $division->id))
            ->assertSessionHasErrors('parent_id');
        $this->assertNull($directorate->fresh()->parent_id);
        $this->assertSame('directorate', $directorate->fresh()->type);
    }

    public function test_type_change_cannot_invalidate_existing_children(): void
    {
        $this->admin();
        $directorate = OrganizationalUnit::where('type', 'directorate')->firstOrFail();
        $other = OrganizationalUnit::create(array_replace($this->data(), ['name' => 'Parent Lain']));
        $division = $directorate->children()->firstOrFail();
        $this->put($this->url('update', $directorate), $this->data('division', $other->id))->assertSessionHasErrors('type');
        $this->put($this->url('update', $division), $this->data())->assertSessionHasErrors('type');
        $this->assertSame('directorate', $directorate->fresh()->type);
        $this->assertSame('division', $division->fresh()->type);
    }

    public function test_edit_prefills_data_and_update_moves_unit_and_persists(): void
    {
        $this->admin();
        $unit = OrganizationalUnit::where('type', 'division')->firstOrFail();
        $other = OrganizationalUnit::create(array_replace($this->data(), ['name' => 'Direktorat Tujuan']));
        $this->get($this->url('edit', $unit))->assertOk()->assertSee('value="'.$unit->name.'"', false)
            ->assertSee('value="'.$unit->parent_id.'" selected', false)->assertSee($this->url('update', $unit), false);
        $data = array_replace($this->data('division', $other->id), ['name' => 'Divisi Diperbarui']);
        $this->put($this->url('update', $unit), $data)->assertRedirect($this->url('show', $unit))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('organizational_units', ['id' => $unit->id, 'name' => $data['name'], 'parent_id' => $other->id]);
        $this->get($this->url('show', $unit))->assertSee($data['name'])->assertSee($other->name)->assertSee('berhasil diperbarui');
        $this->get($this->url('index'))->assertSee($data['name']);
        $leaf = OrganizationalUnit::create($this->data());
        $this->patch($this->url('update', $leaf), $this->data('division', $other->id))->assertSessionHasNoErrors();
        $this->assertSame('division', $leaf->fresh()->type);
        $data = $this->data();
        unset($data['parent_id']);
        $this->put($this->url('update', $leaf), $data)->assertSessionHasNoErrors();
        $this->assertSame('directorate', $leaf->fresh()->type);
        $this->assertNull($leaf->fresh()->parent_id);
    }

    public function test_status_changes_preserve_children_and_users_and_render_immediately(): void
    {
        $this->admin();
        $unit = OrganizationalUnit::where('type', 'division')->firstOrFail();
        $user = User::where('role', 'user')->firstOrFail();
        $user->organizational_unit_id = $unit->id;
        $user->save();
        $children = $unit->children()->pluck('id')->all();
        foreach ([false, true] as $active) {
            $this->patch($this->url('status', $unit), ['is_active' => (int) $active, 'name' => 'Ignored'])
                ->assertRedirect($this->url('show', $unit))->assertSessionHasNoErrors()->assertSessionHas('status');
            $this->assertSame($active, $unit->fresh()->is_active);
            $this->assertDatabaseHas('organizational_units', ['id' => $unit->id, 'is_active' => $active, 'name' => $unit->name]);
            $this->get($this->url('show', $unit))->assertSee('Status Aktif: '.($active ? 'Aktif' : 'Nonaktif'))
                ->assertSee($active ? 'Nonaktifkan' : 'Aktifkan');
            $this->get($this->url('index'))->assertSee($unit->name)->assertSee($active ? 'Nonaktifkan' : 'Aktifkan');
            $this->assertSame($children, $unit->children()->pluck('id')->all());
            $this->assertSame($unit->id, $user->fresh()->organizational_unit_id);
        }
        $this->patch($this->url('status', $unit), ['is_active' => 'bad'])->assertSessionHasErrors('is_active');
        $this->assertTrue($unit->fresh()->is_active);
        $this->delete($this->url('show', $unit))->assertStatus(405);
        $this->assertModelExists($unit);
    }

    public function test_invalid_update_preserves_data_and_displays_errors_and_old_input(): void
    {
        $this->admin();
        $unit = OrganizationalUnit::where('type', 'division')->firstOrFail();
        $original = $unit->getAttributes();
        $this->from($this->url('edit', $unit))->put($this->url('update', $unit), $this->data('division'))
            ->assertRedirect($this->url('edit', $unit))->assertSessionHasErrors('parent_id');
        $this->get($this->url('edit', $unit))->assertSee('Parent harus bertipe directorate.')
            ->assertSee('value="Unit Pengujian"', false)->assertSee('role="alert"', false);
        $this->assertSame($original, $unit->fresh()->getAttributes());
    }

    public function test_missing_records_return_404_and_names_are_escaped(): void
    {
        $this->admin();
        foreach (['GET' => '', 'PUT' => '', 'PATCH' => '/status'] as $method => $suffix) {
            $this->call($method, '/admin/organizational-units/999999999'.$suffix, $this->data())->assertNotFound();
        }
        $this->get('/admin/organizational-units/999999999/edit')->assertNotFound();
        $this->get('/admin/organizational-units/preview')->assertNotFound();
        $unit = OrganizationalUnit::create(array_replace($this->data(), ['name' => '<script>alert(1)</script>']));
        $this->get($this->url('show', $unit))->assertSee($unit->name)->assertDontSee($unit->name, false);
        $this->get($this->url('index'))->assertSee($unit->name)->assertDontSee($unit->name, false);
    }
}
