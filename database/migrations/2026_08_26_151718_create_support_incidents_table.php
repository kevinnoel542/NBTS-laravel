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
        Schema::create('support_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users', indexName: 'support_incident_owner_fk')->nullOnDelete();
            $table->foreignId('blood_center_id')->nullable()->constrained(indexName: 'support_incident_center_fk')->nullOnDelete();
            $table->foreignId('recurrence_link_id')->nullable()->constrained('support_incidents', indexName: 'support_incident_recurrence_fk')->nullOnDelete();
            $table->string('incident_reference', 96)->unique();
            $table->string('severity', 32);
            $table->string('service', 96);
            $table->text('impact');
            $table->string('status', 32)->default('open');
            $table->text('workaround')->nullable();
            $table->text('root_cause')->nullable();
            $table->json('communication_log')->nullable();
            $table->json('escalation_targets');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['severity', 'status']);
            $table->index(['service', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_incidents');
    }
};
