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
        Schema::create('rollout_policy_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rollout_site_assessment_id')->nullable()->constrained(indexName: 'rollout_policy_decision_assessment_fk')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users', indexName: 'rollout_policy_decision_owner_fk')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'rollout_policy_decision_approver_fk')->nullOnDelete();
            $table->string('decision_code', 96)->unique();
            $table->string('category', 80);
            $table->string('title');
            $table->text('decision_summary');
            $table->json('options_considered');
            $table->json('required_approvals');
            $table->json('approval_evidence')->nullable();
            $table->json('risk_acceptance')->nullable();
            $table->json('implementation_controls');
            $table->json('review_schedule');
            $table->string('status', 32)->default('pending');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index(['rollout_site_assessment_id', 'status'], 'rollout_policy_assessment_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollout_policy_decisions');
    }
};
