<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // sender_id/receiver_id can each point at either the users table
            // or the workers table, so a single FK to `users` is wrong and
            // is dropped in favor of the type discriminators below.
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
            $table->string('sender_type')->nullable()->after('sender_id');
            $table->string('receiver_type')->nullable()->after('receiver_id');
        });

        // Best-effort backfill for existing rows: infer the type by checking
        // which table actually contains that id.
        DB::table('messages')
            ->select('id', 'sender_id', 'receiver_id')
            ->orderBy('id')
            ->chunkById(200, function ($messages) {
                foreach ($messages as $message) {
                    DB::table('messages')->where('id', $message->id)->update([
                        'sender_type' => DB::table('users')->where('id', $message->sender_id)->exists()
                            ? 'user' : 'worker',
                        'receiver_type' => DB::table('users')->where('id', $message->receiver_id)->exists()
                            ? 'user' : 'worker',
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['sender_type', 'receiver_type']);
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
