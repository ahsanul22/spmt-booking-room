<?php

namespace App\Livewire;

class PublicRoomSchedule extends RoomSchedule
{
    public function boot(): void
    {
        // Public occupancy is scoped on every query, including Livewire refreshes.
    }

    protected function publicOnly(): bool
    {
        return true;
    }
}
