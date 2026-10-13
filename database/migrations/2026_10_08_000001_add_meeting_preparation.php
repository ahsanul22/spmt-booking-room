<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->boolean('allows_meeting_preparation')->default(false);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('meeting_preparation')->default(false);
            $table->integer('participant_count')->nullable();
            $table->boolean('needs_consumption')->default(false);
        });
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_preparation_consistent CHECK ((meeting_preparation AND participant_count IS NOT NULL AND participant_count > 0) OR (NOT meeting_preparation AND participant_count IS NULL AND NOT needs_consumption))');
        // Bootstrap existing Selat Malaka rooms once; subsequent changes belong to Admin.
        DB::table('rooms')->whereRaw('LOWER(name) LIKE ?', ['selat malaka%'])
            ->update(['allows_meeting_preparation' => true]);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT bookings_preparation_consistent');
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn(['meeting_preparation', 'participant_count', 'needs_consumption']));
        Schema::table('rooms', fn (Blueprint $table) => $table->dropColumn('allows_meeting_preparation'));
    }
};
