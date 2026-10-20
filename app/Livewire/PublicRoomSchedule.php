<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Gate;

class PublicRoomSchedule extends RoomSchedule
{
    public function boot(): void
    {
        Gate::authorize('access-general');
    }

    protected function publicOnly(): bool
    {
        return true;
    }
}
