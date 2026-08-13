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
        Schema::create('patient_specimens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_blood_request_id')->constrained(indexName: 'patient_specimen_request_fk')->cascadeOnDelete();
            $table->foreignId('hospital_id')->constrained(indexName: 'patient_specimen_hospital_fk')->restrictOnDelete();
            $table->foreignId('collected_by')->constrained('users', indexName: 'patient_specimen_collected_by_fk')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users', indexName: 'patient_specimen_received_by_fk')->nullOnDelete();
            $table->string('specimen_identifier', 96)->unique();
            $table->string('patient_reference', 120);
            $table->char('patient_reference_hash', 64);
            $table->string('positive_identification_method', 120);
            $table->string('blood_group', 8)->nullable();
            $table->string('antibody_screen_result', 64)->nullable();
            $table->string('status', 32)->default('collected');
            $table->timestamp('collected_at');
            $table->timestamp('received_at')->nullable();
            $table->timestamp('expires_at');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'status'], 'patient_specimen_hospital_status_index');
            $table->index(['patient_reference_hash', 'status'], 'patient_specimen_patient_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_specimens');
    }
};
