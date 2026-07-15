<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // BookingController::reject() sets status to 'rejected', but that
        // value was never added to the enum — every rejection has been
        // failing with a "Data truncated for column 'status'" SQL error.
        DB::statement(
            "ALTER TABLE bookings MODIFY COLUMN status " .
            "ENUM('pending','accepted','upcoming','completed','cancelled','saved','rejected') " .
            "NOT NULL DEFAULT 'pending'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE bookings MODIFY COLUMN status " .
            "ENUM('pending','accepted','upcoming','completed','cancelled','saved') " .
            "NOT NULL DEFAULT 'pending'"
        );
    }
};
