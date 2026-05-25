<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add soft deletes to critical tables for data archival and audit trails.
     * This allows recovering deleted data and maintaining historical records.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
