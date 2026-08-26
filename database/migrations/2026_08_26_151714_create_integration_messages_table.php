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
        Schema::create('integration_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_endpoint_id')->constrained(indexName: 'integration_message_endpoint_fk')->cascadeOnDelete();
            $table->string('message_reference', 96)->unique();
            $table->string('idempotency_key', 128);
            $table->unsignedBigInteger('sequence_number')->nullable();
            $table->string('direction', 32);
            $table->string('message_type', 96);
            $table->string('status', 32)->default('received');
            $table->string('payload_digest', 128);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->json('acknowledgement_payload')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('dead_lettered_at')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();

            $table->unique(['integration_endpoint_id', 'idempotency_key'], 'integration_message_idempotency_unique');
            $table->index(['integration_endpoint_id', 'sequence_number'], 'integration_message_endpoint_sequence_index');
            $table->index(['status', 'next_retry_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_messages');
    }
};
