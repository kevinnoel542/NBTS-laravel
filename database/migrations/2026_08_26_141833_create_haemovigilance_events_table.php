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
        Schema::create('haemovigilance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->nullable()->constrained(indexName: 'hv_event_center_fk')->nullOnDelete();
            $table->foreignId('hospital_id')->nullable()->constrained(indexName: 'hv_event_hospital_fk')->nullOnDelete();
            $table->foreignId('donor_id')->nullable()->constrained('users', indexName: 'hv_event_donor_fk')->nullOnDelete();
            $table->foreignId('hospital_blood_request_id')->nullable()->constrained(indexName: 'hv_event_hbr_fk')->nullOnDelete();
            $table->foreignId('transfusion_record_id')->nullable()->constrained(indexName: 'hv_event_transfusion_fk')->nullOnDelete();
            $table->foreignId('blood_component_id')->nullable()->constrained(indexName: 'hv_event_component_fk')->nullOnDelete();
            $table->foreignId('reported_by')->constrained('users', indexName: 'hv_event_reported_by_fk')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users', indexName: 'hv_event_assigned_to_fk')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users', indexName: 'hv_event_closed_by_fk')->nullOnDelete();
            $table->string('event_reference', 96)->unique();
            $table->string('event_type', 64);
            $table->string('severity', 32);
            $table->string('status', 32)->default('open');
            $table->string('reaction_type', 96);
            $table->json('symptoms')->nullable();
            $table->timestamp('occurred_at');
            $table->text('immediate_action')->nullable();
            $table->text('treatment')->nullable();
            $table->text('referral')->nullable();
            $table->text('outcome')->nullable();
            $table->json('equipment_context')->nullable();
            $table->json('investigation_context')->nullable();
            $table->string('classification')->nullable();
            $table->string('imputability')->nullable();
            $table->string('reporting_state')->default('recorded');
            $table->json('supply_context')->nullable();
            $table->json('notifications')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('followup_due_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'severity', 'status'], 'hv_event_type_severity_status_index');
            $table->index(['blood_center_id', 'status'], 'hv_event_center_status_index');
            $table->index(['hospital_id', 'status'], 'hv_event_hospital_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('haemovigilance_events');
    }
};
