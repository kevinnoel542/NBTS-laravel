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
        Schema::table('blood_centers', function (Blueprint $table) {
            $table->foreignId('organization_unit_id')
                ->nullable()
                ->after('id')
                ->unique()
                ->constrained()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blood_centers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_unit_id');
        });
    }
};
