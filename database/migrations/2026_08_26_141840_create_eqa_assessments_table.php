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
        Schema::create('eqa_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->constrained(indexName: 'eqa_center_fk')->restrictOnDelete();
            $table->foreignId('laboratory_test_catalog_id')->nullable()->constrained(indexName: 'eqa_test_catalog_fk')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users', indexName: 'eqa_submitted_by_fk')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'eqa_reviewed_by_fk')->nullOnDelete();
            $table->string('scheme_code', 96);
            $table->string('round_code', 96);
            $table->string('status', 32)->default('scheduled');
            $table->json('expected_results')->nullable();
            $table->json('submitted_results')->nullable();
            $table->json('findings')->nullable();
            $table->json('linked_deviation_ids')->nullable();
            $table->timestamp('due_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['blood_center_id', 'scheme_code', 'round_code'], 'eqa_center_scheme_round_unique');
            $table->index(['status', 'due_at'], 'eqa_status_due_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eqa_assessments');
    }
};
