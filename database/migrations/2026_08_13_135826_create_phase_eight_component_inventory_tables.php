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
        Schema::create('component_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_component_id')->constrained(indexName: 'component_reservation_component_fk')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users', indexName: 'component_reservation_requested_by_fk')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'component_reservation_approved_by_fk')->restrictOnDelete();
            $table->string('status', 32)->default('active');
            $table->string('reason', 500);
            $table->string('exception_reason', 500)->nullable();
            $table->timestamp('reserved_at');
            $table->timestamp('reserved_until');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['blood_component_id', 'status'], 'component_reservation_component_status_index');
            $table->index(['status', 'reserved_until'], 'component_reservation_expiry_index');
        });

        Schema::create('component_inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_component_id')->constrained(indexName: 'component_adjustment_component_fk')->cascadeOnDelete();
            $table->foreignId('blood_center_id')->constrained(indexName: 'component_adjustment_center_fk')->restrictOnDelete();
            $table->foreignId('adjusted_by')->constrained('users', indexName: 'component_adjustment_actor_fk')->restrictOnDelete();
            $table->foreignId('independent_approved_by')->nullable()->constrained('users', indexName: 'component_adjustment_approval_fk')->restrictOnDelete();
            $table->string('previous_status', 32);
            $table->string('new_status', 32);
            $table->string('reason', 120);
            $table->string('evidence_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('adjusted_at');
            $table->timestamps();

            $table->index(['blood_center_id', 'new_status'], 'component_adjustment_center_status_index');
        });

        Schema::create('component_return_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_component_id')->constrained(indexName: 'component_return_component_fk')->cascadeOnDelete();
            $table->foreignId('assessed_by')->constrained('users', indexName: 'component_return_assessed_by_fk')->restrictOnDelete();
            $table->timestamp('received_at');
            $table->decimal('temperature_min_c', 5, 2)->nullable();
            $table->decimal('temperature_max_c', 5, 2)->nullable();
            $table->string('package_condition', 120);
            $table->json('chain_of_custody')->nullable();
            $table->string('disposition', 32);
            $table->boolean('accepted_for_restock')->default(false);
            $table->string('evidence_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('assessed_at');
            $table->timestamps();
        });

        Schema::create('component_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_component_id')->constrained(indexName: 'component_disposal_component_fk')->cascadeOnDelete();
            $table->foreignId('disposed_by')->constrained('users', indexName: 'component_disposal_actor_fk')->restrictOnDelete();
            $table->foreignId('witnessed_by')->nullable()->constrained('users', indexName: 'component_disposal_witness_fk')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'component_disposal_approval_fk')->restrictOnDelete();
            $table->string('method', 120);
            $table->string('reason', 120);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->string('location');
            $table->string('evidence_reference')->nullable();
            $table->timestamp('disposed_at');
            $table->timestamps();

            $table->index(['reason', 'disposed_at'], 'component_disposal_reason_time_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_disposals');
        Schema::dropIfExists('component_return_assessments');
        Schema::dropIfExists('component_inventory_adjustments');
        Schema::dropIfExists('component_reservations');
    }
};
