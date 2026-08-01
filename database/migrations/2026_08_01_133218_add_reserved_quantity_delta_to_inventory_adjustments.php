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
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->integer('reserved_quantity_delta')->default(0)->after('quantity_delta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropColumn('reserved_quantity_delta');
        });
    }
};
