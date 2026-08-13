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
        Schema::create('compatibility_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_blood_request_id')->constrained(indexName: 'compat_test_request_fk')->cascadeOnDelete();
            $table->foreignId('patient_specimen_id')->constrained(indexName: 'compat_test_specimen_fk')->restrictOnDelete();
            $table->foreignId('blood_component_id')->constrained(indexName: 'compat_test_component_fk')->restrictOnDelete();
            $table->foreignId('performed_by')->constrained('users', indexName: 'compat_test_performed_by_fk')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'compat_test_reviewed_by_fk')->nullOnDelete();
            $table->unsignedBigInteger('emergency_release_authorization_id')->nullable();
            $table->string('method', 120);
            $table->string('instrument_identifier', 120)->nullable();
            $table->string('reagent_lot', 120)->nullable();
            $table->string('control_result', 120)->nullable();
            $table->string('abo_rh_confirmation', 32);
            $table->string('antibody_screen_result', 64)->nullable();
            $table->string('compatibility_result', 32);
            $table->string('status', 32)->default('performed');
            $table->timestamp('performed_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('exception_reason', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['hospital_blood_request_id', 'blood_component_id'], 'compat_request_component_unique');
            $table->index(['blood_component_id', 'status'], 'compat_component_status_index');
            $table->index(['patient_specimen_id', 'status'], 'compat_specimen_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compatibility_tests');
    }
};
