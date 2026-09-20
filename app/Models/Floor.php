<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    protected $fillable = ['name', 'floor_number', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'floor_number' => 'integer'];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
