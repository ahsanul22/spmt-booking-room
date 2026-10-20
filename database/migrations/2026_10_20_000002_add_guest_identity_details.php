<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_registrations', function (Blueprint $table) {
            $table->string('nip', 50)->nullable();
            $table->text('purpose')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('guest_registrations', fn (Blueprint $table) => $table->dropColumn(['nip', 'purpose']));
    }
};
