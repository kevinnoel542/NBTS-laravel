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
        Schema::create('offline_identifier_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offline_collection_device_id')->constrained()->restrictOnDelete();
            $table->foreignId('blood_center_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('prefix', 24);
            $table->unsignedBigInteger('start_sequence');
            $table->unsignedBigInteger('end_sequence');
            $table->unsignedBigInteger('next_sequence');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at');
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['offline_collection_device_id', 'year', 'start_sequence'],
                'offline_identifier_device_range_unique',
            );
            $table->index(['blood_center_id', 'expires_at'], 'offline_identifier_center_expiry_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_identifier_batches');
    }
};
