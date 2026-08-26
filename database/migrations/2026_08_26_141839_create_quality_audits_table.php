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
        Schema::create('quality_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_center_id')->nullable()->constrained(indexName: 'qaudit_center_fk')->nullOnDelete();
            $table->foreignId('hospital_id')->nullable()->constrained(indexName: 'qaudit_hospital_fk')->nullOnDelete();
            $table->foreignId('lead_auditor_id')->constrained('users', indexName: 'qaudit_lead_fk')->restrictOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users', indexName: 'qaudit_closed_by_fk')->nullOnDelete();
            $table->string('audit_reference', 96)->unique();
            $table->string('audit_type', 64);
            $table->string('status', 32)->default('planned');
            $table->json('scope')->nullable();
            $table->json('findings')->nullable();
            $table->json('linked_deviation_ids')->nullable();
            $table->date('scheduled_on');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('accreditation_readiness')->nullable();
            $table->timestamps();

            $table->index(['audit_type', 'status'], 'qaudit_type_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_audits');
    }
};
