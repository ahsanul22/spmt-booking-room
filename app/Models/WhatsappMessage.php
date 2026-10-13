<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['number', 'message'];

    protected $casts = ['accepted_at' => 'immutable_datetime'];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
