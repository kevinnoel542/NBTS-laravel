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
        Schema::create('hospital_blood_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained(indexName: 'hbr_hospital_fk')->restrictOnDelete();
            $table->foreignId('hospital_service_id')->nullable()->constrained(indexName: 'hbr_service_fk')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users', indexName: 'hbr_requested_by_fk')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'hbr_reviewed_by_fk')->nullOnDelete();
            $table->foreignId('component_product_catalog_id')->constrained(indexName: 'hbr_product_catalog_fk')->restrictOnDelete();
            $table->string('request_reference', 96)->unique();
            $table->string('patient_reference', 120);
            $table->char('patient_reference_hash', 64);
            $table->unsignedSmallInteger('patient_birth_year')->nullable();
            $table->string('patient_gender', 32)->nullable();
            $table->string('diagnosis', 255);
            $table->string('indication', 255);
            $table->decimal('hemoglobin_g_dl', 4, 2)->nullable();
            $table->json('observations')->nullable();
            $table->boolean('active_bleeding')->default(false);
            $table->string('urgency', 32)->default('routine');
            $table->string('requested_blood_group', 8)->nullable();
            $table->unsignedSmallInteger('quantity_requested');
            $table->unsignedSmallInteger('quantity_allocated')->default(0);
            $table->timestamp('required_at');
            $table->json('attachments')->nullable();
            $table->text('notes')->nullable();
            $table->json('guidance_snapshot')->nullable();
            $table->string('override_reason', 500)->nullable();
            $table->string('source_mode', 32)->default('electronic');
            $table->string('status', 32)->default('submitted');
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('partially_filled_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'status', 'urgency'], 'hbr_hospital_status_index');
            $table->index(['patient_reference_hash', 'status'], 'hbr_patient_hash_status_index');
            $table->index(['required_at', 'status'], 'hbr_required_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_blood_requests');
    }
};
