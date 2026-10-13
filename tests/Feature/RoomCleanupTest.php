<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Services\RoomService;
use Database\Seeders\BookingRoomsSeeder;
use Database\Seeders\FoundationSeeder;
use Tests\PostgresTestCase;

class RoomCleanupTest extends PostgresTestCase
{
    public function test_new_rooms_use_letter_names_and_demo_rooms_are_not_recreated_outside_testing(): void
    {
        $this->seed(BookingRoomsSeeder::class);
        $this->assertSame('3A', Room::where('code', 'SPMT-L3-01')->sole()->name);
        $this->assertSame('3B', Room::where('code', 'SPMT-L3-02')->sole()->name);
        foreach (Room::where('code', 'like', 'DEMO-%')->get() as $room) {
            app(RoomService::class)->delete($room);
        }
        $this->assertSame(10, Room::count());
        $this->app['env'] = 'local';
        try {
            $this->seed(FoundationSeeder::class);
        } finally {
            $this->app['env'] = 'testing';
        }
        $this->assertSame(0, Room::where('code', 'like', 'DEMO-%')->count());
        $this->seed(BookingRoomsSeeder::class);
        $this->assertSame(10, Room::count());
        $this->assertSame('Selat Malaka I', Room::where('code', 'SPMT-L7-01')->sole()->name);
    }
}
