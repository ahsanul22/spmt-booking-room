<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['requires_approval' => 'boolean', 'meeting_preparation' => 'boolean', 'participant_count' => 'integer', 'consumption_snack' => 'boolean', 'consumption_meal' => 'boolean', 'approved_consumption_snack' => 'boolean', 'approved_consumption_meal' => 'boolean', 'decided_at' => 'immutable_datetime'];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }
}
