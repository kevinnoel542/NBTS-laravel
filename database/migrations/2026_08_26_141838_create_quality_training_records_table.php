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
        Schema::create('quality_training_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(indexName: 'qtrain_user_fk')->cascadeOnDelete();
            $table->foreignId('quality_document_id')->nullable()->constrained(indexName: 'qtrain_document_fk')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users', indexName: 'qtrain_verified_by_fk')->nullOnDelete();
            $table->string('competency_code', 96);
            $table->string('title');
            $table->string('status', 32)->default('scheduled');
            $table->date('trained_on')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamp('reassessment_due_at')->nullable();
            $table->boolean('retraining_required')->default(false);
            $table->text('evidence_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'competency_code', 'status'], 'qtrain_user_competency_index');
            $table->index(['status', 'valid_until'], 'qtrain_status_valid_until_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_training_records');
    }
};
