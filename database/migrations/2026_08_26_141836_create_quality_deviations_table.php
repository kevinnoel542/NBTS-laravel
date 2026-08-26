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
        Schema::create('quality_deviations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->nullable()->constrained(indexName: 'qdev_center_fk')->nullOnDelete();
            $table->foreignId('hospital_id')->nullable()->constrained(indexName: 'qdev_hospital_fk')->nullOnDelete();
            $table->foreignId('opened_by')->constrained('users', indexName: 'qdev_opened_by_fk')->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users', indexName: 'qdev_owner_fk')->nullOnDelete();
            $table->foreignId('quality_approved_by')->nullable()->constrained('users', indexName: 'qdev_approved_by_fk')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users', indexName: 'qdev_closed_by_fk')->nullOnDelete();
            $table->string('deviation_reference', 96)->unique();
            $table->string('type', 64);
            $table->string('severity', 32);
            $table->string('status', 32)->default('open');
            $table->string('title');
            $table->text('description');
            $table->json('affected_records')->nullable();
            $table->text('containment')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('correction')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('preventive_action')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->text('effectiveness_check')->nullable();
            $table->timestamp('effectiveness_checked_at')->nullable();
            $table->text('closure_evidence')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity'], 'qdev_status_severity_index');
            $table->index(['type', 'status'], 'qdev_type_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_deviations');
    }
};
