<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old CHECK constraint before altering columns.
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_preparation_consistent');

        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('consumption_snack')->default(false)->after('needs_consumption');
            $table->boolean('consumption_meal')->default(false)->after('consumption_snack');
        });

        // Migrate existing data: if needs_consumption was true, set both snack and meal.
        DB::table('bookings')->where('needs_consumption', true)->update([
            'consumption_snack' => true,
            'consumption_meal' => true,
        ]);

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('needs_consumption');
        });

        // Re-add the preparation consistency constraint with the new columns.
        // When meeting_preparation is true: participant_count must be present and > 0 (consumption columns are independently optional).
        // When meeting_preparation is false: participant_count must be null and both consumption flags must be false.
        DB::statement(
            'ALTER TABLE bookings ADD CONSTRAINT bookings_preparation_consistent CHECK ('
            .'(meeting_preparation AND participant_count IS NOT NULL AND participant_count > 0) OR '
            .'(NOT meeting_preparation AND participant_count IS NULL AND NOT consumption_snack AND NOT consumption_meal))'
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_preparation_consistent');

        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('needs_consumption')->default(false)->after('participant_count');
        });

        // Migrate back: if either snack or meal was true, set needs_consumption to true.
        DB::table('bookings')->where('consumption_snack', true)->orWhere('consumption_meal', true)->update([
            'needs_consumption' => true,
        ]);

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['consumption_snack', 'consumption_meal']);
        });

        DB::statement(
            'ALTER TABLE bookings ADD CONSTRAINT bookings_preparation_consistent CHECK ('
            .'(meeting_preparation AND participant_count IS NOT NULL AND participant_count > 0) OR '
            .'(NOT meeting_preparation AND participant_count IS NULL AND NOT needs_consumption))'
        );
    }
};
