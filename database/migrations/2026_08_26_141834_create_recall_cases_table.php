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
        Schema::create('recall_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->nullable()->constrained(indexName: 'recall_center_fk')->nullOnDelete();
            $table->foreignId('opened_by')->constrained('users', indexName: 'recall_opened_by_fk')->restrictOnDelete();
            $table->foreignId('decision_authority_id')->nullable()->constrained('users', indexName: 'recall_decision_authority_fk')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users', indexName: 'recall_closed_by_fk')->nullOnDelete();
            $table->string('case_reference', 96)->unique();
            $table->string('trigger_type', 96);
            $table->string('severity', 32);
            $table->string('status', 32)->default('open');
            $table->text('description');
            $table->json('trigger_evidence')->nullable();
            $table->json('containment_actions')->nullable();
            $table->json('notification_plan')->nullable();
            $table->json('regulator_communication')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('trace_started_at')->nullable();
            $table->timestamp('deadline_at');
            $table->timestamp('closed_at')->nullable();
            $table->text('closure_summary')->nullable();
            $table->text('unresolved_exception_reason')->nullable();
            $table->timestamp('approved_for_closure_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity'], 'recall_status_severity_index');
            $table->index(['blood_center_id', 'status'], 'recall_center_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recall_cases');
    }
};
