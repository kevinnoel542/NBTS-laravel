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
        Schema::create('cold_chain_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->constrained(indexName: 'cold_chain_device_center_fk')->restrictOnDelete();
            $table->string('device_code', 96)->unique();
            $table->string('name');
            $table->string('device_type', 48);
            $table->string('status', 32)->default('active');
            $table->string('location');
            $table->unsignedSmallInteger('capacity_units')->nullable();
            $table->foreignId('responsible_staff_id')->nullable()->constrained('users', indexName: 'cold_chain_device_staff_fk')->nullOnDelete();
            $table->decimal('temperature_min_c', 5, 2);
            $table->decimal('temperature_max_c', 5, 2);
            $table->date('calibration_due_on')->nullable();
            $table->date('maintenance_due_on')->nullable();
            $table->json('alarm_config')->nullable();
            $table->timestamps();

            $table->index(['blood_center_id', 'status', 'device_type'], 'cold_chain_device_center_status_index');
        });

        Schema::create('cold_chain_temperature_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cold_chain_device_id')->constrained(indexName: 'cold_chain_reading_device_fk')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users', indexName: 'cold_chain_reading_actor_fk')->nullOnDelete();
            $table->decimal('temperature_c', 5, 2);
            $table->timestamp('recorded_at');
            $table->string('sync_state', 32)->default('manual');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['cold_chain_device_id', 'recorded_at'], 'cold_chain_reading_device_time_index');
        });

        Schema::create('cold_chain_excursions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cold_chain_device_id')->constrained(indexName: 'cold_chain_excursion_device_fk')->cascadeOnDelete();
            $table->foreignId('opened_by')->nullable()->constrained('users', indexName: 'cold_chain_excursion_opened_by_fk')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users', indexName: 'cold_chain_excursion_closed_by_fk')->nullOnDelete();
            $table->string('status', 32)->default('open');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->decimal('observed_min_c', 5, 2)->nullable();
            $table->decimal('observed_max_c', 5, 2)->nullable();
            $table->json('affected_component_ids')->nullable();
            $table->text('disposition')->nullable();
            $table->text('capa')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['cold_chain_device_id', 'status'], 'cold_chain_excursion_device_status_index');
        });

        Schema::create('cold_chain_alarms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cold_chain_device_id')->constrained(indexName: 'cold_chain_alarm_device_fk')->cascadeOnDelete();
            $table->foreignId('cold_chain_excursion_id')->nullable()->constrained(indexName: 'cold_chain_alarm_excursion_fk')->nullOnDelete();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users', indexName: 'cold_chain_alarm_ack_by_fk')->nullOnDelete();
            $table->string('status', 32)->default('open');
            $table->timestamp('triggered_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('response_target_at')->nullable();
            $table->string('summary');
            $table->decimal('threshold_min_c', 5, 2)->nullable();
            $table->decimal('threshold_max_c', 5, 2)->nullable();
            $table->decimal('observed_min_c', 5, 2)->nullable();
            $table->decimal('observed_max_c', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['status', 'triggered_at'], 'cold_chain_alarm_status_time_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cold_chain_alarms');
        Schema::dropIfExists('cold_chain_excursions');
        Schema::dropIfExists('cold_chain_temperature_readings');
        Schema::dropIfExists('cold_chain_devices');
    }
};
