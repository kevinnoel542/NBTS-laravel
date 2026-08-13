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
        Schema::create('emergency_release_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_blood_request_id')->constrained(indexName: 'era_request_fk')->cascadeOnDelete();
            $table->foreignId('blood_component_id')->constrained(indexName: 'era_component_fk')->restrictOnDelete();
            $table->foreignId('authorized_by')->constrained('users', indexName: 'era_authorized_by_fk')->restrictOnDelete();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users', indexName: 'era_ack_by_fk')->nullOnDelete();
            $table->string('clinical_authorizer_name');
            $table->string('clinical_authorizer_contact')->nullable();
            $table->string('reason', 500);
            $table->text('risk_acknowledgement');
            $table->string('status', 32)->default('authorized');
            $table->timestamp('authorized_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('retrospective_completion_due_at');
            $table->timestamp('retrospective_completed_at')->nullable();
            $table->timestamps();

            $table->index(['hospital_blood_request_id', 'status'], 'era_request_status_index');
            $table->index(['blood_component_id', 'status'], 'era_component_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_release_authorizations');
    }
};
