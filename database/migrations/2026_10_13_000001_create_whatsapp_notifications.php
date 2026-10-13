<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('purpose')->unique();
            $table->string('number', 16);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->string('event');
            $table->string('number', 16);
            $table->text('message');
            $table->string('status')->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->string('error_code')->nullable();
            $table->timestampTz('accepted_at')->nullable();
            $table->timestampsTz();
            $table->unique(['booking_id', 'event', 'number']);
            $table->index(['status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_recipients');
    }
};
