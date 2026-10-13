<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event')->unique();
            $table->text('body');
            $table->boolean('is_active')->default(false);
            $table->boolean('include_room_pics')->default(false);
            $table->timestampsTz();
        });
        foreach ([
            'approval_request' => "Pengajuan booking menunggu approval\nBooking #{booking_id}\nRuangan: {room}\nPemohon: {applicant}\nTanggal: {date}\nJam: {start_time}–{end_time} WIB\nReview (login PIC/Admin diperlukan): {review_url}",
            'consumption' => "Booking disetujui — kebutuhan konsumsi\nBooking #{booking_id}\nRuangan: {room}\nPemohon: {applicant}\nTanggal: {date}\nJam: {start_time}–{end_time} WIB\nPerkiraan peserta: {participant_count}\nKonsumsi: Ya",
        ] as $event => $body) {
            DB::table('whatsapp_templates')->insert(['event' => $event, 'body' => $body, 'created_at' => now(), 'updated_at' => now()]);
        }
        Schema::table('whatsapp_recipients', function (Blueprint $table) {
            $table->dropUnique(['purpose']);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unique(['purpose', 'user_id']);
        });
        // Keep unmatched legacy numbers for Admin review, never invent accounts/roles.
        DB::statement('ALTER TABLE whatsapp_recipients ALTER COLUMN number DROP NOT NULL');
        foreach (DB::table('whatsapp_recipients')->get() as $recipient) {
            $users = DB::table('users')->where('whatsapp_number', $recipient->number)->where('is_active', true)
                ->when($recipient->purpose === 'approval_request', fn ($query) => $query->where('role', 'super_admin'))->get();
            DB::table('whatsapp_recipients')->where('id', $recipient->id)->update($users->count() === 1
                ? ['user_id' => $users->first()->id, 'number' => null]
                : ['is_active' => false]);
        }
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
        foreach (DB::table('whatsapp_messages')->get() as $message) {
            $recipient = DB::table('whatsapp_recipients')->join('users', 'users.id', '=', 'whatsapp_recipients.user_id')
                ->where('purpose', $message->event)->where('users.whatsapp_number', $message->number)->first(['users.id']);
            if ($recipient) {
                DB::table('whatsapp_messages')->where('id', $message->id)->update(['recipient_user_id' => $recipient->id]);
            }
        }
    }

    public function down(): void
    {
        throw new LogicException('Migration penerima user tidak dapat dirollback otomatis. Gunakan backup database jika diperlukan.');
    }
};
