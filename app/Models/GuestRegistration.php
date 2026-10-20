<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestRegistration extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['expires_at' => 'immutable_datetime'];
}
