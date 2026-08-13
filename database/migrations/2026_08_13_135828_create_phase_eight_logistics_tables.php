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
        Schema::create('component_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_center_id')->constrained('blood_centers', indexName: 'component_transfer_source_fk')->restrictOnDelete();
            $table->foreignId('destination_center_id')->constrained('blood_centers', indexName: 'component_transfer_destination_fk')->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users', indexName: 'component_transfer_requester_fk')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'component_transfer_approver_fk')->restrictOnDelete();
            $table->string('status', 32)->default('requested');
            $table->string('urgency', 32)->default('routine');
            $table->string('reason', 500);
            $table->string('package_seal')->nullable();
            $table->string('courier_name')->nullable();
            $table->string('vehicle_identifier')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->json('temperature_evidence')->nullable();
            $table->text('discrepancy_notes')->nullable();
            $table->string('acceptance_decision', 32)->nullable();
            $table->timestamps();

            $table->index(['source_center_id', 'destination_center_id', 'status'], 'component_transfer_route_status_index');
        });

        Schema::create('component_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_transfer_id')->constrained(indexName: 'component_transfer_item_transfer_fk')->cascadeOnDelete();
            $table->foreignId('blood_component_id')->constrained(indexName: 'component_transfer_item_component_fk')->restrictOnDelete();
            $table->string('status', 32)->default('requested');
            $table->timestamp('source_confirmed_at')->nullable();
            $table->timestamp('destination_confirmed_at')->nullable();
            $table->boolean('accepted')->nullable();
            $table->timestamps();

            $table->unique(['component_transfer_id', 'blood_component_id'], 'component_transfer_item_unique');
        });

        Schema::create('component_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->constrained(indexName: 'component_dispatch_center_fk')->restrictOnDelete();
            $table->foreignId('dispatched_by')->constrained('users', indexName: 'component_dispatch_actor_fk')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users', indexName: 'component_dispatch_receiver_fk')->nullOnDelete();
            $table->string('request_reference');
            $table->string('destination_name');
            $table->string('route')->nullable();
            $table->timestamp('eta_at')->nullable();
            $table->string('courier_name')->nullable();
            $table->string('vehicle_identifier')->nullable();
            $table->string('package_identifier')->nullable();
            $table->unsignedBigInteger('logger_device_id')->nullable();
            $table->string('status', 32)->default('packed');
            $table->json('chain_of_custody')->nullable();
            $table->string('proof_of_receipt')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['blood_center_id', 'status'], 'component_dispatch_center_status_index');
        });

        Schema::create('component_dispatch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_dispatch_id')->constrained(indexName: 'component_dispatch_item_dispatch_fk')->cascadeOnDelete();
            $table->foreignId('blood_component_id')->constrained(indexName: 'component_dispatch_item_component_fk')->restrictOnDelete();
            $table->string('status', 32)->default('packed');
            $table->string('reconciled_disposition', 48)->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();

            $table->unique(['component_dispatch_id', 'blood_component_id'], 'component_dispatch_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_dispatch_items');
        Schema::dropIfExists('component_dispatches');
        Schema::dropIfExists('component_transfer_items');
        Schema::dropIfExists('component_transfers');
    }
};
