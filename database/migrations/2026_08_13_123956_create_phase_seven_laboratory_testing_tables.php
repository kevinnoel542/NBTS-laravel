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
        Schema::create('laboratory_specimen_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specimen_id')->constrained(indexName: 'lab_receipt_specimen_fk')->cascadeOnDelete();
            $table->foreignId('collection_episode_id')->constrained(indexName: 'lab_receipt_episode_fk')->cascadeOnDelete();
            $table->foreignId('collection_container_id')->nullable()->constrained(indexName: 'lab_receipt_container_fk')->nullOnDelete();
            $table->foreignId('blood_center_id')->constrained(indexName: 'lab_receipt_center_fk')->restrictOnDelete();
            $table->foreignId('received_by')->constrained('users', indexName: 'lab_receipt_received_by_fk')->restrictOnDelete();
            $table->string('scanned_identifier', 96);
            $table->string('receiving_station');
            $table->string('status', 32)->default('accepted');
            $table->timestamp('received_at');
            $table->text('rejection_reason')->nullable();
            $table->text('exception_notes')->nullable();
            $table->timestamps();

            $table->unique('specimen_id', 'lab_receipt_specimen_unique');
            $table->index(['blood_center_id', 'status', 'received_at'], 'lab_receipt_center_status_time_index');
        });

        Schema::create('laboratory_test_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_specimen_receipt_id')->constrained(indexName: 'lab_order_receipt_fk')->cascadeOnDelete();
            $table->foreignId('specimen_id')->constrained(indexName: 'lab_order_specimen_fk')->cascadeOnDelete();
            $table->foreignId('laboratory_test_catalog_id')->constrained(indexName: 'lab_order_catalog_fk')->restrictOnDelete();
            $table->foreignId('ordered_by')->constrained('users', indexName: 'lab_order_ordered_by_fk')->restrictOnDelete();
            $table->string('status', 32)->default('ordered');
            $table->timestamp('ordered_at');
            $table->timestamp('due_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->unique(['laboratory_specimen_receipt_id', 'laboratory_test_catalog_id'], 'lab_order_receipt_catalog_unique');
            $table->index(['status', 'due_at'], 'lab_order_status_due_index');
        });

        Schema::create('laboratory_test_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_test_order_id')->constrained(indexName: 'lab_run_order_fk')->cascadeOnDelete();
            $table->foreignId('laboratory_test_catalog_id')->constrained(indexName: 'lab_run_catalog_fk')->restrictOnDelete();
            $table->foreignId('laboratory_equipment_id')->nullable()->constrained('laboratory_equipment', indexName: 'lab_run_equipment_fk')->nullOnDelete();
            $table->foreignId('laboratory_reagent_lot_id')->nullable()->constrained(indexName: 'lab_run_reagent_fk')->nullOnDelete();
            $table->foreignId('operator_id')->constrained('users', indexName: 'lab_run_operator_fk')->restrictOnDelete();
            $table->string('method_version', 64);
            $table->string('status', 32)->default('completed');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('control_lot', 96)->nullable();
            $table->json('raw_payload')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['laboratory_test_catalog_id', 'status', 'started_at'], 'lab_run_catalog_status_time_index');
        });

        Schema::create('laboratory_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_test_order_id')->constrained(indexName: 'lab_result_order_fk')->cascadeOnDelete();
            $table->foreignId('laboratory_test_run_id')->constrained(indexName: 'lab_result_run_fk')->cascadeOnDelete();
            $table->foreignId('laboratory_test_catalog_id')->constrained(indexName: 'lab_result_catalog_fk')->restrictOnDelete();
            $table->foreignId('laboratory_quality_control_run_id')->constrained(indexName: 'lab_result_qc_fk')->restrictOnDelete();
            $table->foreignId('entered_by')->constrained('users', indexName: 'lab_result_entered_by_fk')->restrictOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users', indexName: 'lab_result_verified_by_fk')->nullOnDelete();
            $table->string('result_value');
            $table->string('interpretation', 48);
            $table->string('status', 32)->default('preliminary');
            $table->boolean('is_release_blocking')->default(false);
            $table->timestamp('resulted_at');
            $table->timestamp('verified_at')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['laboratory_test_order_id', 'status'], 'lab_result_order_status_index');
            $table->index(['interpretation', 'is_release_blocking'], 'lab_result_interpretation_block_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratory_test_results');
        Schema::dropIfExists('laboratory_test_runs');
        Schema::dropIfExists('laboratory_test_orders');
        Schema::dropIfExists('laboratory_specimen_receipts');
    }
};
