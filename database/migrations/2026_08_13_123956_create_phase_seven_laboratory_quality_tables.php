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
        Schema::create('laboratory_quality_control_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_test_catalog_id')->constrained(indexName: 'lab_qc_catalog_fk')->cascadeOnDelete();
            $table->foreignId('laboratory_equipment_id')->nullable()->constrained('laboratory_equipment', indexName: 'lab_qc_equipment_fk')->nullOnDelete();
            $table->foreignId('laboratory_reagent_lot_id')->nullable()->constrained(indexName: 'lab_qc_reagent_fk')->nullOnDelete();
            $table->foreignId('performed_by')->constrained('users', indexName: 'lab_qc_performed_by_fk')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'lab_qc_reviewed_by_fk')->nullOnDelete();
            $table->string('status', 32);
            $table->string('control_lot', 96)->nullable();
            $table->json('expected_results')->nullable();
            $table->json('observed_results')->nullable();
            $table->timestamp('performed_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['laboratory_test_catalog_id', 'status', 'performed_at'], 'lab_qc_catalog_status_time_index');
        });

        Schema::create('laboratory_quality_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->nullable()->constrained(indexName: 'lab_quality_event_center_fk')->nullOnDelete();
            $table->foreignId('laboratory_test_catalog_id')->nullable()->constrained(indexName: 'lab_quality_event_catalog_fk')->nullOnDelete();
            $table->foreignId('laboratory_equipment_id')->nullable()->constrained('laboratory_equipment', indexName: 'lab_quality_event_equipment_fk')->nullOnDelete();
            $table->foreignId('laboratory_reagent_lot_id')->nullable()->constrained(indexName: 'lab_quality_event_reagent_fk')->nullOnDelete();
            $table->foreignId('opened_by')->constrained('users', indexName: 'lab_quality_event_opened_by_fk')->restrictOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users', indexName: 'lab_quality_event_closed_by_fk')->nullOnDelete();
            $table->string('type', 48);
            $table->string('severity', 32);
            $table->string('status', 32)->default('open');
            $table->string('title');
            $table->text('description');
            $table->json('affected_identifiers')->nullable();
            $table->text('corrective_action')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity'], 'lab_quality_events_status_severity_index');
            $table->index(['blood_center_id', 'type', 'status'], 'lab_quality_events_center_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratory_quality_events');
        Schema::dropIfExists('laboratory_quality_control_runs');
    }
};
