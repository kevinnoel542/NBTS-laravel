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
        Schema::create('rollout_site_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->nullable()->constrained(indexName: 'rollout_site_assessment_center_fk')->nullOnDelete();
            $table->foreignId('assessed_by')->nullable()->constrained('users', indexName: 'rollout_site_assessment_assessor_fk')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'rollout_site_assessment_approver_fk')->nullOnDelete();
            $table->string('assessment_reference', 96)->unique();
            $table->string('site_name');
            $table->string('site_type', 64);
            $table->json('workflow_map');
            $table->json('inventory_snapshot');
            $table->json('baseline_kpis');
            $table->json('risks');
            $table->json('data_dictionary_scope');
            $table->json('master_data_owners');
            $table->string('safety_case_reference')->nullable();
            $table->string('target_process_reference')->nullable();
            $table->json('pilot_scope');
            $table->json('prioritized_backlog');
            $table->json('legal_and_policy_inputs');
            $table->json('operational_readiness');
            $table->string('status', 32)->default('draft');
            $table->timestamp('assessed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['site_type', 'status']);
            $table->index(['blood_center_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollout_site_assessments');
    }
};
