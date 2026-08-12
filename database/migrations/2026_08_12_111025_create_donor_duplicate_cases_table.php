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
        Schema::create('donor_duplicate_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_donor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('candidate_donor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('blood_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 24)->default('pending');
            $table->json('match_signals');
            $table->decimal('match_score', 5, 2);
            $table->foreignId('detected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_reason')->nullable();
            $table->timestamps();

            $table->unique(
                ['primary_donor_id', 'candidate_donor_id', 'status'],
                'donor_duplicate_pair_status_unique',
            );
            $table->index(['status', 'created_at'], 'donor_duplicate_status_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor_duplicate_cases');
    }
};
