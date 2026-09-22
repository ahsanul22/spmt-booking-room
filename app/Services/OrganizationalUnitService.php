<?php

namespace App\Services;

use App\Models\OrganizationalUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizationalUnitService
{
    public function save(array $data, ?OrganizationalUnit $unit = null): OrganizationalUnit
    {
        $data['parent_id'] = $data['parent_id'] ?? null;

        return DB::transaction(function () use ($data, $unit) {
            // Serialize hierarchy writes so two concurrent edits cannot invalidate each other.
            DB::statement('LOCK TABLE organizational_units IN SHARE ROW EXCLUSIVE MODE');
            $unit = $unit ? $unit->fresh() : new OrganizationalUnit;
            $parent = empty($data['parent_id']) ? null : OrganizationalUnit::find($data['parent_id']);
            $expectedParent = ['directorate' => null, 'division' => 'directorate', 'department' => 'division'][$data['type']];

            if ($expectedParent === null && ! empty($data['parent_id'])) {
                throw ValidationException::withMessages(['parent_id' => 'Directorate tidak boleh memiliki parent.']);
            }
            if ($expectedParent !== null && (! $parent || $parent->type !== $expectedParent)) {
                throw ValidationException::withMessages(['parent_id' => 'Parent harus bertipe '.$expectedParent.'.']);
            }

            $visited = $unit->exists ? [$unit->id => true] : [];
            for ($ancestor = $parent; $ancestor !== null; $ancestor = $ancestor->parent) {
                if (isset($visited[$ancestor->id])) {
                    throw ValidationException::withMessages(['parent_id' => 'Unit tidak boleh menjadi parent dirinya sendiri atau membentuk circular hierarchy.']);
                }
                $visited[$ancestor->id] = true;
            }

            if ($unit->exists && $unit->type !== $data['type']) {
                $expectedChild = ['directorate' => 'division', 'division' => 'department', 'department' => null][$data['type']];
                $invalidChildren = $unit->children();
                if ($expectedChild !== null) {
                    $invalidChildren->where('type', '!=', $expectedChild);
                }
                if ($invalidChildren->exists()) {
                    throw ValidationException::withMessages(['type' => 'Perubahan tipe membuat struktur child unit yang ada tidak valid.']);
                }
            }

            $unit->fill($data);
            $unit->save();

            return $unit;
        });
    }
}
