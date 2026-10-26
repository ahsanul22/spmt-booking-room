<?php

namespace App\Models;

use App\Models\Concerns\HasReadableRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasReadableRouteKey;

    protected $fillable = ['branch_id', 'name', 'code', 'floor_id', 'capacity', 'description', 'access_type', 'requires_approval', 'allows_meeting_preparation', 'allows_it_support', 'status', 'is_active'];

    protected $casts = ['capacity' => 'integer', 'requires_approval' => 'boolean', 'allows_meeting_preparation' => 'boolean', 'allows_it_support' => 'boolean', 'is_active' => 'boolean'];

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (! $user->is_active || ! in_array($user->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ROOM_PIC, User::ROLE_USER], true)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->when($user->role !== User::ROLE_SUPER_ADMIN, fn ($rooms) => $rooms
            ->whereIn('rooms.branch_id', $user->branches()->select('branches.id')));
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

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
