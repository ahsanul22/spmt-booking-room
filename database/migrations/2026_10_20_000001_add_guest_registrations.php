<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('organization');
            $table->string('contact', 50);
            $table->string('reference', 20)->unique();
            $table->timestampTz('expires_at')->index();
            $table->timestampsTz();
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('guest_name')->nullable();
            $table->string('guest_organization')->nullable();
            $table->string('guest_reference', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn(['guest_name', 'guest_organization', 'guest_reference']));
        Schema::dropIfExists('guest_registrations');
    }
};
