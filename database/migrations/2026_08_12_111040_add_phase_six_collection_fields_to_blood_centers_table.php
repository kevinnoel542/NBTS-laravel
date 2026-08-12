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
            $table->string('collection_identifier_prefix', 16)->nullable()->unique()->after('center_type');
            $table->unsignedSmallInteger('daily_collection_capacity')->nullable()->after('collection_identifier_prefix');
            $table->boolean('offline_collection_enabled')->default(false)->after('daily_collection_capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blood_centers', function (Blueprint $table) {
            $table->dropColumn([
                'collection_identifier_prefix',
                'daily_collection_capacity',
                'offline_collection_enabled',
            ]);
        });
    }
};
