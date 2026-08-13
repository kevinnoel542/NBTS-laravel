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
        Schema::create('hospital_component_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_component_allocation_id')->constrained(indexName: 'hcr_allocation_fk')->cascadeOnDelete();
            $table->foreignId('hospital_blood_request_id')->constrained(indexName: 'hcr_request_fk')->cascadeOnDelete();
            $table->foreignId('blood_component_id')->constrained(indexName: 'hcr_component_fk')->restrictOnDelete();
            $table->foreignId('hospital_id')->constrained(indexName: 'hcr_hospital_fk')->restrictOnDelete();
            $table->foreignId('received_by')->constrained('users', indexName: 'hcr_received_by_fk')->restrictOnDelete();
            $table->string('status', 32)->default('accepted');
            $table->timestamp('received_at');
            $table->string('condition', 120);
            $table->json('temperature_evidence')->nullable();
            $table->text('discrepancy_notes')->nullable();
            $table->json('chain_of_custody')->nullable();
            $table->timestamps();

            $table->unique(['hospital_component_allocation_id', 'blood_component_id'], 'hcr_allocation_component_unique');
            $table->index(['hospital_id', 'status'], 'hcr_hospital_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_component_receipts');
    }
};
