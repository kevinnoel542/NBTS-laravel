<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reminder_key');
            $table->string('phone');
            $table->text('message');
            $table->string('provider')->nullable();
            $table->string('status')->default('pending');
            $table->string('provider_message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['appointment_id', 'reminder_key']);
            $table->index(['user_id', 'status']);
            $table->index(['reminder_key', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_reminder_logs');
    }
};
