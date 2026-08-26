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
        Schema::create('hospital_transfusion_committee_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained(indexName: 'htc_hospital_fk')->cascadeOnDelete();
            $table->foreignId('chaired_by')->nullable()->constrained('users', indexName: 'htc_chaired_by_fk')->nullOnDelete();
            $table->string('review_reference', 96)->unique();
            $table->date('meeting_date');
            $table->string('status', 32)->default('open');
            $table->json('utilization_metrics')->nullable();
            $table->json('emergency_release_review')->nullable();
            $table->json('reaction_review')->nullable();
            $table->json('wastage_review')->nullable();
            $table->json('education_actions')->nullable();
            $table->json('linked_deviation_ids')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'meeting_date'], 'htc_hospital_meeting_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_transfusion_committee_reviews');
    }
};
