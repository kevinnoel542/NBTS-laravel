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
        Schema::create('kpi_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approved_by')->nullable()->constrained('users', indexName: 'kpi_definition_approved_by_fk')->nullOnDelete();
            $table->string('kpi_code', 80)->unique();
            $table->string('name');
            $table->string('category', 80);
            $table->text('numerator');
            $table->text('denominator');
            $table->json('exclusions');
            $table->json('source_models');
            $table->string('owner', 120);
            $table->string('frequency', 40);
            $table->string('target', 120)->nullable();
            $table->json('data_quality_checks');
            $table->json('anti_gaming_controls');
            $table->string('status', 32)->default('draft');
            $table->date('effective_from')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index(['frequency', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_definitions');
    }
};
