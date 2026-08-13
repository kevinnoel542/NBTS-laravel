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
        Schema::create('transfusion_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_blood_request_id')->constrained(indexName: 'tr_request_fk')->cascadeOnDelete();
            $table->foreignId('hospital_component_allocation_id')->constrained(indexName: 'tr_allocation_fk')->cascadeOnDelete();
            $table->foreignId('hospital_component_receipt_id')->nullable()->constrained(indexName: 'tr_receipt_fk')->nullOnDelete();
            $table->foreignId('patient_specimen_id')->constrained(indexName: 'tr_specimen_fk')->restrictOnDelete();
            $table->foreignId('blood_component_id')->constrained(indexName: 'tr_component_fk')->restrictOnDelete();
            $table->foreignId('verified_by')->constrained('users', indexName: 'tr_verified_by_fk')->restrictOnDelete();
            $table->foreignId('recorded_by')->constrained('users', indexName: 'tr_recorded_by_fk')->restrictOnDelete();
            $table->string('status', 32)->default('started');
            $table->json('bedside_checks');
            $table->timestamp('verified_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('observations')->nullable();
            $table->unsignedSmallInteger('volume_ml')->nullable();
            $table->string('outcome', 64)->nullable();
            $table->string('unused_component_disposition', 64)->nullable();
            $table->timestamp('overdue_at')->nullable();
            $table->timestamp('final_disposition_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('blood_component_id', 'tr_component_unique');
            $table->index(['hospital_blood_request_id', 'status'], 'tr_request_status_index');
            $table->index(['status', 'overdue_at'], 'tr_overdue_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfusion_records');
    }
};
