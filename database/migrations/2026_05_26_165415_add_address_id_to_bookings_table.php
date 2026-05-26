<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds address_id to bookings to track where service will be provided.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add the address_id column as nullable first
            $table->foreignId('address_id')
                ->nullable()
                ->constrained()
                ->onDelete('restrict')
                ->after('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeignKey('bookings_address_id_foreign');
            $table->dropColumn('address_id');
        });
    }
};
