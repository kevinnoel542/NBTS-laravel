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
        Schema::table('donations', function (Blueprint $table) {
            $table->unique('appointment_id');
        });

        Schema::table('blood_units', function (Blueprint $table) {
            $table->unique('donation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blood_units', function (Blueprint $table) {
            $table->dropUnique(['donation_id']);
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropUnique(['appointment_id']);
        });
    }
};
