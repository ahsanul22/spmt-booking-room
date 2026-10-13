<?php

use App\Support\WhatsappNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::statement('LOCK TABLE users IN SHARE ROW EXCLUSIVE MODE');
            $users = DB::table('users')->orderBy('id')->get();
            $emails = [];
            $normalized = [];
            foreach ($users as $user) {
                $email = mb_strtolower(trim($user->email));
                $number = WhatsappNumber::normalize($user->whatsapp_number);
                if (isset($emails[$email])) {
                    throw new RuntimeException('Email duplikat pada akun '.$emails[$email].' dan '.$user->id.'. Perbaiki data sebelum migration.');
                }
                if ($number !== null && ! WhatsappNumber::valid($number)) {
                    throw new RuntimeException('Nomor WhatsApp tidak valid pada akun '.$user->id.'. Perbaiki data sebelum migration.');
                }
                $emails[$email] = $user->id;
                $normalized[$user->id] = ['email' => $email, 'whatsapp_number' => $number];
            }
            foreach ($normalized as $id => $values) {
                DB::table('users')->where('id', $id)->update($values);
            }
            DB::statement('CREATE UNIQUE INDEX users_email_normalized_unique ON users (lower(btrim(email)))');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_whatsapp_number_format CHECK (whatsapp_number IS NULL OR whatsapp_number ~ '^\\+[1-9][0-9]{7,14}$')");
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_whatsapp_number_format');
        DB::statement('DROP INDEX users_email_normalized_unique');
    }
};
