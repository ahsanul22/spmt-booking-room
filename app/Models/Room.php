<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    protected $fillable = ['name', 'code', 'floor_id', 'capacity', 'description', 'access_type', 'requires_approval', 'status', 'is_active'];

    protected $casts = ['capacity' => 'integer', 'requires_approval' => 'boolean', 'is_active' => 'boolean'];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class);
    }

    public function pics(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_pics');
    }

    public function allowedOrganizationalUnits(): BelongsToMany
    {
        return $this->belongsToMany(OrganizationalUnit::class, 'room_unit_access');
    }
}
