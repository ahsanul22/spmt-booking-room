<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean', 'include_room_pics' => 'boolean'];

    public function label(): string
    {
        return $this->event === 'approval_request' ? 'Pengajuan approval' : 'Kebutuhan konsumsi';
    }
}
