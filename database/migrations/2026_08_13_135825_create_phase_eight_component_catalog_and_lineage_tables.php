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
        Schema::create('component_product_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 48)->unique();
            $table->string('name');
            $table->string('component_type', 80);
            $table->string('production_method', 120);
            $table->string('additive_solution', 120)->nullable();
            $table->unsignedSmallInteger('default_volume_ml')->nullable();
            $table->decimal('storage_temperature_min_c', 5, 2)->nullable();
            $table->decimal('storage_temperature_max_c', 5, 2)->nullable();
            $table->unsignedSmallInteger('shelf_life_days');
            $table->json('special_attributes')->nullable();
            $table->json('quality_criteria')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'component_catalog_approved_by_fk')->nullOnDelete();
            $table->timestamps();

            $table->index(['component_type', 'is_active'], 'component_catalog_type_active_index');
        });

        Schema::create('component_processing_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained(indexName: 'component_processing_donation_fk')->cascadeOnDelete();
            $table->foreignId('blood_unit_id')->constrained(indexName: 'component_processing_unit_fk')->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('users', indexName: 'component_processing_operator_fk')->restrictOnDelete();
            $table->string('event_type', 64)->default('component_production');
            $table->string('method', 120);
            $table->string('device_identifier', 120)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->json('yield_summary')->nullable();
            $table->json('modifications')->nullable();
            $table->json('qc_samples')->nullable();
            $table->json('deviations')->nullable();
            $table->boolean('final_label_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['donation_id', 'event_type'], 'component_processing_donation_event_index');
        });

        Schema::create('blood_components', function (Blueprint $table) {
            $table->id();
            $table->string('product_identifier', 96)->unique();
            $table->foreignId('blood_unit_id')->constrained(indexName: 'blood_component_unit_fk')->cascadeOnDelete();
            $table->foreignId('donation_id')->constrained(indexName: 'blood_component_donation_fk')->cascadeOnDelete();
            $table->foreignId('parent_component_id')->nullable()->constrained('blood_components', indexName: 'blood_component_parent_fk')->restrictOnDelete();
            $table->foreignId('component_product_catalog_id')->constrained(indexName: 'blood_component_catalog_fk')->restrictOnDelete();
            $table->foreignId('component_processing_event_id')->constrained(indexName: 'blood_component_processing_fk')->cascadeOnDelete();
            $table->foreignId('blood_center_id')->constrained(indexName: 'blood_component_center_fk')->restrictOnDelete();
            $table->string('blood_group', 8);
            $table->string('status', 32)->default('quarantined');
            $table->string('storage_location')->nullable();
            $table->unsignedBigInteger('cold_chain_device_id')->nullable();
            $table->json('special_attributes')->nullable();
            $table->date('expiry_date');
            $table->timestamp('processed_at');
            $table->timestamp('released_at')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('recalled_at')->nullable();
            $table->timestamp('investigation_hold_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('disposed_at')->nullable();
            $table->timestamps();

            $table->index(['blood_center_id', 'status', 'expiry_date'], 'blood_component_center_status_expiry_index');
            $table->index(['donation_id', 'blood_unit_id'], 'blood_component_donation_unit_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_components');
        Schema::dropIfExists('component_processing_events');
        Schema::dropIfExists('component_product_catalogs');
    }
};
