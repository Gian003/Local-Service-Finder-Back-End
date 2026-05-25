<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Replace polymorphic references with specific foreign keys.
     * This ensures database-level referential integrity instead of application-level checks.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade')
                ->after('type');
        });

        // Drop the polymorphic columns (they become optional now)
        // reference_id and reference_type are kept for backward compatibility
        // but booking_id should be the primary link
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeignKey('notifications_booking_id_foreign');
            $table->dropColumn('booking_id');
        });
    }
};
