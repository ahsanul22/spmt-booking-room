<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\OrganizationalUnit;
use App\Models\Room;
use App\Models\User;
use App\Services\RoomAssignmentService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\PostgresTestCase;

class RoomAssignmentTest extends PostgresTestCase
{
    private function admin(): void
    {
        $this->actingAs(User::where('role', 'super_admin')->firstOrFail());
    }

    private function room(): Room
    {
        return Room::where('access_type', 'restricted')->firstOrFail();
    }

    private function url(string $action, Room $room, ?User $pic = null): string
    {
        return route('admin.rooms.'.$action, $pic ? [$room, $pic] : $room);
    }

    public function test_all_assignment_endpoints_require_authentication_and_super_admin(): void
    {
        $room = $this->room();
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $originalPics = $room->pics()->pluck('users.id')->all();
        $originalUnits = $room->allowedOrganizationalUnits()->pluck('organizational_units.id')->all();
        $endpoints = [['GET', 'pics', null], ['PUT', 'pics.update', null], ['DELETE', 'pics.destroy', $pic],
            ['GET', 'access', null], ['PUT', 'access.update', null]];
        foreach ($endpoints as [$method, $action, $user]) {
            $this->call($method, $this->url($action, $room, $user))->assertRedirect(route('login'));
        }
        foreach (['user', 'room_pic'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            foreach ($endpoints as [$method, $action, $user]) {
                $this->call($method, $this->url($action, $room, $user))->assertForbidden();
            }
        }
        $this->assertSame($originalPics, $room->pics()->pluck('users.id')->all());
        $this->assertSame($originalUnits, $room->allowedOrganizationalUnits()->pluck('organizational_units.id')->all());
    }

    public function test_multiple_pics_save_and_render_without_duplicates_or_role_changes(): void
    {
        $this->admin();
        $room = $this->room();
        $first = User::where('role', 'room_pic')->firstOrFail();
        $second = User::factory()->create(['name' => 'PIC Tambahan Database', 'role' => 'room_pic', 'is_active' => true]);
        $users = User::orderBy('id')->get()->toArray();
        $this->get($this->url('pics', $room))->assertOk()->assertSee($room->name)->assertSee($second->name)
            ->assertViewHas('eligiblePics', fn ($pics) => $pics->every(fn ($pic) => $pic->role === 'room_pic'))
            ->assertDontSee('event.preventDefault()');
        foreach ([1, 2] as $attempt) {
            $this->put($this->url('pics.update', $room), ['pic_ids' => [$first->id, $second->id], 'role' => 'super_admin'])
                ->assertRedirect($this->url('show', $room))->assertSessionHasNoErrors()->assertSessionHas('status');
            $this->assertSame(2, $room->pics()->count());
            foreach ([$first, $second] as $pic) {
                $this->assertDatabaseHas('room_pics', ['room_id' => $room->id, 'user_id' => $pic->id]);
            }
        }
        $this->get($this->url('show', $room))->assertSee([$first->name, $second->name, 'PIC ruangan berhasil diperbarui.']);
        $this->get($this->url('pics', $room))->assertSee('value="'.$second->id.'" selected', false);
        $this->assertSame($users, User::orderBy('id')->get()->toArray());
    }

    public function test_pic_validation_rejects_other_roles_missing_users_and_duplicate_ids(): void
    {
        $this->admin();
        $room = $this->room();
        $original = $room->pics()->pluck('users.id')->all();
        $pic = User::where('role', 'room_pic')->firstOrFail();
        foreach ([User::where('role', 'user')->firstOrFail()->id, User::where('role', 'super_admin')->firstOrFail()->id, 999999999, 'bad'] as $id) {
            $this->from($this->url('pics', $room))->put($this->url('pics.update', $room), ['pic_ids' => [$id]])
                ->assertRedirect($this->url('pics', $room))->assertSessionHasErrors('pic_ids.0');
        }
        $this->put($this->url('pics.update', $room), ['pic_ids' => [$pic->id, $pic->id]])->assertSessionHasErrors('pic_ids.0');
        $this->from($this->url('pics', $room))->put($this->url('pics.update', $room), ['pic_ids' => 'invalid'])->assertSessionHasErrors('pic_ids');
        $this->get($this->url('pics', $room))->assertOk()->assertSee('role="alert"', false);
        $this->assertSame($original, $room->pics()->pluck('users.id')->all());
    }

    public function test_remove_is_scoped_to_room_and_never_deletes_users_or_other_assignments(): void
    {
        $this->admin();
        $room = $this->room();
        $other = Room::whereKeyNot($room->id)->firstOrFail();
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $second = User::factory()->create(['role' => 'room_pic']);
        $this->put($this->url('pics.update', $room), ['pic_ids' => [$pic->id, $second->id]])->assertSessionHasNoErrors();
        $this->put($this->url('pics.update', $other), ['pic_ids' => [$pic->id]])->assertSessionHasNoErrors();
        $this->delete($this->url('pics.destroy', $other, $second))->assertNotFound();
        $this->assertDatabaseHas('room_pics', ['room_id' => $room->id, 'user_id' => $second->id]);
        // A previously assigned account can still be detached after its role has changed.
        $pic->role = 'user';
        $pic->save();
        $this->delete($this->url('pics.destroy', $room, $pic))->assertRedirect($this->url('show', $room))->assertSessionHas('status');
        $this->assertDatabaseMissing('room_pics', ['room_id' => $room->id, 'user_id' => $pic->id]);
        $this->assertDatabaseHas('room_pics', ['room_id' => $other->id, 'user_id' => $pic->id]);
        $this->assertModelExists($pic);
        $this->assertSame('user', $pic->fresh()->role);
        $this->get($this->url('show', $room))->assertSee('PIC berhasil dilepas dari ruangan.')->assertSee($second->name);
        $this->put($this->url('pics.update', $room), [])->assertSessionHasNoErrors();
        $this->assertSame(0, $room->pics()->count());
        $this->assertModelExists($second);
    }

    public function test_restricted_units_sync_render_and_store_only_selected_units(): void
    {
        $this->admin();
        $room = $this->room();
        $directorate = OrganizationalUnit::where('type', 'directorate')->has('children')->firstOrFail();
        $department = OrganizationalUnit::where('type', 'department')->firstOrFail();
        $units = OrganizationalUnit::orderBy('id')->get()->toArray();
        $this->get($this->url('access', $room))->assertOk()->assertSee($directorate->name)->assertSee($department->name);
        foreach ([1, 2] as $attempt) {
            $this->put($this->url('access.update', $room), ['organizational_unit_ids' => [$directorate->id, $department->id]])
                ->assertRedirect($this->url('show', $room))->assertSessionHasNoErrors()->assertSessionHas('status');
            $this->assertSame(2, $room->allowedOrganizationalUnits()->count());
        }
        $this->assertDatabaseHas('room_unit_access', ['room_id' => $room->id, 'organizational_unit_id' => $directorate->id]);
        $this->assertDatabaseMissing('room_unit_access', ['room_id' => $room->id, 'organizational_unit_id' => $directorate->children()->firstOrFail()->id]);
        $this->get($this->url('show', $room))->assertSee([$directorate->name, $department->name, 'Unit akses ruangan berhasil diperbarui.']);
        $this->get($this->url('access', $room))->assertSee('value="'.$directorate->id.'" checked', false);
        $this->put($this->url('access.update', $room), ['organizational_unit_ids' => [$department->id]])->assertSessionHasNoErrors();
        $this->assertSame([$department->id], $room->allowedOrganizationalUnits()->pluck('organizational_units.id')->all());
        $this->put($this->url('access.update', $room), [])->assertSessionHasNoErrors();
        $this->assertSame(0, $room->allowedOrganizationalUnits()->count());
        $this->get($this->url('show', $room))->assertSee('Belum ada unit akses.');
        $this->assertSame($units, OrganizationalUnit::orderBy('id')->get()->toArray());
    }

    public function test_invalid_units_and_all_access_cannot_modify_restricted_list(): void
    {
        $this->admin();
        $room = $this->room();
        $original = $room->allowedOrganizationalUnits()->pluck('organizational_units.id')->all();
        $unit = OrganizationalUnit::firstOrFail();
        foreach ([[999999999], ['bad'], [$unit->id, $unit->id]] as $ids) {
            $this->put($this->url('access.update', $room), ['organizational_unit_ids' => $ids])->assertSessionHasErrors('organizational_unit_ids.0');
        }
        $this->from($this->url('access', $room))->put($this->url('access.update', $room), ['organizational_unit_ids' => 'bad'])
            ->assertSessionHasErrors('organizational_unit_ids');
        $this->get($this->url('access', $room))->assertOk()->assertSee('role="alert"', false);
        $room->update(['access_type' => 'all']);
        $this->put($this->url('access.update', $room), ['organizational_unit_ids' => [$unit->id]])->assertSessionHasErrors('organizational_unit_ids');
        $this->get($this->url('access', $room))->assertSee('Daftar unit tersimpan di bawah tidak digunakan.')
            ->assertDontSee('name="organizational_unit_ids[]"', false)->assertDontSee('Simpan Unit Akses');
        $this->get($this->url('show', $room))->assertSee('Akses terbuka untuk semua unit.');
        $this->assertSame($original, $room->allowedOrganizationalUnits()->pluck('organizational_units.id')->all());
        $room->update(['access_type' => 'restricted']);
        $this->get($this->url('access', $room))->assertSee('name="organizational_unit_ids[]"', false);
    }

    public function test_assignment_operations_preserve_room_fields_facilities_and_other_records(): void
    {
        $this->admin();
        $room = $this->room();
        $rooms = Room::orderBy('id')->get()->toArray();
        $facilities = DB::table('facility_room')->orderBy('room_id')->orderBy('facility_id')->get()->toArray();
        $otherPics = DB::table('room_pics')->where('room_id', '!=', $room->id)->orderBy('room_id')->orderBy('user_id')->get()->toArray();
        $otherUnits = DB::table('room_unit_access')->where('room_id', '!=', $room->id)->orderBy('room_id')->orderBy('organizational_unit_id')->get()->toArray();
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $this->put($this->url('pics.update', $room), ['pic_ids' => [$pic->id], 'access_type' => 'all'])->assertSessionHasNoErrors();
        $this->put($this->url('access.update', $room), ['organizational_unit_ids' => [OrganizationalUnit::firstOrFail()->id], 'name' => 'Ignored'])->assertSessionHasNoErrors();
        $this->assertSame($rooms, Room::orderBy('id')->get()->toArray());
        $this->assertEquals($facilities, DB::table('facility_room')->orderBy('room_id')->orderBy('facility_id')->get()->toArray());
        $this->assertEquals($otherPics, DB::table('room_pics')->where('room_id', '!=', $room->id)->orderBy('room_id')->orderBy('user_id')->get()->toArray());
        $this->assertEquals($otherUnits, DB::table('room_unit_access')->where('room_id', '!=', $room->id)->orderBy('room_id')->orderBy('organizational_unit_id')->get()->toArray());
    }

    public function test_service_rechecks_pic_roles_and_rolls_back_failed_unit_sync(): void
    {
        $room = $this->room();
        $pic = User::where('role', 'room_pic')->firstOrFail();
        $originalPics = $room->pics()->pluck('users.id')->all();
        $originalUnits = $room->allowedOrganizationalUnits()->pluck('organizational_units.id')->all();
        $pic->role = 'super_admin';
        $pic->save();
        try {
            app(RoomAssignmentService::class)->syncPics($room, [$pic->id]);
            $this->fail('Expected role revalidation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('pic_ids', $exception->errors());
        }
        try {
            app(RoomAssignmentService::class)->syncUnits($room, [999999999]);
            $this->fail('Expected foreign key failure.');
        } catch (QueryException $exception) {
            $this->assertSame('23503', $exception->getCode());
        }
        $this->assertSame($originalPics, $room->pics()->pluck('users.id')->all());
        $this->assertSame($originalUnits, $room->allowedOrganizationalUnits()->pluck('organizational_units.id')->all());
    }

    public function test_missing_records_escaping_and_csrf_protection(): void
    {
        $this->admin();
        $room = $this->room();
        foreach (['pics', 'access'] as $path) {
            $this->get('/admin/rooms/999999999/'.$path)->assertNotFound();
            $this->put('/admin/rooms/999999999/'.$path)->assertNotFound();
            $this->get('/admin/rooms/preview/'.$path)->assertNotFound();
        }
        $this->delete('/admin/rooms/'.$room->id.'/pics/999999999')->assertNotFound();
        $pic = User::factory()->create(['name' => '<script>alert(1)</script>', 'role' => 'room_pic']);
        $this->get($this->url('pics', $room))->assertSee($pic->name)->assertDontSee($pic->name, false);
        $unit = OrganizationalUnit::create(['name' => '<script>alert(2)</script>', 'type' => 'directorate', 'is_active' => true]);
        $this->get($this->url('access', $room))->assertSee($unit->name)->assertDontSee($unit->name, false);
        $this->app->bind(VerifyCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends VerifyCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });
        $this->put($this->url('pics.update', $room), ['pic_ids' => [$pic->id]])->assertStatus(419);
        $this->delete($this->url('pics.destroy', $room, $pic))->assertStatus(419);
        $this->put($this->url('access.update', $room), ['organizational_unit_ids' => [$unit->id]])->assertStatus(419);
    }
}
