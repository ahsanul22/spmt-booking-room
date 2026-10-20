<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        $headOffice = DB::table('branches')->insertGetId(['name' => 'Kantor Pusat', 'created_at' => now(), 'updated_at' => now()]);
        Schema::table('rooms', function (Blueprint $table) use ($headOffice) {
            $table->foreignId('branch_id')->default($headOffice)->constrained()->restrictOnDelete();
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('it_support')->default(false);
            $table->boolean('approved_it_support')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn(['it_support', 'approved_it_support']));
        Schema::table('rooms', fn (Blueprint $table) => $table->dropConstrainedForeignId('branch_id'));
        Schema::dropIfExists('branches');
    }
};
