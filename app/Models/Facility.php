<?php

namespace App\Models;

use App\Models\Concerns\HasReadableRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Facility extends Model
{
    use HasReadableRouteKey;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }
}
