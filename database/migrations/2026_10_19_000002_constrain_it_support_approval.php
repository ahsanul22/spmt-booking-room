<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_it_support_approval_subset CHECK (NOT approved_it_support OR it_support)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT bookings_it_support_approval_subset');
    }
};
