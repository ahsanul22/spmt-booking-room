<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->foreignId('floor_id')->constrained()->restrictOnDelete();
            $table->integer('capacity');
            $table->text('description')->nullable();
            $table->enum('access_type', ['all', 'restricted'])->default('all');
            $table->boolean('requires_approval')->default(false);
            $table->enum('status', ['available', 'maintenance', 'unavailable'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('floor_id');
        });

        // PostgreSQL does not enforce unsigned integers; use an explicit CHECK.
        DB::statement('ALTER TABLE rooms ADD CONSTRAINT rooms_capacity_nonnegative CHECK (capacity >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
