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
        Schema::create('notification_outboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users', indexName: 'notification_outbox_created_by_fk')->restrictOnDelete();
            $table->foreignId('recipient_id')->nullable()->constrained('users', indexName: 'notification_outbox_recipient_fk')->nullOnDelete();
            $table->foreignId('user_notification_id')->nullable()->constrained('user_notifications', indexName: 'notification_outbox_notification_fk')->nullOnDelete();
            $table->string('outbox_reference', 48)->unique();
            $table->string('idempotency_key', 160)->unique();
            $table->string('template_code', 120);
            $table->string('alert_type', 80);
            $table->string('channel', 32);
            $table->string('locale', 8)->default('en');
            $table->string('recipient_hash', 128)->nullable();
            $table->json('segment_criteria');
            $table->json('payload_summary');
            $table->json('preferences_snapshot');
            $table->json('consent_snapshot');
            $table->json('quiet_hours');
            $table->boolean('after_commit')->default(true);
            $table->boolean('non_coercive')->default(true);
            $table->string('provider')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('status', 32)->default('pending');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(5);
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_attempt_at']);
            $table->index(['alert_type', 'status']);
            $table->index(['channel', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_outboxes');
    }
};
