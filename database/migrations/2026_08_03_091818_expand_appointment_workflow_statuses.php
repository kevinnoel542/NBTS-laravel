<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('checked_in_at')->nullable()->after('confirmed_at');
            $table->timestamp('no_show_at')->nullable()->after('cancelled_at');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE appointments MODIFY status ENUM('pending', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        DB::table('appointments')->where('status', 'checked_in')->update(['status' => 'confirmed']);
        DB::table('appointments')->where('status', 'no_show')->update(['status' => 'cancelled']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE appointments MODIFY status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['checked_in_at', 'no_show_at']);
        });
    }
};
