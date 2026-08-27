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
        Schema::create('rollout_scale_readiness_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rollout_pilot_readiness_review_id')->nullable()->constrained(indexName: 'rollout_scale_review_pilot_fk')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'rollout_scale_review_reviewer_fk')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'rollout_scale_review_approver_fk')->nullOnDelete();
            $table->string('review_reference', 96)->unique();
            $table->string('scale_level', 32);
            $table->json('candidate_sites');
            $table->json('readiness_criteria');
            $table->json('kpi_comparison');
            $table->json('monitoring_plan');
            $table->json('support_model');
            $table->json('operating_budget');
            $table->json('vendor_exit_plan');
            $table->json('unresolved_risks');
            $table->string('status', 32)->default('blocked');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['scale_level', 'status']);
            $table->index(['status', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollout_scale_readiness_reviews');
    }
};
