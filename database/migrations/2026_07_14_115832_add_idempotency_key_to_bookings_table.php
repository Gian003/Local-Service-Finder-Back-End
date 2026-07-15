<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Client-generated key, stable across retries of the same
            // checkout attempt (the app's http client auto-retries on
            // timeout), so a lost response doesn't create a duplicate booking.
            $table->string('idempotency_key')->nullable()->unique()->after('payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('idempotency_key');
        });
    }
};
