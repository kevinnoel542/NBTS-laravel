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
        Schema::create('recall_trace_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recall_case_id')->constrained(indexName: 'recall_trace_case_fk')->cascadeOnDelete();
            $table->foreignId('donation_id')->nullable()->constrained(indexName: 'recall_trace_donation_fk')->nullOnDelete();
            $table->foreignId('blood_unit_id')->nullable()->constrained(indexName: 'recall_trace_unit_fk')->nullOnDelete();
            $table->foreignId('blood_component_id')->nullable()->constrained(indexName: 'recall_trace_component_fk')->nullOnDelete();
            $table->foreignId('hospital_id')->nullable()->constrained(indexName: 'recall_trace_hospital_fk')->nullOnDelete();
            $table->foreignId('hospital_blood_request_id')->nullable()->constrained(indexName: 'recall_trace_request_fk')->nullOnDelete();
            $table->foreignId('transfusion_record_id')->nullable()->constrained(indexName: 'recall_trace_transfusion_fk')->nullOnDelete();
            $table->string('trace_direction', 32);
            $table->string('item_type', 64);
            $table->string('item_identifier', 120);
            $table->string('current_location')->nullable();
            $table->string('status', 32)->default('located');
            $table->json('notifications')->nullable();
            $table->json('disposition')->nullable();
            $table->timestamp('located_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('exception_reason')->nullable();
            $table->timestamps();

            $table->unique(['recall_case_id', 'item_type', 'item_identifier'], 'recall_trace_item_unique');
            $table->index(['recall_case_id', 'status'], 'recall_trace_case_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recall_trace_items');
    }
};
