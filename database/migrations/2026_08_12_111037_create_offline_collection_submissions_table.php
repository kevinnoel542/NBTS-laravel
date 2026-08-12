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
        Schema::create('offline_collection_submissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_submission_id')->unique();
            $table->foreignId('offline_collection_device_id')
                ->constrained(indexName: 'offline_submission_device_fk')
                ->restrictOnDelete();
            $table->foreignId('offline_identifier_batch_id')
                ->constrained(indexName: 'offline_submission_batch_fk')
                ->restrictOnDelete();
            $table->foreignId('blood_center_id')->constrained()->restrictOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('donation_identifier', 64)->unique();
            $table->string('payload_hash', 64)->index();
            $table->longText('payload');
            $table->string('status', 24)->default('received');
            $table->json('conflict_codes')->nullable();
            $table->foreignId('collection_episode_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at');
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_reason')->nullable();
            $table->timestamps();

            $table->index(['blood_center_id', 'status', 'received_at'], 'offline_submission_center_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_collection_submissions');
    }
};
