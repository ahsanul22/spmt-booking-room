<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizational_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['directorate', 'division', 'department']);
            $table->foreignId('parent_id')->nullable()->constrained('organizational_units')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizational_units');
    }
};
