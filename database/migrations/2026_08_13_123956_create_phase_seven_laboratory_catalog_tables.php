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
        Schema::create('laboratory_test_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('category', 48);
            $table->string('specimen_type', 48);
            $table->string('method', 120);
            $table->string('algorithm_version', 48);
            $table->string('result_units', 48)->nullable();
            $table->string('reference_range')->nullable();
            $table->json('release_blocking_interpretations')->nullable();
            $table->boolean('is_required_for_release')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'lab_catalog_approved_by_fk')->nullOnDelete();
            $table->timestamps();

            $table->index(['specimen_type', 'is_active', 'is_required_for_release'], 'lab_catalog_specimen_release_index');
            $table->index(['category', 'is_active'], 'lab_catalog_category_active_index');
        });

        Schema::create('laboratory_reagent_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_test_catalog_id')->nullable()->constrained(indexName: 'lab_reagent_catalog_fk')->nullOnDelete();
            $table->string('reagent_name');
            $table->string('lot_number', 96);
            $table->string('manufacturer')->nullable();
            $table->string('status', 32)->default('usable');
            $table->string('validation_state', 32)->default('pending');
            $table->string('storage_location')->nullable();
            $table->date('received_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('recalled_at')->nullable();
            $table->timestamps();

            $table->unique(['reagent_name', 'lot_number'], 'lab_reagent_name_lot_unique');
            $table->index(['status', 'expires_on'], 'lab_reagent_status_expiry_index');
        });

        Schema::create('laboratory_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->nullable()->constrained(indexName: 'lab_equipment_center_fk')->nullOnDelete();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('equipment_type', 80);
            $table->string('interface_mode', 32)->default('manual');
            $table->string('status', 32)->default('active');
            $table->date('calibration_due_on')->nullable();
            $table->date('maintenance_due_on')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamp('downtime_started_at')->nullable();
            $table->timestamps();

            $table->index(['blood_center_id', 'status'], 'lab_equipment_center_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratory_equipment');
        Schema::dropIfExists('laboratory_reagent_lots');
        Schema::dropIfExists('laboratory_test_catalogs');
    }
};
