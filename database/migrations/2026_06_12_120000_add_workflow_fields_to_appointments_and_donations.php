<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('confirmed_at');
            $table->foreignId('handled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->enum('donation_type', ['appointment', 'walk_in'])->default('appointment')->after('appointment_id');
            $table->foreignId('recorded_by')->nullable()->after('blood_center_id')->constrained('users')->nullOnDelete();
            $table->boolean('blood_group_verified')->default(false)->after('blood_group');
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['recorded_by']);
            $table->dropColumn(['donation_type', 'recorded_by', 'blood_group_verified']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['handled_by']);
            $table->dropColumn(['confirmed_at', 'cancelled_at', 'handled_by']);
        });
    }
};
