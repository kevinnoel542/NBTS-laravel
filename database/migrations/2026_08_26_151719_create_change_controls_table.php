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
        Schema::create('change_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users', indexName: 'change_control_requested_by_fk')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'change_control_approved_by_fk')->nullOnDelete();
            $table->string('change_reference', 96)->unique();
            $table->string('classification', 64);
            $table->string('title');
            $table->json('scope');
            $table->string('risk_level', 32);
            $table->json('approvals');
            $table->json('regression_evidence')->nullable();
            $table->text('migration_plan');
            $table->text('rollback_plan');
            $table->text('release_notes');
            $table->text('training_impact')->nullable();
            $table->boolean('emergency_change')->default(false);
            $table->string('status', 32)->default('requested');
            $table->timestamp('effective_at')->nullable();
            $table->timestamp('retrospective_review_due_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['classification', 'status']);
            $table->index(['effective_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_controls');
    }
};
