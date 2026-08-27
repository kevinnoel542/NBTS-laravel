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
        Schema::create('report_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_definition_id')->nullable()->constrained('kpi_definitions')->nullOnDelete();
            $table->foreignId('generated_by')->constrained('users', indexName: 'report_snapshot_generated_by_fk')->restrictOnDelete();
            $table->string('report_reference', 64)->unique();
            $table->string('report_type', 80);
            $table->date('source_period_start');
            $table->date('source_period_end');
            $table->json('scope');
            $table->json('metrics');
            $table->json('reconciliation');
            $table->boolean('deidentified')->default(true);
            $table->boolean('national_dashboard_ready')->default(false);
            $table->string('status', 32)->default('generated');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['report_type', 'status']);
            $table->index(['source_period_start', 'source_period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_snapshots');
    }
};
