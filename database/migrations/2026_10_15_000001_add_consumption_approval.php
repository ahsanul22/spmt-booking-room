<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('approved_consumption_snack')->default(false);
            $table->boolean('approved_consumption_meal')->default(false);
        });
        // Preserve consumption already confirmed before separate review existed.
        DB::statement("UPDATE bookings SET approved_consumption_snack = consumption_snack, approved_consumption_meal = consumption_meal WHERE status IN ('approved', 'completed')");
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_consumption_approval_subset CHECK ((NOT approved_consumption_snack OR consumption_snack) AND (NOT approved_consumption_meal OR consumption_meal))');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT bookings_consumption_approval_subset');
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn(['approved_consumption_snack', 'approved_consumption_meal']));
    }
};
