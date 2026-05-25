<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add review_count column to services table. This column is used by
     * ServiceController to sort services by popularity.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('review_count')->default(0)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('review_count');
        });
    }
};
