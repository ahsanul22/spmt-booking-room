<?php

namespace Tests\Feature;

use App\Models\Floor;
use App\Models\OrganizationalUnit;
use App\Models\Room;
use App\Models\User;
use App\Rules\EligibleRoomPic;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\PostgresTestCase;

class DatabaseFoundationTest extends PostgresTestCase
{
    public function test_seed_is_repeatable_and_preserves_passwords(): void
    {
        $admin = User::where('email', 'admin@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('password', $admin->password));
        $admin->password = Hash::make('changed-password');
        $admin->save();
        $this->seed();

        foreach (['organizational_units' => 3, 'users' => 3, 'floors' => 8, 'facilities' => 7,
            'rooms' => 4, 'room_pics' => 8, 'facility_room' => 12, 'room_unit_access' => 4] as $table => $count) {
            $this->assertDatabaseCount($table, $count);
        }
        $this->assertTrue(Hash::check('changed-password', $admin->fresh()->password));
        $this->assertSame(0, Floor::where('floor_number', 1)->firstOrFail()->rooms()->count());
    }

    public function test_all_relationships_work_in_both_directions(): void
    {
        $employee = User::where('email', 'pegawai@example.test')->firstOrFail();
        $department = $employee->organizationalUnit;
        $division = $department->parent;
        $this->assertSame('department', $department->type);
        $this->assertTrue($department->users->contains($employee));
        $this->assertTrue($division->children->contains($department));
        $this->assertSame('directorate', $division->parent->type);

        $room = Room::where('code', 'DEMO-01')->firstOrFail();
        $this->assertTrue($room->floor->rooms->contains($room));
        $this->assertCount(3, $room->facilities);
        $this->assertTrue($room->facilities->first()->rooms->contains($room));
        $this->assertCount(2, $room->pics);
        $this->assertTrue($room->pics->first()->managedRooms->contains($room));
        $this->assertTrue($room->allowedOrganizationalUnits->contains($department));
        $this->assertTrue($department->accessibleRooms->contains($room));
        $this->assertTrue($room->requires_approval);
        $this->assertTrue($room->is_active);
        $this->assertSame(7, Room::where('code', 'DEMO-SM')->firstOrFail()->floor->floor_number);
        $this->assertCount(0, Room::where('code', 'DEMO-SM')->firstOrFail()->allowedOrganizationalUnits);
    }

    public function test_pic_validation_accepts_only_pic_and_admin_roles(): void
    {
        foreach (User::all() as $user) {
            $valid = Validator::make(['pic_id' => $user->id], ['pic_id' => [new EligibleRoomPic]])->passes();
            $this->assertSame($user->role !== 'user', $valid);
        }
        foreach ([-1, 'invalid', ['id' => 1]] as $value) {
            $this->assertFalse(Validator::make(['pic_id' => $value], ['pic_id' => [new EligibleRoomPic]])->passes());
        }
    }

    public function test_user_privileges_are_not_mass_assignable(): void
    {
        $user = new User;
        foreach (['role', 'is_active', 'organizational_unit_id'] as $attribute) {
            $this->assertFalse($user->isFillable($attribute));
        }
        $user = User::factory()->create();
        $this->assertSame('user', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNull($user->organizational_unit_id);
    }

    public function test_database_rejects_negative_capacity_and_invalid_enum_values(): void
    {
        foreach ([['rooms', 'capacity', -1], ['rooms', 'access_type', 'invalid'],
            ['rooms', 'status', 'booked'], ['users', 'role', 'division'],
            ['organizational_units', 'type', 'invalid']] as [$table, $column, $value]) {
            $this->assertConstraint('23514', fn () => DB::table($table)->update([$column => $value]));
        }
        $room = Room::firstOrFail();
        $room->update(['capacity' => 0]);
        $this->assertSame(0, $room->fresh()->capacity);
    }

    public function test_all_foreign_keys_reject_missing_references(): void
    {
        foreach ([['organizational_units', 'parent_id'], ['users', 'organizational_unit_id'],
            ['rooms', 'floor_id'], ['facility_room', 'room_id'], ['facility_room', 'facility_id'],
            ['room_pics', 'room_id'], ['room_pics', 'user_id'],
            ['room_unit_access', 'room_id'], ['room_unit_access', 'organizational_unit_id']] as [$table, $column]) {
            $row = (array) DB::table($table)->first();
            $query = DB::table($table);
            foreach ($row as $key => $value) {
                $query->where($key, $value);
            }
            $this->assertConstraint('23503', fn () => $query->update([$column => -1]));
        }
    }

    public function test_unique_codes_and_pivot_pairs_are_enforced(): void
    {
        $code = Room::firstOrFail()->code;
        $this->assertConstraint('23505', fn () => Room::where('code', '!=', $code)->update(['code' => $code]));
        foreach (['facility_room', 'room_pics', 'room_unit_access'] as $table) {
            $row = (array) DB::table($table)->first();
            $this->assertConstraint('23505', fn () => DB::table($table)->insert($row));
        }
        Room::query()->update(['code' => null]);
        $this->assertSame(4, Room::whereNull('code')->count());
    }

    public function test_deleting_used_units_and_floors_is_restricted(): void
    {
        $department = OrganizationalUnit::where('type', 'department')->firstOrFail();
        $directorate = OrganizationalUnit::where('type', 'directorate')->firstOrFail();
        $this->assertConstraint(['23001', '23503'], fn () => $department->delete());
        $this->assertConstraint(['23001', '23503'], fn () => $directorate->delete());
        $this->assertConstraint(['23001', '23503'], fn () => Room::firstOrFail()->floor->delete());
        $this->assertDatabaseCount('users', 3);
    }

    public function test_deleting_room_removes_only_its_pivot_links(): void
    {
        $room = Room::where('code', 'DEMO-01')->firstOrFail();
        $room->delete();
        foreach (['facility_room', 'room_pics', 'room_unit_access'] as $table) {
            $this->assertDatabaseMissing($table, ['room_id' => $room->id]);
        }
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('facilities', 7);
        $this->assertDatabaseCount('organizational_units', 3);
        $this->assertDatabaseCount('rooms', 3);
    }

    public function test_dummy_seeder_refuses_production(): void
    {
        $this->app->instance('env', 'production');
        $this->expectException(\LogicException::class);
        $this->seed();
    }

    public function test_migrations_can_be_rolled_back_and_reapplied(): void
    {
        // This command operates only on the generated test schema.
        $this->artisan('migrate:rollback', ['--force' => true])->assertSuccessful();
        $this->assertFalse(Schema::hasTable('rooms'));
        $this->assertFalse(Schema::hasTable('organizational_units'));
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->seed();
        $this->assertDatabaseCount('rooms', 4);
    }

    private function assertConstraint(string|array $sqlState, callable $operation): void
    {
        try {
            $operation();
            $this->fail('Expected PostgreSQL constraint violation '.implode(', ', (array) $sqlState));
        } catch (QueryException $exception) {
            $this->assertContains((string) $exception->getCode(), (array) $sqlState);
        }
    }
}
