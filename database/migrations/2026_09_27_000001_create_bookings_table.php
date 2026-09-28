<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->foreignId('organizational_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit_name')->nullable();
            $table->string('agenda');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed']);
            $table->boolean('requires_approval');
            $table->uuid('submission_token');
            $table->string('request_hash', 64);
            $table->foreignId('decided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('decided_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestampsTz();
            $table->unique(['user_id', 'submission_token']);
            $table->index(['room_id', 'date', 'status']);
            $table->index(['user_id', 'created_at']);
        });
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_time_order CHECK (end_time > start_time)');
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
