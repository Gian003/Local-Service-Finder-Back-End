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
            $table->string('payment_method')->default('cash')->change();
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending')->after('payment_intent_id');
            $table->unique('payment_intent_id', 'bookings_payment_intent_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique('bookings_payment_intent_id_unique');
            $table->dropColumn('payment_status');
        });
    }
};
