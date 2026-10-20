<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'branch_id']);
        });
        // All accounts and rooms predate branch permissions and belonged to head office.
        $headOffice = DB::table('branches')->orderBy('id')->value('id');
        if ($headOffice !== null) {
            DB::statement('INSERT INTO branch_user (user_id, branch_id) SELECT id, ? FROM users', [$headOffice]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_user');
    }
};
