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
        Schema::create('rollout_pilot_readiness_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rollout_site_assessment_id')->nullable()->constrained(indexName: 'rollout_pilot_review_assessment_fk')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'rollout_pilot_review_reviewer_fk')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'rollout_pilot_review_approver_fk')->nullOnDelete();
            $table->string('review_reference', 96)->unique();
            $table->string('pilot_name');
            $table->json('pilot_sites');
            $table->json('chain_coverage');
            $table->json('prerequisites');
            $table->json('validation_evidence');
            $table->json('data_migration_evidence');
            $table->json('training_evidence');
            $table->json('downtime_restore_evidence');
            $table->json('traceability_recall_evidence');
            $table->json('open_defects');
            $table->json('signoffs');
            $table->json('exit_criteria');
            $table->string('status', 32)->default('blocked');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollout_pilot_readiness_reviews');
    }
};
